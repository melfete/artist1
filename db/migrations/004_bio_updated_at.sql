-- Migration 004 – eigener Zeitstempel für die Wikipedia-Beschreibung
--
-- Übersicht und Spiel laden nur noch Fans/Bild von Deezer (parallel, schnell);
-- die Wikipedia-Beschreibung wird nur auf der Detailseite geladen. Dafür braucht
-- jede Quelle ihren eigenen Cache-Zeitstempel:
--   updated_at     = letzter Deezer-Abruf
--   bio_updated_at = letzter Wikipedia-Abruf
--
-- Voraussetzung: 003 ist eingespielt. Nur einmal ausführen.

ALTER TABLE `artists`
  MODIFY `updated_at` DATETIME NULL COMMENT 'Letzter Deezer-Abruf (Fans, Bild)',
  ADD COLUMN `bio_updated_at` DATETIME NULL COMMENT 'Letzter Wikipedia-Abruf' AFTER `updated_at`;

-- Bereits geladene Beschreibungen gelten als aktuell
UPDATE `artists` SET `bio_updated_at` = `updated_at` WHERE `updated_at` IS NOT NULL;
