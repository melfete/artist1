<?php
session_start();

$artist1_streams = $_POST["artist1_streams"];
$artist2_streams = $_POST["artist2_streams"];
$guess = $_POST["guess"];

$correct = false;
if ($guess == "higher" && $artist2_streams > $artist1_streams) {
    $correct = true;
} elseif ($guess == "lower" && $artist2_streams < $artist1_streams) {
    $correct = true;
}

if ($correct) {
    $_SESSION["score"] += 1;
    $_SESSION["result"] = "correct"; 

    $_SESSION["current_artist"] = $_SESSION["next_artist"];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "higher_lower";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Verbindung fehlgeschlagen: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM `künstler` ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION["next_artist"] = $result->fetch_assoc();
    } else {
        die("Fehler: Kein neuer Künstler gefunden.");
    }

    $conn->close();

    header("Location: higher_lower_game.php"); 
    exit;
} else {
    header("Location: wrong_answer.php"); 
    exit;
}
?>
