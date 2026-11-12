<?php
require_once 'security.php';
start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    header('Location: higher_lower_game.php');
    exit();
}

// Kein Weiterspielen nach Game Over und kein Raten ohne laufende Runde
if (!empty($_SESSION['game_over']) || !isset($_SESSION['current_artist'], $_SESSION['next_artist'])) {
    header('Location: higher_lower_game.php');
    exit();
}

$guess = $_POST['guess'] ?? '';
if ($guess !== 'higher' && $guess !== 'lower') {
    header('Location: higher_lower_game.php');
    exit();
}

// Fan-Zahlen (Deezer) ausschließlich aus der Session – nie vom Client übernehmen
$fans1 = (int)($_SESSION['current_artist']['fans'] ?? 0);
$fans2 = (int)($_SESSION['next_artist']['fans'] ?? 0);

$correct = ($guess === 'higher' && $fans2 >= $fans1)
        || ($guess === 'lower'  && $fans2 <= $fans1);

if (!isset($_SESSION['score'])) {
    $_SESSION['score'] = 0;
}

if ($correct) {
    $_SESSION['score']++;
    $_SESSION['current_artist'] = $_SESSION['next_artist'];
    unset($_SESSION['next_artist']);
} else {
    $_SESSION['game_over'] = true;
    unset($_SESSION['score_saved']);
}

header('Location: higher_lower_game.php');
exit();
