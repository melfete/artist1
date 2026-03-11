<?php
session_start(); 

if (!isset($_SESSION["score"])) {
    $_SESSION["score"] = 0;
}


$score = $_SESSION["score"];
?>


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Over</title>
    <link rel="stylesheet" href="hl_styles.css">
</head>

<body>
<audio id="game-over-sound" src="sounds/game_over.mp3"></audio>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("game-over-sound").play();
    });
</script>

<div class="game_over">
    <div class="logo-container">
        <img src="LogoHigherLower.png" alt="Higher Lower Logo" class="logo">
    </div>
    <h1 class="game-over">Game Over!</h1>
    <h2 class="score">Dein Score: <strong><?php echo $score; ?></strong></h2>
    <br><br>
    <button class="playbutton" onclick="window.location.href='higher_lower_game.php';">Erneut Spielen</button>
    <button class="playbutton" onclick="window.location.href='validierung.php';">Score speichern</button>
</div>

<?php
session_destroy();
?>


</body>
</html>
