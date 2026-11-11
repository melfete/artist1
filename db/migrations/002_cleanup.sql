-- Migration 002 – ERST AUSFÜHREN, wenn 001 eingespielt ist und die Seite funktioniert!
-- Entfernt die durch `artists`/`albums` ersetzten und die ungenutzten Tabellen.
-- Achtung: Löschen ist endgültig. Vorher ggf. in phpMyAdmin einen Export machen.

DROP TABLE IF EXISTS
  `artist1`, `artist2`, `artist3`, `artist4`, `artist5`,
  `artist6`, `artist7`, `artist8`, `artist9`,
  `artisthower`,  -- ungenutzt, dritte Kopie derselben 9 Bilder
  `highscores`;   -- ungenutzt (Scores liegen in `leaderboard`)
