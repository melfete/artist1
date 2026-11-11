<?php
require_once 'security.php';
start_secure_session();
include 'db_connect.php';

// Speichern nur nach beendetem Spiel, nur einmal, und nur mit dem Score aus der Session
$canSave = !empty($_SESSION["game_over"])
        && empty($_SESSION["score_saved"])
        && isset($_SESSION["score"])
        && (int)$_SESSION["score"] > 0;

$score = $canSave ? (int)$_SESSION["score"] : 0;
$error = "";
$player_name = "";

if ($canSave && $_SERVER["REQUEST_METHOD"] === "POST") {
    $player_name = trim($_POST["player_name"] ?? "");
    $nameLength = mb_strlen($player_name);

    if (!csrf_valid($_POST["csrf_token"] ?? null)) {
        $error = "Sitzung abgelaufen – bitte erneut absenden.";
    } elseif ($nameLength < 1 || $nameLength > 30) {
        $error = "Bitte einen Namen mit 1–30 Zeichen eingeben!";
    } else {
        $stmt = $conn->prepare("INSERT INTO leaderboard (name, score) VALUES (?, ?)");
        $stmt->bind_param("si", $player_name, $score);

        if ($stmt->execute()) {
            $_SESSION["score_saved"] = true;
            $stmt->close();
            $conn->close();
            header("Location: leaderboard.php");
            exit;
        }

        error_log("Score speichern fehlgeschlagen: " . $stmt->error);
        $error = "Der Score konnte nicht gespeichert werden.";
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validierung</title>
    <link rel="stylesheet" href="validierung.css">
</head>
<body>
    <div class="game_over">
        <div class="logo-container">
            <img src="LogoHigherLower.png" alt="Higher Lower Logo" class="logo">
        </div>
        <h1 class="game-over">Validierung</h1>

        <?php if ($canSave): ?>
            <h2 class="score">Dein Score: <strong><?= $score ?></strong></h2>
            <h2 class="score">Gib deinen Namen ein:</h2>

            <form method="POST" name="scoreForm">
                <?= csrf_field() ?>
                <input type="text" name="player_name" placeholder="Dein Name"
                       maxlength="30" required value="<?= e($player_name) ?>">
                <br><br>
                <button class="sendebutton" type="submit">Senden</button>
                <button class="zurückbutton" type="button" onclick="window.location.href='higher_lower_game.php';">Zurück</button>
            </form>

            <?php if ($error !== ""): ?>
                <div style="color: red;"><br><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        <?php else: ?>
            <h2 class="score">Es gibt keinen Score zum Speichern.</h2>
            <button class="zurückbutton" type="button" onclick="window.location.href='higher_lower_game.php?action=reset&token=<?= e(csrf_token()) ?>';">Neues Spiel</button>
            <button class="zurückbutton" type="button" onclick="window.location.href='leaderboard.php';">Leaderboard</button>
        <?php endif; ?>
    </div>
</body>
</html>
