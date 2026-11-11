<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

// Streams ausschließlich aus der Session – nie vom Client übernehmen
$streams1 = (int)$_SESSION['current_artist']['künstler_streams'];
$streams2 = (int)$_SESSION['next_artist']['künstler_streams'];

$correct = ($guess === 'higher' && $streams2 >= $streams1)
        || ($guess === 'lower'  && $streams2 <= $streams1);

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
