<?php
// Gemeinsame Helfer für mehrere Seiten.

const DEFAULT_ARTIST_IMAGE = 'image/logo.png';

/**
 * Sucht das Bild zu einem Künstlernamen im Ordner image/.
 * Probiert "kendricklamar.png", dann "kendrick_lamar.png", jeweils mit mehreren Endungen.
 *
 * @param string      $name     Künstlername, z. B. "Kendrick Lamar"
 * @param string|null $fallback Rückgabewert, wenn kein Bild existiert (null = "nicht gefunden")
 * @return string|null Relativer Pfad wie "image/kendricklamar.png"
 */
function find_artist_image(string $name, ?string $fallback = DEFAULT_ARTIST_IMAGE): ?string
{
    static $cache = [];
    if (array_key_exists($name, $cache)) {
        return $cache[$name] ?? $fallback;
    }

    $cleanName = strtr($name, [
        'Ä' => 'ae', 'Ö' => 'oe', 'Ü' => 'ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'á' => 'a', 'à' => 'a', 'â' => 'a',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ñ' => 'n',
    ]);

    $candidates = [
        strtolower(str_replace([' ', '.', '-'], '', $cleanName)), // "kendricklamar"
        strtolower(str_replace(' ', '_', $cleanName)),            // "kendrick_lamar"
    ];
    $extensions = ['png', 'jpg', 'jpeg', 'webp', 'JPG', 'PNG'];

    $found = null;
    foreach ($candidates as $base) {
        foreach ($extensions as $ext) {
            $relative = 'image/' . $base . '.' . $ext;
            if (is_file(__DIR__ . '/' . $relative)) {
                $found = $relative;
                break 2;
            }
        }
    }

    $cache[$name] = $found;
    return $found ?? $fallback;
}
