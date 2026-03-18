<?php
session_start(); 

if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['HTTP_HOST'] == 'localhost') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "projektm"; 
} else {
    $host = "sql207.infinityfree.com"; 
    $user = "if0_41364848";           
    $pass = "pvaN0SHDgBN4ixg";          
    $dbname = "if0_41364848_db_quiz"; 
}    

$conn = new mysqli($host, $user, $pass, $dbname);


if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
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

$conn->close();

$artist1 = $_SESSION["current_artist"];
$artist2 = $_SESSION["next_artist"];

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Higher Lower Game</title>  
</head>
<body>

<h2>Higher or Lower Game</h2>
<p><strong>Score:</strong> <?= $_SESSION["score"] ?></p>

<div class="artist-box">
    <h3>Künstler 1</h3>
    <p><strong>Name:</strong> <?= htmlspecialchars($artist1["künstler_name"]) ?></p>
    <p><strong>Song:</strong> <?= htmlspecialchars($artist1["song"]) ?></p>
    <p><strong>Streams:</strong> <?= number_format($artist1["künstler_streams"]) ?></p>
</div>

<div class="artist-box">
    <h3>Künstler 2</h3>
    <p><strong>Name:</strong> <?= htmlspecialchars($artist2["künstler_name"]) ?></p>
    <p><strong>Song:</strong> <?= htmlspecialchars($artist2["song"]) ?></p>
    <p><strong>Streams:</strong> ???</p>
</div>

<form method="post" action="check_answer.php">
    <input type="hidden" name="artist1_streams" value="<?= $artist1["künstler_streams"] ?>">
    <input type="hidden" name="artist2_streams" value="<?= $artist2["künstler_streams"] ?>">
    <button type="submit" name="guess" value="higher">Höher</button>
    <button type="submit" name="guess" value="lower">Niedriger</button>
</form>

</body>
</html>
