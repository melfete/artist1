<?php
// Externe Künstlerdaten: Fans + Bild von der Deezer API, Kurzbeschreibung von der Wikipedia REST API.
// Beide APIs brauchen keinen Schlüssel. Die Daten werden in `artists` gecacht (siehe db/migrations/003)
// und erst nach ARTIST_CACHE_TTL neu abgerufen. Schlägt ein Abruf fehl, bleiben die alten Daten stehen.

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

const ARTIST_CACHE_TTL = 7 * 24 * 3600;   // 7 Tage
const API_TIMEOUT      = 5;               // Sekunden pro Anfrage
const API_USER_AGENT   = 'ArtistQuiz/1.0 (https://artistquiz.rf.gd)';
const ARTIST_COLUMNS   = 'id, name, deezer_id, fans, image_url, bio, bio_url, wiki_title, updated_at,
                          age, streams_mio, most_streamed, newest_song, image_path';

/**
 * GET-Anfrage, Antwort als JSON-Array – oder null bei Fehler/Timeout/Statuscode ≠ 200.
 * Antwortet ein Host zweimal nicht (Timeout, 5xx, 429), wird er für den Rest des Requests
 * übersprungen, damit eine ausgefallene API die Seite nicht mehrfach 5 s blockiert.
 */
function api_get_json(string $url): ?array
{
    static $failures = [];
    $host = (string)parse_url($url, PHP_URL_HOST);

    if (!function_exists('curl_init') || ($failures[$host] ?? 0) >= 2) {
        return null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_CONNECTTIMEOUT => API_TIMEOUT,
        CURLOPT_TIMEOUT        => API_TIMEOUT,
        CURLOPT_USERAGENT      => API_USER_AGENT,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($body === false || $code === 429 || $code >= 500) {
        $failures[$host] = ($failures[$host] ?? 0) + 1;
        error_log("API-Fehler ($code) $url: " . curl_error($ch));
        return null;
    }
    if ($code !== 200) {
        return null; // z. B. 404: kein Fehler der API, nur nichts gefunden
    }

    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

/** Nur https-URLs übernehmen (Schutz vor unerwarteten Werten aus der API-Antwort). */
function https_url_or_null($url): ?string
{
    return is_string($url) && str_starts_with($url, 'https://') && strlen($url) <= 255 ? $url : null;
}

/**
 * Deezer: Fans und Bild. Mit bekannter ID direkt per /artist/{id}, sonst über die Suche
 * (exakter Name mit den meisten Fans, ansonsten Treffer mit den meisten Fans).
 *
 * @return array{deezer_id:int, fans:int, image_url:?string}|null
 */
function deezer_fetch_artist(?int $deezerId, string $name): ?array
{
    if ($deezerId) {
        $artist = api_get_json('https://api.deezer.com/artist/' . $deezerId);
    } else {
        $search = api_get_json('https://api.deezer.com/search/artist?limit=25&q=' . rawurlencode($name));
        $results = $search['data'] ?? [];
        $norm = fn($s) => mb_strtolower(trim((string)$s), 'UTF-8');
        $exact = array_filter($results, fn($a) => $norm($a['name'] ?? '') === $norm($name));
        $pool = $exact ?: $results;
        usort($pool, fn($a, $b) => ($b['nb_fan'] ?? 0) <=> ($a['nb_fan'] ?? 0));
        $artist = $pool[0] ?? null;
    }

    // Deezer meldet Fehler mit Status 200 und {"error": {...}}
    if (!is_array($artist) || isset($artist['error']) || !isset($artist['id'], $artist['nb_fan'])) {
        return null;
    }

    return [
        'deezer_id' => (int)$artist['id'],
        'fans'      => max(0, (int)$artist['nb_fan']),
        'image_url' => https_url_or_null($artist['picture_xl'] ?? null),
    ];
}

/**
 * Wikipedia: Kurzbeschreibung, zuerst Deutsch, dann Englisch.
 * Erst direkter Titel, sonst Suche. Ein Artikel zählt nur, wenn sein Titel (ohne Klammerzusatz)
 * dem Namen entspricht und Beschreibung/Text nach Musik klingt – so landet „Drake“ bei
 * „Drake (Rapper)“ und nicht bei einer Begriffsklärung oder einem Vornamen-Artikel.
 *
 * Bei mehrdeutigen Namen kann der Artikel in `artists`.`wiki_title` fest vorgegeben werden,
 * Format „sprache:Titel“, z. B. „de:Lalisa Manobal“.
 *
 * @return array{bio:string, bio_url:?string}|null
 */
function wiki_fetch_bio(string $name, ?string $fixedTitle = null): ?array
{
    $summaryUrl = fn(string $lang, string $title) =>
        "https://{$lang}.wikipedia.org/api/rest_v1/page/summary/" . rawurlencode(str_replace(' ', '_', $title));
    $toResult = fn(array $page) => [
        'bio'     => trim($page['extract']),
        'bio_url' => https_url_or_null($page['content_urls']['desktop']['page'] ?? null),
    ];

    if ($fixedTitle && preg_match('/^([a-z]{2,3}):(.+)$/', $fixedTitle, $m)) {
        $page = api_get_json($summaryUrl($m[1], $m[2]));
        $ok = $page && ($page['type'] ?? '') === 'standard' && !empty($page['extract']);
        return $ok ? $toResult($page) : null;
    }

    // Vergleich ohne Groß-/Kleinschreibung, Akzente und Klammerzusatz: „Netón_Vega“ = „Neton Vega“
    $norm = function ($s): string {
        $s = preg_replace('/\s*\(.*\)$/u', '', str_replace('_', ' ', (string)$s));
        $s = strtr(mb_strtolower(trim($s), 'UTF-8'), [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ñ' => 'n', 'ç' => 'c',
        ]);
        return $s;
    };
    $music = '/s[äa]nger|musiker|band|rapper|komponist|songwriter|produzent|\bdj\b|singer|musician|composer|record producer|group|duo/iu';

    $isMatch = function (?array $page) use ($name, $norm, $music): bool {
        return $page
            && ($page['type'] ?? '') === 'standard'
            && !empty($page['extract'])
            && $norm($page['title'] ?? '') === $norm($name)
            && preg_match($music, ($page['description'] ?? '') . ' ' . $page['extract']);
    };

    foreach (['de', 'en'] as $lang) {
        $base = "https://{$lang}.wikipedia.org";
        $page = api_get_json($summaryUrl($lang, $name));
        if (!$isMatch($page)) {
            $page = null;
            $search = api_get_json("$base/w/rest.php/v1/search/page?limit=5&q=" . rawurlencode($name));
            foreach ($search['pages'] ?? [] as $hit) {
                $desc = $hit['description'] ?? '';
                if ($norm($hit['key'] ?? '') !== $norm($name) || ($desc !== '' && !preg_match($music, $desc))) {
                    continue;
                }
                $candidate = api_get_json($summaryUrl($lang, $hit['key']));
                if ($isMatch($candidate)) {
                    $page = $candidate;
                    break;
                }
            }
        }

        if ($page) {
            return $toResult($page);
        }
    }
    return null;
}

/** Sind die gecachten Daten älter als 7 Tage (oder noch nie geladen)? */
function artist_is_stale(array $artist): bool
{
    return empty($artist['updated_at'])
        || strtotime($artist['updated_at']) < time() - ARTIST_CACHE_TTL;
}

/**
 * Lädt Deezer- und Wikipedia-Daten neu und speichert sie. Bei einem Deezer-Fehler bleibt alles,
 * wie es ist (updated_at wird nicht gesetzt → nächster Aufruf versucht es erneut).
 * Findet Wikipedia nichts, bleibt eine vorhandene Beschreibung erhalten.
 */
function artist_refresh(mysqli $conn, array $artist): array
{
    $deezer = deezer_fetch_artist($artist['deezer_id'] ? (int)$artist['deezer_id'] : null, $artist['name']);
    if ($deezer === null) {
        return $artist;
    }
    $wiki = wiki_fetch_bio($artist['name'], $artist['wiki_title'] ?? null);

    $bio    = $wiki['bio'] ?? null;
    $bioUrl = $wiki['bio_url'] ?? null;
    $id     = (int)$artist['id'];

    $stmt = $conn->prepare(
        'UPDATE artists
            SET deezer_id = ?, fans = ?, image_url = ?,
                bio = COALESCE(?, bio), bio_url = COALESCE(?, bio_url),
                updated_at = NOW()
          WHERE id = ?'
    );
    $stmt->bind_param('iisssi', $deezer['deezer_id'], $deezer['fans'], $deezer['image_url'], $bio, $bioUrl, $id);
    if (!$stmt->execute()) {
        error_log('artist_refresh: ' . $stmt->error);
        $stmt->close();
        return $artist;
    }
    $stmt->close();

    return array_merge($artist, $deezer, [
        'bio'        => $bio ?? $artist['bio'],
        'bio_url'    => $bioUrl ?? $artist['bio_url'],
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

/** Gibt den Künstler zurück und lädt seine Daten vorher neu, falls sie veraltet sind. */
function artist_fresh(mysqli $conn, array $artist): array
{
    return artist_is_stale($artist) ? artist_refresh($conn, $artist) : $artist;
}

/** Ein Künstler per ID (Daten aus dem Cache, ggf. aktualisiert). */
function artist_find(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare('SELECT ' . ARTIST_COLUMNS . ' FROM artists WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $artist = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $artist ? artist_fresh($conn, $artist) : null;
}

/**
 * Alle Künstler, alphabetisch. Höchstens $refreshLimit veraltete Einträge werden pro Aufruf
 * neu geladen (die ältesten zuerst), damit die Übersicht nicht auf 40 API-Abrufe wartet.
 */
function artist_all(mysqli $conn, int $refreshLimit = 2): array
{
    $artists = $conn->query('SELECT ' . ARTIST_COLUMNS . ' FROM artists ORDER BY name')->fetch_all(MYSQLI_ASSOC);

    $stale = array_keys(array_filter($artists, 'artist_is_stale'));
    usort($stale, fn($a, $b) => strcmp((string)$artists[$a]['updated_at'], (string)$artists[$b]['updated_at']));
    foreach (array_slice($stale, 0, $refreshLimit) as $i) {
        $artists[$i] = artist_refresh($conn, $artists[$i]);
    }
    return $artists;
}

/** Lokales Bild: image_path aus der DB, sonst Suche in image/, sonst Platzhalter. */
function artist_local_image(array $artist): string
{
    if (!empty($artist['image_path']) && is_file(__DIR__ . '/' . $artist['image_path'])) {
        return $artist['image_path'];
    }
    return find_artist_image($artist['name']);
}

/** Bild für die Anzeige: Deezer-Bild, ansonsten lokales Bild. */
function artist_image(array $artist): string
{
    return $artist['image_url'] ?: artist_local_image($artist);
}

/**
 * <img>-Tag mit Fallback: Lädt das Deezer-Bild im Browser nicht, wird das lokale Bild gezeigt.
 */
function artist_img_tag(array $artist, string $attrs = ''): string
{
    $local = artist_local_image($artist);
    return '<img src="' . e(artist_image($artist)) . '" alt="' . e($artist['name']) . '"'
         . ' data-fallback="' . e($local) . '"'
         . ' onerror="this.onerror=null;this.src=this.dataset.fallback"'
         . ($attrs !== '' ? ' ' . $attrs : '') . '>';
}

/** Fan-Zahl im deutschen Format, „–“ wenn noch nicht geladen. */
function format_fans($fans): string
{
    return $fans === null ? '–' : number_format((int)$fans, 0, ',', '.');
}
