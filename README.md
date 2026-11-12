# 🎵 Spotify Higher Lower Game

Ein interaktives Web-Spiel rund um Musik-Künstler. Teste dein Wissen darüber, welcher Künstler mehr Fans hat! Dazu gibt es eine Künstler-Übersicht mit Detailseiten und aktuelle Charts, deren Songs man direkt auf der Startseite anhören kann. Das Projekt ist für die Ausführung auf PHP-basierten Webservern optimiert.

## 🚀 Live Demo

Das Spiel ist online über **InfinityFree** erreichbar:

👉 [**ZUM LIVE-SPIEL**](https://artistquiz.rf.gd/)

## 📸 Preview

| Startseite | In-Game Vergleich |
| :---: | :---: |
| ![Startseite](image/PreviewStartseite.png) | ![Gameplay](image/PreviewHigherLowerGame.png) |
| **Künstler** | **Song-Player** |
| ![Künstler](image/PreviewArtists.png) | ![Song-Player](image/PreviewSongPlayer.png) |

## 🎮 Features

* **Higher Lower Game:** Vergleiche die Deezer-Fan-Zahlen zweier zufällig gewählter Künstler und sammle Punkte für jede richtige Antwort.
* **Leaderboard:** Speichere deinen Score nach dem Spiel und vergleiche dich mit anderen.
* **Künstler-Mediathek:** Übersicht aller Künstler mit Bild und Fan-Zahl; jede Karte führt zur Detailseite (`artist.php?id=N`) mit Kurzbeschreibung.
* **Song-Player:** Ein Klick auf einen der Top Songs der Startseite öffnet unten eine Player-Leiste mit der 30-Sekunden-Vorschau von Deezer (Vor/Zurück, Spulen, Lautstärke, Autoplay des nächsten Songs, Leertaste für Play/Pause).
* **Aktuelle Startseite:** Top Songs aus den weltweiten Deezer-Charts, Top Künstler nach Fans; Künstler in der Liste und im Karussell führen zur Detailseite.
* **Spotify Design:** Modernes Dark-Mode Interface im typischen Spotify-Stil.
* **Responsive Layout:** Funktioniert auf Smartphones, Tablets und Desktop-PCs.

## 🛠️ Technische Details

* **Sprachen:** PHP 8.x, HTML5, CSS3, JavaScript.
* **Datenbank:** MySQL/MariaDB für Künstler, Charts und Leaderboard sowie als Cache für die API-Daten.
* **Externe APIs:** Deezer und Wikipedia, ohne API-Schlüssel per cURL (siehe Datenquellen).
* **Hosting:** Veröffentlicht via InfinityFree Webhosting.
* **Sicherheit:** Prepared Statements, CSRF-Schutz, serverseitige Spiellogik (die Fan-Zahl des zweiten Künstlers steht nie im HTML), Zugangsdaten außerhalb des Repositorys.

## ⚙️ Lokal einrichten (XAMPP)

1. Projekt nach `C:\xampp\htdocs\artist1` klonen und in XAMPP **Apache** und **MySQL** starten.
2. In phpMyAdmin eine Datenbank `projektm` anlegen und `projektm.sql` sowie `higher_lower.sql` importieren.
3. Die Migrationen aus `db/migrations/` der Reihe nach importieren:
   `001_normalize_artists.sql`, `002_cleanup.sql`, `003_external_api.sql`, `004_bio_updated_at.sql`, danach `005_api_cache.sql`.
4. `config.example.php` nach `config.php` kopieren und die Zugangsdaten eintragen.
   `config.php` wird **nicht** ins Repository übernommen.
5. Im Browser `http://localhost/artist1/` öffnen.

## 📚 Datenquellen

* **[Deezer API](https://developers.deezer.com/api):** Künstlerbilder (`picture_xl`) und Fan-Zahlen (`nb_fan`);
  auf der Startseite die Top Songs aus der Playlist „Top Worldwide“ (täglich aktualisiert) und die Top Künstler nach Fans.
  Die 30-Sekunden-Vorschauen holt `preview.php` erst beim Abspielen, weil Deezer die MP3-Links nur ca. 15 Minuten gültig signiert.
* **[Wikipedia REST API](https://de.wikipedia.org/api/rest_v1/):** Kurzbeschreibungen der Künstler, zuerst aus der deutschen, sonst aus der englischen Wikipedia.
  Die Texte stehen unter der Lizenz [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/deed.de); jede Detailseite verlinkt den Quellartikel.

Die Daten werden in der Tabelle `artists` gecacht und erst nach 7 Tagen neu abgerufen (`artist_api.php`).
Ist eine API nicht erreichbar, bleiben die zuletzt gespeicherten Daten bzw. das lokale Bild aus `image/` stehen.
Die Deezer-IDs sind in `003_external_api.sql` fest eingetragen. Bei mehrdeutigen Namen gibt die Spalte `wiki_title` den Wikipedia-Artikel vor (z. B. `de:Lalisa Manobal`).

## 👥 Projekt-Team

Dieses Projekt ist als Gruppenprojekt entstanden:

* **Saltuk Kalender**: Song-Player sowie spätere Überarbeitung und Verbesserungen
* **Chris**: Higher Lower Game
* **Yuki**: Startseite

---
*Hinweis: Dies ist ein Fan-Projekt zu Bildungszwecken und steht in keiner offiziellen Verbindung zu Spotify, Deezer oder Wikipedia.*
