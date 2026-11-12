<?php
/**
 * Leitet auf die 30-Sekunden-Vorschau eines Deezer-Songs weiter: preview.php?id=<Deezer-Track-ID>.
 *
 * Deezer signiert die MP3-Links nur für ca. 15 Minuten. Deshalb wird der Link nicht gecacht,
 * sondern erst beim Abspielen frisch geholt. Der Player auf der Startseite nutzt die Datei direkt
 * als <audio>-Quelle.
 */
require_once 'artist_api.php';

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$id = $_GET['id'] ?? '';
if (!is_string($id) || !preg_match('/^[1-9][0-9]{0,15}$/', $id)) {
    http_response_code(400);
    exit('Ungültige Song-ID.');
}

$track = api_get_json('https://api.deezer.com/track/' . $id);
$preview = $track['preview'] ?? null;

// Nur auf Deezers eigenes CDN weiterleiten, nie auf beliebige Adressen aus der Antwort
$host = is_string($preview) ? (string)parse_url($preview, PHP_URL_HOST) : '';
if (!str_starts_with((string)$preview, 'https://') || !preg_match('/(^|\.)dzcdn\.net$/', $host)) {
    http_response_code($track === null ? 502 : 404);
    exit('Keine Vorschau verfügbar.');
}

header('Location: ' . $preview, true, 302);
