<?php
session_start(); 
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Higher Lower Game</title>
    <link rel="stylesheet" href="hl_styles.css">
</head>
<body>

<div class="container">
            <div class="logo-container">
                <img src="LogoHigherLower.png" alt="Higher Lower Logo" class="logo">  
            </div>
        <h1>THE <span class="highlight">HIGHER</span> <span class="highlight lower">LOWER</span> GAME</h1>
        <h1>Spotify  Edition</h1>
        <br>
        <p>Was wird öfter gestreamt?</p>
        <p>Dies ist ein Typisches Higher Lower Game</p>
        <p>Die Daten basierend auf den "Top 50 Global" von Spotify</p>

        <button class="playbutton" onclick="window.location.href='higher_lower_game.php';">Play</button>
        <button class="zurückbutton"onclick="window.location.href='index.php';">Zurück </button>
    </div>
</body>
</html>
