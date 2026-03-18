<?php
session_start(); 

include 'db_connect.php'; 

if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    $_SESSION["score"] = 0;
    unset($_SESSION["current_artist"]);
    unset($_SESSION["next_artist"]);
    unset($_SESSION["game_over"]);
    header("Location: higher_lower_game.php");
    exit();
}

if (!isset($_SESSION["score"])) {
    $_SESSION["score"] = 0;
}

function findExistingImage($name) {
    $search  = array('Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß', 'é', 'è', 'ê', 'á', 'à', 'â', 'í', 'ì', 'î', 'ó', 'ò', 'ô', 'ú', 'ù', 'û', 'ñ');
    $replace = array('ae', 'oe', 'ue', 'ae', 'oe', 'ue', 'ss', 'e', 'e', 'e', 'a', 'a', 'a', 'i', 'i', 'i', 'o', 'o', 'o', 'u', 'u', 'u', 'n');
    $cleanName = str_replace($search, $replace, $name);

    $formatted = str_replace(' ', '', $cleanName);
    $formatted = str_replace('.', '', $formatted);
    $formatted = str_replace('-', '', $formatted);
    $formatted = strtolower($formatted);
    
    $extensions = ['png', 'jpg', 'jpeg', 'webp', 'JPG', 'PNG'];
    
    foreach ($extensions as $ext) {
        $path = "image/" . $formatted . "." . $ext;
        if (file_exists($path)) {
            return $path;
        }
    }

    $formattedUnderscore = str_replace(' ', '_', strtolower($cleanName));
    foreach ($extensions as $ext) {
        $path = "image/" . $formattedUnderscore . "." . $ext;
        if (file_exists($path)) {
            return $path;
        }
    }

    return "image/placeholder.jpg";
}

if (!isset($_SESSION["current_artist"]) || !isset($_SESSION["next_artist"])) {
    $sql = "SELECT * FROM `künstler` ORDER BY RAND() LIMIT 2";
    $result = $conn->query($sql);

    if ($result && $result->num_rows >= 2) {
        $artists = $result->fetch_all(MYSQLI_ASSOC);
        $_SESSION["current_artist"] = $artists[0]; 
        $_SESSION["next_artist"] = $artists[1]; 
    } else {
        die("Fehler: Datenbank unvollständig.");
    }
}

if ($_SESSION["current_artist"]["künstler_name"] === $_SESSION["next_artist"]["künstler_name"]) {
    $currentName = $conn->real_escape_string($_SESSION["current_artist"]["künstler_name"]);
    $sql = "SELECT * FROM `künstler` WHERE künstler_name != '$currentName' ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $_SESSION["next_artist"] = $result->fetch_assoc();
    }
}

$artist1 = $_SESSION["current_artist"];
$artist2 = $_SESSION["next_artist"];

$image1 = findExistingImage($artist1["künstler_name"]);
$image2 = findExistingImage($artist2["künstler_name"]);

$streams1 = isset($artist1["künstler_streams"]) ? (int)$artist1["künstler_streams"] : 0;
$streams2 = isset($artist2["künstler_streams"]) ? (int)$artist2["künstler_streams"] : 0;

$isGameOver = isset($_SESSION["game_over"]) && $_SESSION["game_over"] === true;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Higher Lower Game</title>
    <style>
        :root {
            --spotify-green: #1db954;
            --spotify-black: #121212;
            --spotify-grey: #181818;
            --spotify-light-grey: #282828;
            --error-red: #e91e63;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--spotify-black);
            color: white;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .score-display {
            font-size: 1.5rem;
            margin-bottom: 20px;
            background: var(--spotify-green);
            padding: 10px 30px;
            border-radius: 50px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 10;
        }

        .game-container {
            display: flex;
            justify-content: center;
            align-items: stretch;
            gap: 20px;
            width: 95%;
            max-width: 1000px;
            flex-wrap: wrap;
        }

        .artist-box { 
            flex: 1;
            min-width: 300px;
            text-align: center; 
            padding: 30px;
            background: var(--spotify-grey);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .image-wrapper {
            width: 100%;
            aspect-ratio: 1/1;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.4);
        }

        .image-wrapper img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }

        .artist-name { 
            font-size: 1.8rem; 
            font-weight: bold;
            margin-bottom: 5px;
        }

        .streams { 
            font-size: 2rem; 
            color: var(--spotify-green); 
            font-weight: 800; 
        }

        .vs-circle {
            background: white;
            color: black;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            align-self: center;
            box-shadow: 0 0 20px rgba(255,255,255,0.2);
            z-index: 5;
        }

        .controls {
            margin-top: 30px;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        form {
            display: flex;
            gap: 15px;
        }

        button, .btn-link { 
            padding: 15px 40px; 
            font-size: 1.2rem; 
            font-weight: bold;
            cursor: pointer; 
            border: none;
            border-radius: 50px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-higher { background-color: var(--spotify-green); color: white; }
        .btn-lower { background-color: var(--error-red); color: white; }
        
        button:hover, .btn-link:hover { transform: scale(1.05); filter: brightness(1.1); }

        .game-over-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .game-over-card {
            background: var(--spotify-grey);
            padding: 50px;
            border-radius: 25px;
            text-align: center;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 25px 60px rgba(0,0,0,1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }

        .game-over-card h2 { color: var(--error-red); font-size: 3.5rem; margin: 0; font-weight: 900; }
        .final-score { font-size: 6rem; font-weight: 900; margin: 10px 0; color: var(--spotify-green); line-height: 1; }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 35px;
        }

        .btn-restart { background: var(--spotify-green); color: white; }
        .btn-secondary { background: var(--spotify-light-grey); color: white; }

        @media (max-width: 768px) {
            .game-container { flex-direction: column; align-items: center; }
            .vs-circle { margin: -15px 0; }
        }
    </style>
</head>
<body>

<?php if ($isGameOver): ?>
    <div class="game-over-overlay">
        <div class="game-over-card">
            <h2>FALSCH!</h2>
            <p style="color: #b3b3b3; font-size: 1.2rem; margin-top: 15px;">Dein Endergebnis:</p>
            <div class="final-score"><?= (int)$_SESSION["score"] ?></div>
            <p style="text-transform: uppercase; letter-spacing: 2px; color: #b3b3b3;">Punkte erreicht</p>
            
            <div class="action-buttons">
                <a href="higher_lower_game.php?action=reset" class="btn-link btn-restart">NOCHMAL SPIELEN</a>
                <a href="index.php" class="btn-link btn-secondary">ZUM HAUPTMENÜ</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="score-display">Punkte: <?= (int)$_SESSION["score"] ?></div>

<div class="game-container">
    <div class="artist-box">
        <div>
            <p style="color: #b3b3b3; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">Künstler 1</p>
            <div class="image-wrapper">
                <img src="<?= $image1 ?>" alt="<?= htmlspecialchars($artist1["künstler_name"]) ?>">
            </div>
            <div class="artist-name"><?= htmlspecialchars($artist1["künstler_name"]) ?></div>
        </div>
        <div class="streams"><?= number_format($streams1, 0, ',', '.') ?> <span style="font-size: 1rem; color: #b3b3b3;">Streams</span></div>
    </div>

    <div class="vs-circle">VS</div>

    <div class="artist-box">
        <div>
            <p style="color: #b3b3b3; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">Künstler 2</p>
            <div class="image-wrapper">
                <img src="<?= $image2 ?>" alt="<?= htmlspecialchars($artist2["künstler_name"]) ?>">
            </div>
            <div class="artist-name"><?= htmlspecialchars($artist2["künstler_name"]) ?></div>
        </div>
        <div class="streams" style="color: #444;">? ? ?</div>
        <p style="margin: 0; color: #b3b3b3;">Mehr oder weniger Streams?</p>
    </div>
</div>

<div class="controls">
    <form method="post" action="check_answer.php">
        <input type="hidden" name="artist1_streams" value="<?= $streams1 ?>">
        <input type="hidden" name="artist2_streams" value="<?= $streams2 ?>">
        <button type="submit" name="guess" value="higher" class="btn-higher">HÖHER ▲</button>
        <button type="submit" name="guess" value="lower" class="btn-lower">TIEFER ▼</button>
    </form>
</div>

<div style="margin-top: 40px;">
    <a href="index.php" style="color: #555; text-decoration: none; font-size: 0.9rem; transition: color 0.2s;" onmouseover="this.style.color='#888'" onmouseout="this.style.color='#555'">Spiel abbrechen</a>
</div>

</body>
</html>