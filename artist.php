<?php
require_once 'security.php';
require_once 'artist_api.php';
include 'db_connect.php';

// Künstlerseite: artist.php?id=1 … (Daten aus `artists` + `albums`; Fans/Bild von Deezer,
// Kurzbeschreibung von Wikipedia – gecacht, siehe artist_api.php und db/migrations/003)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    header('Location: artists.php');
    exit();
}

$artist = artist_find($conn, $id);

$albums = [];
if ($artist) {
    $stmt = $conn->prepare('SELECT title FROM albums WHERE artist_id = ? ORDER BY position');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $albums = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'title');
    $stmt->close();
}
$conn->close();

if (!$artist) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($artist['name'] ?? 'Künstler nicht gefunden') ?> | Artist Quiz</title>
    <link rel="stylesheet" href="artist.css">
</head>
<body>

<?php if (!$artist): ?>
    <p class="not-found">Künstler nicht gefunden.</p>
<?php else: ?>
    <div class="artist-container">
        <div class="artist-image">
            <?= artist_img_tag($artist) ?>
        </div>

        <div class="artist-text">
            <h1><?= e($artist['name']) ?></h1>

            <p class="fans">
                <span class="fans-number"><?= e(format_fans($artist['fans'])) ?></span> Fans auf Deezer
            </p>

            <?php if (!empty($artist['bio'])): ?>
                <p class="bio"><?= e($artist['bio']) ?></p>
                <?php if (!empty($artist['bio_url'])): ?>
                    <p class="source">
                        Quelle: <a href="<?= e($artist['bio_url']) ?>" target="_blank" rel="noopener">Wikipedia</a>,
                        Lizenz <a href="https://creativecommons.org/licenses/by-sa/4.0/deed.de" target="_blank" rel="noopener">CC BY-SA 4.0</a>
                    </p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($artist['age'] !== null): ?>
                <p><strong>Alter:</strong> <?= e($artist['age']) ?></p>
            <?php endif; ?>
            <?php if (!empty($artist['most_streamed'])): ?>
                <p><strong>Bekanntester Song:</strong> <?= e($artist['most_streamed']) ?></p>
            <?php endif; ?>
            <?php if (!empty($artist['newest_song'])): ?>
                <p><strong>Neuester Song:</strong> <?= e($artist['newest_song']) ?></p>
            <?php endif; ?>

            <?php if ($albums): ?>
                <p><strong>Alben:</strong></p>
                <ul class="albums">
                    <?php foreach ($albums as $album): ?>
                        <li><?= e($album) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($artist['deezer_id'])): ?>
                <p class="source">
                    Fans und Bild: <a href="https://www.deezer.com/artist/<?= (int)$artist['deezer_id'] ?>" target="_blank" rel="noopener">Deezer</a>
                    <?php if (!empty($artist['updated_at'])): ?>
                        · Stand <?= e(date('d.m.Y', strtotime($artist['updated_at']))) ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<form action="artists.php">
    <button>Zurück zur Übersicht</button>
</form>

</body>
</html>
