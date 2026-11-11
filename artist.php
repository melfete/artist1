<?php
require_once 'security.php';
include 'db_connect.php';

// Ersetzt artist1.php … artist9.php: artist.php?id=1 … artist.php?id=9
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);
if (!$id) {
    header('Location: artists.php');
    exit();
}

// Tabellennamen lassen sich nicht als Prepared-Statement-Parameter übergeben,
// deshalb nur aus der geprüften Zahl 1–9 zusammensetzen.
$result = $conn->query('SELECT * FROM `artist' . $id . '`');
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();

function picture_data_uri(string $blob): string
{
    $mime = 'image/jpeg';
    if (function_exists('finfo_open')) {
        $detected = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $blob);
        if (is_string($detected) && strpos($detected, 'image/') === 0) {
            $mime = $detected;
        }
    }
    return 'data:' . $mime . ';base64,' . base64_encode($blob);
}

$title = $rows ? $rows[0]['name'] : 'Künstler';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="artist.css">
</head>
<body>

<?php if (!$rows): ?>
    <p>No Artist found</p>
<?php endif; ?>

<?php foreach ($rows as $row): ?>
    <div class="artist-container">
        <div class="artist-text">
            <p><strong>Name:</strong> <?= e($row['name']) ?></p>
            <p><strong>Age:</strong> <?= e($row['age']) ?></p>
            <p><strong>Streams:</strong> <?= isset($row['streams']) ? e($row['streams']) : 'N/A' ?></p>
            <p><strong>Most Streamed:</strong> <?= e($row['most_streamed']) ?></p>

            <?php if (!empty($row['newessong'])): ?>
                <p><strong>Newest Song:</strong> <?= e($row['newessong']) ?></p>
            <?php endif; ?>

            <?php
            $albums = [];
            for ($i = 1; $i <= 10; $i++) {
                if (!empty($row['album' . $i])) {
                    $albums[] = $row['album' . $i];
                }
            }
            ?>
            <?php if ($albums): ?>
                <p><strong>Albums:</strong></p>
                <?php foreach ($albums as $album): ?>
                    <p><?= e($album) ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($row['picture'])): ?>
            <div class="artist-image">
                <img src="<?= picture_data_uri($row['picture']) ?>" width="200" alt="<?= e($row['name']) ?>">
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<form action="artists.php">
    <button>Back</button>
</form>

</body>
</html>
