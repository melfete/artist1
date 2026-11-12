-- Migration 003 – Daten aus externen APIs (Deezer, Wikipedia) in `artists` cachen
--
-- 1. Neue Spalten: deezer_id, fans, image_url, bio, bio_url, wiki_title, updated_at
--    bio_url    = Link auf den Wikipedia-Artikel, nötig für die CC-BY-SA-Quellenangabe
--    wiki_title = optional fester Artikel bei mehrdeutigen Namen, Format „sprache:Titel“
-- 2. age / streams_mio / most_streamed dürfen leer sein (für die neu übernommenen Künstler unbekannt)
-- 3. Deezer-IDs fest eintragen – die Deezer-Suche liefert sonst teils falsche Treffer
--    (z. B. „Aleman“ mit 26 Fans statt „Alemán“, „Jamal“ ist ein anderer Künstler)
-- 4. Die Künstler aus `künstler` (Higher Lower Game) als eigene Zeilen in `artists` übernehmen,
--    damit Spiel, Übersicht und Detailseite dieselbe Tabelle nutzen
--
-- fans, image_url, bio und updated_at bleiben hier leer; sie werden beim ersten Aufruf
-- von der Seite selbst geladen und danach 7 Tage gecacht (siehe artist_api.php).
--
-- Voraussetzung: 001 ist eingespielt. Nur einmal ausführen (ALTER TABLE ist nicht wiederholbar).
-- Import in phpMyAdmin: links die Datenbank anklicken → Import → diese Datei.

SET NAMES utf8mb4;

ALTER TABLE `artists`
  MODIFY `age`           TINYINT UNSIGNED NULL,
  MODIFY `streams_mio`   DECIMAL(10,2)    NULL COMMENT 'Monatliche Hörer in Millionen',
  MODIFY `most_streamed` VARCHAR(50)      NULL,
  ADD COLUMN `deezer_id`  BIGINT UNSIGNED NULL AFTER `name`,
  ADD COLUMN `fans`       INT UNSIGNED    NULL COMMENT 'Deezer nb_fan',
  ADD COLUMN `image_url`  VARCHAR(255)    NULL COMMENT 'Deezer picture_xl',
  ADD COLUMN `bio`        TEXT            NULL COMMENT 'Wikipedia-Kurzbeschreibung (CC BY-SA 4.0)',
  ADD COLUMN `bio_url`    VARCHAR(255)    NULL COMMENT 'Quelle der Kurzbeschreibung',
  ADD COLUMN `wiki_title` VARCHAR(255)    NULL COMMENT 'Optional: fester Wikipedia-Artikel, z. B. de:Lalisa Manobal',
  ADD COLUMN `updated_at` DATETIME        NULL COMMENT 'Letzter erfolgreicher Abruf',
  ADD UNIQUE KEY `uq_artists_deezer_id` (`deezer_id`),
  ADD INDEX `idx_artists_updated_at` (`updated_at`);

-- Deezer-IDs der bestehenden Künstler
UPDATE `artists` SET `deezer_id` = CASE `id`
  WHEN 1 THEN 5719412    -- reezy
  WHEN 2 THEN 525046     -- Kendrick Lamar
  WHEN 3 THEN 259        -- Michael Jackson
  WHEN 4 THEN 492716     -- RIN
  WHEN 5 THEN 153996392  -- Jazeek
  WHEN 6 THEN 1355105    -- Nimo
  WHEN 7 THEN 12632197   -- Luciano
  WHEN 8 THEN 3574561    -- Xatar
  WHEN 9 THEN 248376642  -- Jamal
END
WHERE `id` BETWEEN 1 AND 9;

-- Künstler aus `künstler` (Kendrick Lamar ist bereits id 2).
-- most_streamed = Song mit den meisten Streams aus `künstler`.
INSERT IGNORE INTO `artists` (`id`, `name`, `deezer_id`, `most_streamed`, `image_path`) VALUES
  (10, 'Billie Eilish',      9635624,   'BIRDS OF A FEATHER',           'image/billieeilish.png'),
  (11, 'Sabrina Carpenter',  1176900,   'Taste',                        'image/sabrinacarpenter.png'),
  (12, 'Vance Joy',          4768525,   'Riptide',                      'image/vancejoy.png'),
  (13, 'Drake',              246791,    'Gods Plan',                    'image/drake.png'),
  (14, 'Lady Gaga',          75491,     'Die With a Smile',             'image/ladygaga.png'),
  (15, 'ROSÉ',               126335112, 'APT',                          'image/rose.png'),
  (16, 'Bad Bunny',          10583405,  'DtMF',                         'image/badbunny.png'),
  (17, 'Gracie Abrams',      64874492,  'Thats So True',                'image/gracieabrams.jpg'),
  (18, 'The Weeknd',         4050205,   'Timeless',                     'image/theweekend.png'),
  (19, 'Lola Young',         9266850,   'Messy',                        'image/lolayoung.png'),
  (20, 'Chappell Roan',      12945219,  'Good Luck, Babe!',             'image/chappellroan.png'),
  (21, 'Jimin',              168047437, 'Who',                          'image/jimin.png'),
  (22, 'LISA',               145068682, 'Born Again',                   'image/lisa.png'),
  (23, 'Neton Vega',         212949977, 'Loco',                         'image/netonvega.png'),
  (24, 'Gigi Perez',         95858302,  'Sailor Song',                  'image/gigiperez.png'),
  (25, 'Doechii',            51965252,  'DENIAL IS A RIVER',            'image/doechii.png'),
  (26, 'Benson Boone',       146543992, 'Beautiful Things',             'image/bensonboone.png'),
  (27, 'SZA',                5531258,   '30 For 30',                    'image/sza.png'),
  (28, 'Gabito Ballesteros', 7583722,   '7 Dias',                       'image/gabitoballesteros.jpg'),
  (29, 'Tyler, The Creator', 1194083,   'Like Him',                     'image/tyler.png'),
  (30, 'Rauw Alejandro',     11289472,  'Que Pasaria',                  'image/rauwalejandro.png'),
  (31, 'Lord Huron',         887565,    'The Night We Met',             'image/lordhuron.png'),
  (32, 'Alemán',             4951751,   'Te Queria Ver',                'image/aleman.png'),
  (33, 'KAROL G',            5297021,   'Si Antes Te Hubiera Conocido', 'image/karolg.png'),
  (34, 'Tate McRae',         13817211,  'Sports Car',                   'image/tatemcrae.png'),
  (35, 'Teddy Swims',        64927672,  'Lose Control',                 'image/teddyswims.png'),
  (36, 'yung kai',           49777742,  'blue',                         'image/yungkai.png'),
  (37, 'alleh',              119913392, 'capaz',                        'image/alleh.png'),
  (38, 'Tito Double P',      216346095, 'Rosones',                      'image/titodoublep.png'),
  (39, 'Linkin Park',        92,        'The Emptiness Machine',        'image/linkinpark.png'),
  (40, 'The Neighbourhood',  296861,    'Sweather weather',             'image/theneighbourhood.png');

-- Mehrdeutige Namen: Wikipedia-Artikel fest vorgeben
--   „LISA“ fände sonst die japanische Sängerin LiSA, „Alemán (rapper)“ findet die Suche nicht
UPDATE `artists` SET `wiki_title` = 'de:Lalisa Manobal'  WHERE `id` = 22;
UPDATE `artists` SET `wiki_title` = 'en:Alemán (rapper)' WHERE `id` = 32;
