<?php
require_once 'security.php';
require_once 'artist_api.php';
include 'db_connect.php';

// Alle Künstler aus `artists`; veraltete Fans/Bilder werden bis zu 3 s lang bei Deezer
// neu geladen, der Rest kommt aus dem Cache. Wikipedia wird nur auf der Detailseite abgefragt.
$artists = artist_all($conn);
$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alle Künstler</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #121212;
            color: white;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #1db954;
            margin-bottom: 40px;
            font-size: 2.5rem;
        }
        .artist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .artist-card {
            display: block;
            background: #181818;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            color: inherit;
            text-decoration: none;
        }
        .artist-card:focus-visible {
            outline: 2px solid #1db954;
            outline-offset: 2px;
        }
        .artist-fans {
            margin-top: 4px;
            font-size: 0.85rem;
            color: #b3b3b3;
        }
        .data-source {
            text-align: center;
            margin-top: 30px;
            font-size: 0.8rem;
            color: #777;
        }
        .data-source a {
            color: #999;
        }
        .artist-card:hover {
            background: #282828;
            transform: translateY(-5px);
        }
        .image-container {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 50%; 
            overflow: hidden;
            margin-bottom: 15px;
            background-color: #333;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        }
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .artist-name {
            font-size: 1rem;
            font-weight: bold;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .back-btn {
            display: block;
            width: fit-content;
            margin: 50px auto;
            background: #1db954;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .back-btn:hover {
            transform: scale(1.05);
            background: #1ed760;
        }
    </style>
</head>
<body>

<h1>Künstler Mediathek</h1>

<div class="artist-grid">
    <?php if ($artists): ?>
        <?php foreach ($artists as $artist): ?>
            <a class="artist-card" href="artist.php?id=<?= (int)$artist['id'] ?>">
                <div class="image-container">
                    <?= artist_img_tag($artist, 'loading="lazy"', 500) ?>
                </div>
                <div class="artist-name"><?= e($artist['name']) ?></div>
                <div class="artist-fans"><?= e(format_fans($artist['fans'])) ?> Fans</div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center;">Keine Künstler gefunden.</p>
    <?php endif; ?>
</div>

<p class="data-source">
    Fans und Bilder: <a href="https://www.deezer.com" target="_blank" rel="noopener">Deezer</a> ·
    Beschreibungen: Wikipedia (<a href="https://creativecommons.org/licenses/by-sa/4.0/deed.de" target="_blank" rel="noopener">CC BY-SA 4.0</a>)
</p>

<a href="index.php" class="back-btn">Zurück zum Dashboard</a>

</body>
</html>