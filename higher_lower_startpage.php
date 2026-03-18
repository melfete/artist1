<?php
session_start(); 
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Higher Lower Game - Start</title>
    <style>
        :root {
            --spotify-green: #1db954;
            --spotify-black: #121212;
            --spotify-grey: #181818;
            --text-main: #ffffff;
            --text-dim: #b3b3b3;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--spotify-black);
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }

        .container {
            max-width: 600px;
            padding: 40px;
            background: var(--spotify-grey);
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.5);
            margin: 20px;
        }

        .logo-container {
            margin-bottom: 30px;
        }

        .logo {
            max-width: 180px;
            height: auto;
            filter: drop-shadow(0 0 10px rgba(29, 185, 84, 0.3));
        }

        h1 {
            font-size: 2.8rem;
            font-weight: 900;
            margin: 0;
            line-height: 1.1;
            letter-spacing: -1px;
        }

        .highlight {
            color: var(--spotify-green);
        }

        .highlight.lower {
            color: #e91e63;
        }

        .sub-headline {
            font-size: 1.5rem;
            color: var(--text-dim);
            margin: 10px 0 30px 0;
            font-weight: 300;
        }

        .description {
            color: var(--text-dim);
            line-height: 1.6;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }

        .playbutton {
            background-color: var(--spotify-green);
            color: white;
            padding: 18px 60px;
            font-size: 1.3rem;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: transform 0.2s, filter 0.2s;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            max-width: 300px;
        }

        .zurückbutton {
            background-color: transparent;
            color: var(--text-dim);
            padding: 12px 30px;
            font-size: 1rem;
            border: 1px solid #444;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .playbutton:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }

        .zurückbutton:hover {
            border-color: white;
            color: white;
        }

        @media (max-width: 480px) {
            h1 { font-size: 2rem; }
            .container { padding: 30px 20px; }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo-container">
            <img src="LogoHigherLower.png" alt="Higher Lower Logo" class="logo">  
        </div>

        <h1>THE <span class="highlight">HIGHER</span> <br> <span class="highlight lower">LOWER</span> GAME</h1>
        <div class="sub-headline">Spotify Edition</div>

        <div class="description">
            <p>Welcher Künstler hat mehr monatliche Hörer?</p>
            <p style="font-size: 0.9rem;">Basierend auf den aktuellen Global Spotify Streaming-Daten.</p>
        </div>

        <div class="button-group">
            <button class="playbutton" onclick="window.location.href='higher_lower_game.php';">SPIELEN</button>
            <button class="zurückbutton" onclick="window.location.href='index.php';">ZURÜCK</button>
        </div>
    </div>

</body>
</html>