-- Migration 005 – Zwischenspeicher für API-Daten der Startseite
--
-- Speichert z. B. die Top Songs der Deezer-Playlist „Top Worldwide“ (cache_key = 'top_tracks')
-- als JSON; sie werden einmal am Tag neu geladen (siehe top_tracks() in artist_api.php).
--
-- Kann gefahrlos mehrmals ausgeführt werden.

CREATE TABLE IF NOT EXISTS `api_cache` (
  `cache_key`  VARCHAR(64) NOT NULL,
  `payload`    MEDIUMTEXT  NOT NULL COMMENT 'JSON',
  `updated_at` DATETIME    NOT NULL,
  PRIMARY KEY (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
