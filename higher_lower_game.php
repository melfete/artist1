<?php
session_start(); 

include 'db_connect.php'; 

if (!isset($_SESSION["score"])) {
    $_SESSION["score"] = 0;
}

if (!isset($_SESSION["current_artist"]) || !isset($_SESSION["next_artist"])) {
    $sql = "SELECT * FROM `künstler` ORDER BY RAND() LIMIT 2";
    $result = $conn->query($sql);

    if ($result === false) {
        die("SQL-Fehler: " . $conn->error);
    }

    if ($result->num_rows >= 2) {
        $artists = $result->fetch_all(MYSQLI_ASSOC);
        $_SESSION["current_artist"] = $artists[0]; 
        $_SESSION["next_artist"] = $artists[1]; 
    } else {
        die("Fehler: Nicht genug Künstler in der Datenbank.");
    }
}

if (!isset($_SESSION["next_artist"])) {
    $sql = "SELECT * FROM `künstler` ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);

    if ($result === false) {
        die("SQL-Fehler: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $_SESSION["next_artist"] = $result->fetch_assoc();
    } else {
        die("Fehler: Kein neuer Künstler gefunden.");
    }
}

$artist1 = $_SESSION["current_artist"];
$artist2 = $_SESSION["next_artist"];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Higher Lower Game</title>
    <style>
        .artist-box { 
            display: inline-block; 
            width: 45%; 
            text-align: center; 
            vertical-align: top; 
            margin: 10px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        .artist-box img { 
            width: 250px; 
            height: 250px; 
            object-fit: cover; 
            border-radius: 8px; 
        }
        button { padding: 10px 20px; font-size: 18px; cursor: pointer; }
    </style>
</head>
<body>

<h2 style="text-align: center;">Higher or Lower Game</h2>
<p style="text-align: center;"><strong>Score:</strong> <?= $_SESSION["score"] ?></p>

<div style="text-align: center;">
    <div class="artist-box">
        <h3>Künstler 1</h3>
        <img src="artists/<?= htmlspecialchars($artist1["künstler_name"]) ?>.jpg" alt="<?= htmlspecialchars($artist1["künstler_name"]) ?>">
        <p><strong>Name:</strong> <?= htmlspecialchars($artist1["künstler_name"]) ?></p>
        <p><strong>Song:</strong> <?= htmlspecialchars($artist1["song"]) ?></p>
        <p><strong>Streams:</strong> <?= number_format($artist1["künstler_streams"]) ?></p>
    </div>

    <div class="artist-box">
        <h3>Künstler 2</h3>
        <img src="artists/<?= htmlspecialchars($artist2["künstler_name"]) ?>.jpg" alt="<?= htmlspecialchars($artist2["künstler_name"]) ?>">
        <p><strong>Name:</strong> <?= htmlspecialchars($artist2["künstler_name"]) ?></p>
        <p><strong>Song:</strong> <?= htmlspecialchars($artist2["song"]) ?></p>
        <p><strong>Streams:</strong> ???</p>
    </div>
</div>

<div style="text-align: center; margin-top: 20px;">
    <form method="post" action="check_answer.php">
        <input type="hidden" name="artist1_streams" value="<?= $artist1["künstler_streams"] ?>">
        <input type="hidden" name="artist2_streams" value="<?= $artist2["künstler_streams"] ?>">
        <button type="submit" name="guess" value="higher">Höher</button>
        <button type="submit" name="guess" value="lower">Niedriger</button>
    </form>
</div>

</body>
</html>