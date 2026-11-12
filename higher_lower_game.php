<?php
require_once 'security.php';
require_once 'artist_api.php';
start_secure_session();

include 'db_connect.php';

/**
 * Zieht einen zufälligen Künstler (außer $excludeIds) und lädt seine Fan-Zahl, falls veraltet.
 * Künstler ohne Fan-Zahl (Deezer bisher nie erreichbar) werden übersprungen.
 * Rückgabe enthält nur, was das Spiel braucht – landet so in der Session.
 */
function draw_game_artist(mysqli $conn, array $excludeIds): ?array
{
    for ($try = 0; $try < 5; $try++) {
        $ids = array_values(array_map('intval', $excludeIds)) ?: [0];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $conn->prepare(
            'SELECT ' . ARTIST_COLUMNS . " FROM artists WHERE id NOT IN ($placeholders) ORDER BY RAND() LIMIT 1"
        );
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }
        $row = artist_fresh($conn, $row);
        if ($row['fans'] !== null) {
            return [
                'id'         => (int)$row['id'],
                'name'       => $row['name'],
                'fans'       => (int)$row['fans'],
                'image_url'  => $row['image_url'],
                'image_path' => artist_local_image($row),
            ];
        }
        $excludeIds[] = (int)$row['id'];
    }
    return null;
}

// Laufendes Spiel aus der Zeit vor der Umstellung auf Fans (alte Session-Struktur): neu starten
if (isset($_SESSION['current_artist']) && !isset($_SESSION['current_artist']['fans'])) {
    unset($_SESSION['current_artist'], $_SESSION['next_artist'], $_SESSION['game_over'], $_SESSION['score_saved']);
    $_SESSION['score'] = 0;
}

if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    if (!csrf_valid($_GET['token'] ?? null)) {
        // Fremd ausgelöster oder veralteter Reset-Link: ignorieren
        header("Location: higher_lower_game.php");
        exit();
    }
    $_SESSION["score"] = 0;
    unset($_SESSION["current_artist"]);
    unset($_SESSION["next_artist"]);
    unset($_SESSION["game_over"]);
    unset($_SESSION["score_saved"]);
    header("Location: higher_lower_game.php");
    exit();
}

if (!isset($_SESSION["score"])) {
    $_SESSION["score"] = 0;
}


if (!isset($_SESSION["current_artist"])) {
    // Neues Spiel: ersten Künstler ziehen (der Gegner folgt unten)
    $first = draw_game_artist($conn, []);
    if (!$first) {
        die("Fehler: Datenbank unvollständig.");
    }
    $_SESSION["current_artist"] = $first;
    unset($_SESSION["next_artist"]);
}

if (!isset($_SESSION["next_artist"])) {
    // Nach richtiger Antwort: aktuellen Künstler behalten, nur einen neuen Gegner ziehen
    $next = draw_game_artist($conn, [$_SESSION["current_artist"]["id"]]);
    if (!$next) {
        die("Fehler: Kein neuer Künstler gefunden.");
    }
    $_SESSION["next_artist"] = $next;
}
$conn->close();

$artist1 = $_SESSION["current_artist"];
$artist2 = $_SESSION["next_artist"];

// Nur die Fans von Künstler 1 werden angezeigt; die von Künstler 2 bleiben in der Session.
$fans1 = (int)$artist1["fans"];

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
                <a href="higher_lower_game.php?action=reset&amp;token=<?= e(csrf_token()) ?>" class="btn-link btn-restart">NOCHMAL SPIELEN</a>
                <?php if ((int)$_SESSION["score"] > 0 && empty($_SESSION["score_saved"])): ?>
                    <a href="validierung.php" class="btn-link btn-secondary">SCORE SPEICHERN</a>
                <?php endif; ?>
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
                <?= artist_img_tag($artist1, '', 500) ?>
            </div>
            <div class="artist-name"><?= e($artist1["name"]) ?></div>
        </div>
        <div class="streams"><?= e(format_fans($fans1)) ?> <span style="font-size: 1rem; color: #b3b3b3;">Fans</span></div>
    </div>

    <div class="vs-circle">VS</div>

    <div class="artist-box">
        <div>
            <p style="color: #b3b3b3; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">Künstler 2</p>
            <div class="image-wrapper">
                <?= artist_img_tag($artist2, '', 500) ?>
            </div>
            <div class="artist-name"><?= e($artist2["name"]) ?></div>
        </div>
        <div class="streams" style="color: #444;">? ? ?</div>
        <p style="margin: 0; color: #b3b3b3;">Mehr oder weniger Fans?</p>
    </div>
</div>

<div class="controls">
    <form method="post" action="check_answer.php">
        <?= csrf_field() ?>
        <button type="submit" name="guess" value="higher" class="btn-higher">HÖHER ▲</button>
        <button type="submit" name="guess" value="lower" class="btn-lower">TIEFER ▼</button>
    </form>
</div>

<div style="margin-top: 40px;">
    <a href="index.php" style="color: #555; text-decoration: none; font-size: 0.9rem; transition: color 0.2s;" onmouseover="this.style.color='#888'" onmouseout="this.style.color='#555'">Spiel abbrechen</a>
</div>

</body>
</html>