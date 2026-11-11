<?php
require_once 'security.php';
include 'db_connect.php';

// Künstlerseite: artist.php?id=1 … (Daten aus `artists` + `albums`, siehe db/migrations/001)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    header('Location: artists.php');
    exit();
}

$stmt = $conn->prepare(
    'SELECT name, age, streams_mio, most_streamed, newest_song, image_path FROM artists WHERE id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$artist = $stmt->get_result()->fetch_assoc();
$stmt->close();

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
    <title><?= e($artist['name'] ?? 'Künstler nicht gefunden') ?></title>
    <link rel="stylesheet" href="artist.css">
</head>
<body>

<?php if (!$artist): ?>
    <p>No Artist found</p>
<?php else: ?>
    <div class="artist-container">
        <div class="artist-text">
            <p><strong>Name:</strong> <?= e($artist['name']) ?></p>
            <p><strong>Age:</strong> <?= e($artist['age']) ?></p>
            <p><strong>Streams:</strong> <?= e($artist['streams_mio']) ?></p>
            <p><strong>Most Streamed:</strong> <?= e($artist['most_streamed']) ?></p>

            <?php if (!empty($artist['newest_song'])): ?>
                <p><strong>Newest Song:</strong> <?= e($artist['newest_song']) ?></p>
            <?php endif; ?>

            <?php if ($albums): ?>
                <p><strong>Albums:</strong></p>
                <?php foreach ($albums as $album): ?>
                    <p><?= e($album) ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="artist-image">
            <img src="<?= e($artist['image_path']) ?>" width="200" alt="<?= e($artist['name']) ?>">
        </div>
    </div>
<?php endif; ?>

<form action="artists.php">
    <button>Back</button>
</form>

</body>
</html>
