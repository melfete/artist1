<?php
require_once 'security.php';
require_once 'artist_api.php';
include 'db_connect.php';

// Top Künstler und Karussell: Künstler mit den meisten Deezer-Fans (aus dem Cache, kein API-Abruf).
// Top Songs: Deezer-Playlist „Top Worldwide“, einmal am Tag neu geladen.
// Solange noch keine API-Daten vorliegen, greifen die bisherigen festen Listen.
$topArtists = top_artists($conn, 12);
$topTracks  = top_tracks($conn);

$carouselArtists = $topArtists;
if (!$carouselArtists) {
    foreach (["reezy", "Kendrick Lamar", "Michael Jackson", "RIN", "Jazeek", "Nimo",
              "Luciano", "Xatar", "Jamal", "Sabrina Carpenter", "Billie Eilish", "Bad Bunny"] as $name) {
        $carouselArtists[] = ['name' => $name, 'image_url' => null, 'image_path' => null];
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify Artist Website</title>
    <style>
        :root {
            --spotify-green: #1db954;
            --bg-dark: #121212;
            --card-bg: #181818;
            --card-hover: #282828;
            --text-main: #ffffff;
            --text-dim: #b3b3b3;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        h1 {
            text-align: center;
            margin-top: 40px;
            font-size: 2.5rem;
            color: var(--spotify-green);
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 40px 0;
            flex-wrap: wrap;
        }

        .nav-button {
            padding: 15px 35px;
            font-size: 1.1rem;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .game-button { background-color: var(--spotify-green); color: white; }
        .artist-button { background-color: #333; color: white; }
        .music-button { background-color: #333; color: white; }

        .nav-button:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }

        .carousel {
            width: 100%;
            overflow: hidden;
            background: #000;
            padding: 30px 0;
            margin-bottom: 50px;
            position: relative;
        }

        .image-track {
            display: flex;
            width: max-content;
            animation: scroll 40s linear infinite;
        }

        .image-track img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            margin: 0 25px;
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0,0,0,0.5);
            flex-shrink: 0;
        }

        @keyframes scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        #news {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .news-column {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 15px;
            transition: background 0.3s;
        }

        .news-column:hover {
            background: var(--card-hover);
        }

        .news-column h2 {
            color: var(--spotify-green);
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-top: 0;
        }

        .news-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #222;
        }

        .news-item:last-child { border-bottom: none; }

        .rank { color: var(--spotify-green); font-weight: bold; margin-right: 10px; }
        .name { flex-grow: 1; }
        .detail { color: var(--text-dim); font-size: 0.9rem; }
        .name a { color: inherit; text-decoration: none; }
        .name a:hover { text-decoration: underline; }
        .source { margin: 12px 0 0; color: #666; font-size: 0.75rem; }

        #faq {
            max-width: 800px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .faq-item {
            background: var(--card-bg);
            margin-bottom: 10px;
            border-radius: 8px;
            overflow: hidden;
        }

        .faq-question {
            width: 100%;
            background: none;
            border: none;
            color: white;
            padding: 20px;
            text-align: left;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-answer {
            padding: 0 20px;
            display: none;
            color: var(--text-dim);
            padding-bottom: 20px;
        }

        footer {
            text-align: center;
            padding: 40px;
            color: var(--text-dim);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<h1>Willkommen in der Mediathek</h1>

<div class="button-container">
    <a href="higher_lower_startpage.php" class="nav-button game-button">Higher Lower Game</a>
    <a href="artists.php" class="nav-button artist-button">Künstler Entdecken</a>
    <a href="music_player.php" class="nav-button music-button">Music Player</a>
</div>

<div class="carousel">
    <div class="image-track">
        <?php
        // Zweimal hintereinander, damit die Endlos-Animation (translateX -50 %) nahtlos läuft
        for ($i = 0; $i < 2; $i++) {
            foreach ($carouselArtists as $artist) {
                echo artist_img_tag($artist, '', 500);
            }
        }
        ?>
    </div>
</div>

<div id="news">
    <div class="news-column">
        <h2>Top Songs</h2>
        <?php if ($topTracks): ?>
            <?php foreach ($topTracks as $i => $track): ?>
                <div class="news-item">
                    <span class="rank">#<?= $i + 1 ?></span>
                    <span class="name">
                        <?php if ($track['link']): ?>
                            <a href="<?= e($track['link']) ?>" target="_blank" rel="noopener"><?= e($track['title']) ?></a>
                        <?php else: ?>
                            <?= e($track['title']) ?>
                        <?php endif; ?>
                    </span>
                    <span class="detail"><?= e($track['artist']) ?></span>
                </div>
            <?php endforeach; ?>
            <p class="source">Deezer-Playlist „Top Worldwide“, täglich aktualisiert</p>
        <?php else: ?>
            <?php
            $sql = "SELECT place, name, artist FROM topsongs ORDER BY place ASC LIMIT 10";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='news-item'><span class='rank'>#" . (int)$row['place'] . "</span> <span class='name'>" . htmlspecialchars($row['name']) . "</span> <span class='detail'>" . htmlspecialchars($row['artist']) . "</span></div>";
                }
            }
            ?>
        <?php endif; ?>
    </div>

    <div class="news-column">
        <h2>Top Alben</h2>
        <?php
        $sql = "SELECT place, name, artist FROM topalbums ORDER BY place ASC LIMIT 10";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='news-item'><span class='rank'>#" . (int)$row['place'] . "</span> <span class='name'>" . htmlspecialchars($row['name']) . "</span> <span class='detail'>" . htmlspecialchars($row['artist']) . "</span></div>";
            }
        }
        ?>
    </div>

    <div class="news-column">
        <h2>Top Künstler</h2>
        <?php if ($topArtists): ?>
            <?php foreach (array_slice($topArtists, 0, 10) as $i => $artist): ?>
                <div class="news-item">
                    <span class="rank">#<?= $i + 1 ?></span>
                    <span class="name"><a href="artist.php?id=<?= (int)$artist['id'] ?>"><?= e($artist['name']) ?></a></span>
                    <span class="detail"><?= e(format_fans_short($artist['fans'])) ?> Fans</span>
                </div>
            <?php endforeach; ?>
            <p class="source">Nach Deezer-Fans, aus den Künstlern im Spiel</p>
        <?php else: ?>
            <?php
            $sql = "SELECT place, name, streams FROM topartist ORDER BY place ASC LIMIT 10";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='news-item'><span class='rank'>#" . (int)$row['place'] . "</span> <span class='name'>" . htmlspecialchars($row['name']) . "</span> <span class='detail'>" . htmlspecialchars($row['streams']) . " Mio.</span></div>";
                }
            }
            ?>
        <?php endif; ?>
    </div>
</div>

<div id="faq">
    <h2>Häufig gestellte Fragen</h2>
    <div class="faq-item">
        <button class="faq-question">Wer hat an diesem Projekt gearbeitet? <span>+</span></button>
        <div class="faq-answer">
            <p><strong>Saltuk Kalender</strong>: Music Player sowie spätere Überarbeitung und Verbesserungen</p>
            <p><strong>Chris</strong>: Higher Lower Game</p>
            <p><strong>Yuki</strong>: Startseite</p>
        </div>
    </div>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> Spotify Artist Project
</footer>

<script>
document.querySelectorAll('.faq-question').forEach(item => {
    item.addEventListener('click', function() {
        let answer = this.nextElementSibling;
        let icon = this.querySelector('span');
        if (answer.style.display === "block") {
            answer.style.display = "none";
            icon.innerText = "+";
        } else {
            answer.style.display = "block";
            icon.innerText = "-";
        }
    });
});
</script>

</body>
</html>