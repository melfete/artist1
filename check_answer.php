<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guess = $_POST["guess"];
    $streams1 = $_POST["artist1_streams"];
    $streams2 = $_POST["artist2_streams"];

    $correct = false;
    if ($guess == "higher" && $streams2 >= $streams1) {
        $correct = true;
    } elseif ($guess == "lower" && $streams2 <= $streams1) {
        $correct = true;
    }

    if ($correct) {
        $_SESSION["score"]++;
        $_SESSION["current_artist"] = $_SESSION["next_artist"];
        unset($_SESSION["next_artist"]);
        header("Location: higher_lower_game.php");
    } else {
        $final_score = $_SESSION["score"];
        $_SESSION["score"] = 0;
        unset($_SESSION["current_artist"]);
        unset($_SESSION["next_artist"]);
        echo "Falsch! Dein Endscore: " . $final_score . " <a href='higher_lower_game.php'>Nochmal versuchen</a>";
    }
}
?>