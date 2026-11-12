<?php
// Externe Künstlerdaten: Fans + Bild von der Deezer API, Kurzbeschreibung von der Wikipedia REST API.
// Beide APIs brauchen keinen Schlüssel. Die Daten werden in `artists` gecacht und erst nach
// ARTIST_CACHE_TTL neu abgerufen (siehe db/migrations/003 und 004):
//   updated_at     = letzter Deezer-Abruf (Fans, Bild)    – Übersicht, Spiel, Detailseite
//   bio_updated_at = letzter Wikipedia-Abruf (Beschreibung) – nur Detailseite
// Schlägt ein Abruf fehl, bleiben die alten Daten stehen.

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

const ARTIST_CACHE_TTL = 7 * 24 * 3600;   // 7 Tage
const API_TIMEOUT      = 5;               // Sekunden pro Anfrage
const API_USER_AGENT   = 'ArtistQuiz/1.0 (https://artistquiz.rf.gd)';
const ARTIST_REFRESH_BUDGET = 3.0;        // Sekunden, die die Übersicht pro Aufruf für Deezer nutzt
const ARTIST_COLUMNS   = 'id, name, deezer_id, fans, image_url, bio, bio_url, wiki_title, updated_at, bio_updated_at,
                          age, streams_mio, most_streamed, newest_song, image_path';

/* ------------------------------------------------------------------ HTTP */

/**
 * Fehlerzähler pro Host. Antwortet ein Host zweimal nicht (Timeout, 5xx, 429), wird er für den
 * Rest des Requests übersprungen, damit eine ausgefallene API die Seite nicht mehrfach 5 s blockiert.
 */
function api_failures(?string $failedHost = null): array
{
    static $failures = [];
    if ($failedHost !== null) {
        $failures[$failedHost] = ($failures[$failedHost] ?? 0) + 1;
    }
    return $failures;
}

function api_host_blocked(string $url): bool
{
    return (api_failures()[(string)parse_url($url, PHP_URL_HOST)] ?? 0) >= 2;
}

function api_curl_handle(string $url)
{
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
    return $ch;
}

/** Wertet eine fertige Anfrage aus: JSON-Array oder null (Fehler wird pro Host gezählt). */
function api_handle_result($ch, string $url, $body): ?array
{
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($code === 0 || $code === 429 || $code >= 500) {
        api_failures((string)parse_url($url, PHP_URL_HOST));
        error_log("API-Fehler ($code) $url: " . curl_error($ch));
        return null;
    }
    if ($code !== 200 || !is_string($body)) {
        return null; // z. B. 404: kein Fehler der API, nur nichts gefunden
    }
    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

/** Eine GET-Anfrage, Antwort als JSON-Array – oder null bei Fehler/Timeout/Statuscode ≠ 200. */
function api_get_json(string $url): ?array
{
    if (!function_exists('curl_init') || api_host_blocked($url)) {
        return null;
    }
    $ch = api_curl_handle($url);
    $body = curl_exec($ch);
    return api_handle_result($ch, $url, $body);
}

/** Nur https-URLs übernehmen (Schutz vor unerwarteten Werten aus der API-Antwort). */
function https_url_or_null($url): ?string
{
    return is_string($url) && str_starts_with($url, 'https://') && strlen($url) <= 255 ? $url : null;
}

/* ---------------------------------------------------------------- Deezer */

/** Antwort von /artist/{id} bzw. ein Suchtreffer → Fans und Bild, oder null. */
function deezer_parse_artist(?array $artist): ?array
{
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
 * Deezer für einen Künstler: mit bekannter ID direkt per /artist/{id}, sonst über die Suche
 * (exakter Name mit den meisten Fans, ansonsten Treffer mit den meisten Fans).
 *
 * @return array{deezer_id:int, fans:int, image_url:?string}|null
 */
function deezer_fetch_artist(?int $deezerId, string $name): ?array
{
    if ($deezerId) {
        return deezer_parse_artist(api_get_json('https://api.deezer.com/artist/' . $deezerId));
    }

    $search = api_get_json('https://api.deezer.com/search/artist?limit=25&q=' . rawurlencode($name));
    $results = $search['data'] ?? [];
    $norm = fn($s) => mb_strtolower(trim((string)$s), 'UTF-8');
    $exact = array_filter($results, fn($a) => $norm($a['name'] ?? '') === $norm($name));
    $pool = $exact ?: $results;
    usort($pool, fn($a, $b) => ($b['nb_fan'] ?? 0) <=> ($a['nb_fan'] ?? 0));
    return deezer_parse_artist($pool[0] ?? null);
}

/* ------------------------------------------------------------- Wikipedia */

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
        return strtr(mb_strtolower(trim($s), 'UTF-8'), [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ñ' => 'n', 'ç' => 'c',
        ]);
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

/* ----------------------------------------------------------------- Cache */

function cache_expired(?string $timestamp, int $ttl = ARTIST_CACHE_TTL): bool
{
    return empty($timestamp) || strtotime($timestamp) < time() - $ttl;
}

/** Fans/Bild älter als 7 Tage (oder noch nie geladen)? */
function artist_is_stale(array $artist): bool
{
    return cache_expired($artist['updated_at'] ?? null);
}

/** Kurzbeschreibung älter als 7 Tage (oder noch nie geladen)? */
function artist_bio_is_stale(array $artist): bool
{
    return cache_expired($artist['bio_updated_at'] ?? null);
}

/** Speichert Deezer-Daten und gibt den aktualisierten Künstler zurück. */
function artist_save_deezer(mysqli $conn, array $artist, array $deezer): array
{
    $id = (int)$artist['id'];
    $stmt = $conn->prepare(
        'UPDATE artists SET deezer_id = ?, fans = ?, image_url = ?, updated_at = NOW() WHERE id = ?'
    );
    $stmt->bind_param('iisi', $deezer['deezer_id'], $deezer['fans'], $deezer['image_url'], $id);
    $ok = $stmt->execute();
    if (!$ok) {
        error_log('artist_save_deezer: ' . $stmt->error);
    }
    $stmt->close();

    return $ok ? array_merge($artist, $deezer, ['updated_at' => date('Y-m-d H:i:s')]) : $artist;
}

/**
 * Lädt Fans/Bild der veralteten Künstler in $artists neu, die ältesten zuerst.
 * Gleichzeitige Anfragen gehen nicht (InfinityFree sperrt curl_multi_exec), daher nacheinander,
 * aber höchstens $budget Sekunden lang – der Rest folgt beim nächsten Aufruf.
 * Bei einem Fehler bleibt der jeweilige Eintrag unverändert (nächster Aufruf versucht es erneut).
 */
function artists_refresh_fans(mysqli $conn, array $artists, float $budget = ARTIST_REFRESH_BUDGET): array
{
    $stale = array_keys(array_filter($artists, 'artist_is_stale'));
    usort($stale, fn($a, $b) => strcmp((string)$artists[$a]['updated_at'], (string)$artists[$b]['updated_at']));

    $start = microtime(true);
    foreach ($stale as $i) {
        if (microtime(true) - $start >= $budget) {
            break;
        }
        $artist = $artists[$i];
        $deezer = deezer_fetch_artist($artist['deezer_id'] ? (int)$artist['deezer_id'] : null, $artist['name']);
        if ($deezer !== null) {
            $artists[$i] = artist_save_deezer($conn, $artist, $deezer);
        }
    }
    return $artists;
}

/** Ein Künstler mit aktuellen Fans/Bild (lädt bei Bedarf neu). */
function artist_fresh(mysqli $conn, array $artist): array
{
    return artists_refresh_fans($conn, [$artist], PHP_FLOAT_MAX)[0];
}

/**
 * Lädt die Wikipedia-Beschreibung neu, falls veraltet. Findet Wikipedia nichts, bleibt eine
 * vorhandene Beschreibung erhalten. War Wikipedia nicht erreichbar, wird bio_updated_at nicht
 * gesetzt, damit der nächste Aufruf es erneut versucht.
 */
function artist_refresh_bio(mysqli $conn, array $artist): array
{
    if (!artist_bio_is_stale($artist)) {
        return $artist;
    }

    $failuresBefore = array_sum(api_failures());
    $wiki = wiki_fetch_bio($artist['name'], $artist['wiki_title'] ?? null);
    if ($wiki === null && array_sum(api_failures()) > $failuresBefore) {
        return $artist;
    }

    $bio    = $wiki['bio'] ?? null;
    $bioUrl = $wiki['bio_url'] ?? null;
    $id     = (int)$artist['id'];

    $stmt = $conn->prepare(
        'UPDATE artists
            SET bio = COALESCE(?, bio), bio_url = COALESCE(?, bio_url), bio_updated_at = NOW()
          WHERE id = ?'
    );
    $stmt->bind_param('ssi', $bio, $bioUrl, $id);
    $ok = $stmt->execute();
    if (!$ok) {
        error_log('artist_refresh_bio: ' . $stmt->error);
    }
    $stmt->close();

    return $ok ? array_merge($artist, [
        'bio'            => $bio ?? $artist['bio'],
        'bio_url'        => $bioUrl ?? $artist['bio_url'],
        'bio_updated_at' => date('Y-m-d H:i:s'),
    ]) : $artist;
}

/* ---------------------------------------------------------- Abfragen */

/** Ein Künstler per ID für die Detailseite: Fans/Bild und Beschreibung, bei Bedarf neu geladen. */
function artist_find(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare('SELECT ' . ARTIST_COLUMNS . ' FROM artists WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $artist = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $artist ? artist_refresh_bio($conn, artist_fresh($conn, $artist)) : null;
}

/** Alle Künstler, alphabetisch; veraltete Fans/Bilder werden bis zu 3 s lang neu geladen. */
function artist_all(mysqli $conn): array
{
    $artists = $conn->query('SELECT ' . ARTIST_COLUMNS . ' FROM artists ORDER BY name')->fetch_all(MYSQLI_ASSOC);
    return artists_refresh_fans($conn, $artists);
}

/* ------------------------------------------------------------ Startseite */

const TOP_TRACKS_TTL      = 24 * 3600;   // Top Songs: 1 Tag
const TOP_TRACKS_PLAYLIST = 3155776842;  // Deezer-Playlist „Top Worldwide“ (unabhängig vom Serverstandort)

/** Liest einen Eintrag aus `api_cache` (db/migrations/005); null, wenn nicht vorhanden. */
function cache_get(mysqli $conn, string $key): ?array
{
    $stmt = $conn->prepare('SELECT payload, updated_at FROM api_cache WHERE cache_key = ?');
    if (!$stmt) {
        return null; // Tabelle fehlt (005 nicht eingespielt): ohne Cache weiter
    }
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $data = $row ? json_decode($row['payload'], true) : null;
    return is_array($data) ? ['data' => $data, 'updated_at' => $row['updated_at']] : null;
}

function cache_set(mysqli $conn, string $key, array $data): void
{
    $stmt = $conn->prepare(
        'INSERT INTO api_cache (cache_key, payload, updated_at) VALUES (?, ?, NOW())
         ON DUPLICATE KEY UPDATE payload = VALUES(payload), updated_at = VALUES(updated_at)'
    );
    if (!$stmt) {
        return;
    }
    $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
    $stmt->bind_param('ss', $key, $payload);
    $stmt->execute();
    $stmt->close();
}

/**
 * Die 10 ersten Songs der Deezer-Playlist „Top Worldwide“, einmal am Tag neu geladen.
 * Bei einem Fehler die zuletzt gespeicherte Liste; null, wenn es noch keine gibt.
 *
 * @return list<array{title:string, artist:string, link:?string}>|null
 */
function top_tracks(mysqli $conn): ?array
{
    $cached = cache_get($conn, 'top_tracks');
    if ($cached && !cache_expired($cached['updated_at'], TOP_TRACKS_TTL)) {
        return $cached['data'];
    }

    $response = api_get_json('https://api.deezer.com/playlist/' . TOP_TRACKS_PLAYLIST . '/tracks?limit=10');
    $tracks = [];
    foreach ($response['data'] ?? [] as $track) {
        if (isset($track['title'], $track['artist']['name'])) {
            $tracks[] = [
                'title'  => (string)$track['title'],
                'artist' => (string)$track['artist']['name'],
                'link'   => https_url_or_null($track['link'] ?? null),
            ];
        }
    }

    if ($tracks) {
        cache_set($conn, 'top_tracks', $tracks);
        return $tracks;
    }
    return $cached['data'] ?? null;
}

/** Künstler mit den meisten Deezer-Fans (nur aus dem Cache, ohne API-Abruf). */
function top_artists(mysqli $conn, int $limit = 10): array
{
    $stmt = $conn->prepare(
        'SELECT ' . ARTIST_COLUMNS . ' FROM artists WHERE fans IS NOT NULL ORDER BY fans DESC LIMIT ?'
    );
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $artists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $artists;
}

/* ---------------------------------------------------------------- Ausgabe */

/** Lokales Bild: image_path aus der DB, sonst Suche in image/, sonst Platzhalter. */
function artist_local_image(array $artist): string
{
    if (!empty($artist['image_path']) && is_file(__DIR__ . '/' . $artist['image_path'])) {
        return $artist['image_path'];
    }
    return find_artist_image($artist['name']);
}

/**
 * Bild für die Anzeige: Deezer-Bild, ansonsten lokales Bild.
 * Deezer liefert dasselbe Bild in mehreren Größen (…/1000x1000-…, …/500x500-…, …/250x250-…);
 * für kleine Darstellungen reicht 500 (ca. 23 KB statt 120 KB).
 */
function artist_image(array $artist, int $size = 1000): string
{
    if (empty($artist['image_url'])) {
        return artist_local_image($artist);
    }
    return $size === 1000
        ? $artist['image_url']
        : str_replace('/1000x1000-', "/{$size}x{$size}-", $artist['image_url']);
}

/**
 * <img>-Tag mit Fallback: Lädt das Deezer-Bild im Browser nicht, wird das lokale Bild gezeigt.
 */
function artist_img_tag(array $artist, string $attrs = '', int $size = 1000): string
{
    $local = artist_local_image($artist);
    return '<img src="' . e(artist_image($artist, $size)) . '" alt="' . e($artist['name']) . '"'
         . ' data-fallback="' . e($local) . '"'
         . ' onerror="this.onerror=null;this.src=this.dataset.fallback"'
         . ($attrs !== '' ? ' ' . $attrs : '') . '>';
}

/** Kurzform für Listen: „24,1 Mio.“, „547 Tsd.“ */
function format_fans_short($fans): string
{
    $fans = (int)$fans;
    if ($fans >= 1_000_000) {
        return number_format($fans / 1_000_000, 1, ',', '.') . ' Mio.';
    }
    if ($fans >= 1_000) {
        return number_format($fans / 1_000, 0, ',', '.') . ' Tsd.';
    }
    return (string)$fans;
}

/** Fan-Zahl im deutschen Format, „–“ wenn noch nicht geladen. */
function format_fans($fans): string
{
    return $fans === null ? '–' : number_format((int)$fans, 0, ',', '.');
}
