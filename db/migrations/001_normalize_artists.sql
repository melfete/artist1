-- Migration 001
-- 1. artist1 … artist9 → eine Tabelle `artists` + Tabelle `albums`
--    Bilder als Dateipfad statt BLOB (die BLOBs sind byte-identisch mit image/*.jpg)
-- 2. `künstler`.`künstler_streams` INT → BIGINT UNSIGNED
-- 3. Index auf `leaderboard`.`score`
--
-- Import in phpMyAdmin: links die Datenbank anklicken → Import → diese Datei.
-- Die alten Tabellen bleiben erhalten; gelöscht werden sie erst mit 002_cleanup.sql.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `artists` (
  `id`            INT UNSIGNED     NOT NULL,
  `name`          VARCHAR(50)      NOT NULL,
  `age`           TINYINT UNSIGNED NOT NULL,
  `streams_mio`   DECIMAL(10,2)    NOT NULL COMMENT 'Monatliche Hörer in Millionen',
  `most_streamed` VARCHAR(50)      NOT NULL,
  `newest_song`   VARCHAR(50)      NULL,
  `image_path`    VARCHAR(255)     NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `albums` (
  `id`        INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `artist_id` INT UNSIGNED     NOT NULL,
  `position`  TINYINT UNSIGNED NOT NULL,
  `title`     VARCHAR(50)      NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_albums_artist_position` (`artist_id`, `position`),
  CONSTRAINT `fk_albums_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Daten übernehmen (INSERT IGNORE: erneutes Ausführen erzeugt keine Duplikate)
INSERT IGNORE INTO `artists` (`id`, `name`, `age`, `streams_mio`, `most_streamed`, `newest_song`, `image_path`)
          SELECT 1, `name`, `age`, `streams`, `most_streamed`, NULLIF(`newessong`, ''), 'image/reezy.jpg'          FROM `artist1`
UNION ALL SELECT 2, `name`, `age`, `streams`, `most_streamed`, NULL,                     'image/kendricklamar.jpg'  FROM `artist2`
UNION ALL SELECT 3, `name`, `age`, `streams`, `most_streamed`, NULL,                     'image/michaeljackson.jpg' FROM `artist3`
UNION ALL SELECT 4, `name`, `age`, `streams`, `most_streamed`, NULL,                     'image/rin.jpg'            FROM `artist4`
UNION ALL SELECT 5, `name`, `age`, `streams`, `most_streamed`, NULL,                     'image/jazeek.jpg'         FROM `artist5`
UNION ALL SELECT 6, `name`, `age`, `streams`, `most_streamed`, NULL,                     'image/nimo.jpg'           FROM `artist6`
UNION ALL SELECT 7, `name`, `age`, `streams`, `most_streamed`, NULL,                     'image/luciano.jpg'        FROM `artist7`
UNION ALL SELECT 8, `name`, `age`, `streams`, `most_streamed`, NULL,                     'image/xatar.jpg'          FROM `artist8`
UNION ALL SELECT 9, `name`, `age`, `streams`, `most_streamed`, NULL,                     'image/jamal.jpg'          FROM `artist9`;

-- Nur artist1 hat Album-Spalten
INSERT IGNORE INTO `albums` (`artist_id`, `position`, `title`)
          SELECT 1,  1, `album1`  FROM `artist1` WHERE `album1`  <> ''
UNION ALL SELECT 1,  2, `album2`  FROM `artist1` WHERE `album2`  <> ''
UNION ALL SELECT 1,  3, `album3`  FROM `artist1` WHERE `album3`  <> ''
UNION ALL SELECT 1,  4, `album4`  FROM `artist1` WHERE `album4`  <> ''
UNION ALL SELECT 1,  5, `album5`  FROM `artist1` WHERE `album5`  <> ''
UNION ALL SELECT 1,  6, `album6`  FROM `artist1` WHERE `album6`  <> ''
UNION ALL SELECT 1,  7, `album7`  FROM `artist1` WHERE `album7`  <> ''
UNION ALL SELECT 1,  8, `album8`  FROM `artist1` WHERE `album8`  <> ''
UNION ALL SELECT 1,  9, `album9`  FROM `artist1` WHERE `album9`  <> ''
UNION ALL SELECT 1, 10, `album10` FROM `artist1` WHERE `album10` <> '';

-- Streams: INT (max. ~2,1 Mrd.) → BIGINT UNSIGNED
ALTER TABLE `künstler` MODIFY `künstler_streams` BIGINT UNSIGNED NOT NULL;

-- Leaderboard: ORDER BY score DESC LIMIT 50 per Index statt Volltabellen-Sortierung
ALTER TABLE `leaderboard` ADD INDEX `idx_leaderboard_score` (`score`);
