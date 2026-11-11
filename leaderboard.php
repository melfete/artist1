<?php
session_start();
include 'db_connect.php';

$sql = "SELECT name, score FROM leaderboard ORDER BY score DESC LIMIT 50";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="leaderboard.css">
</head>
<body>

    <div class="leaderboard">
        <div class="top-section">
            <h1>Leaderboard</h1>
            <button class="playbutton" onclick="window.location.href='higher_lower_game.php?action=reset';">Nochmal spielen</button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Platz</th>
                        <th>Name</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rank = 1;
                    while ($result && $row = $result->fetch_assoc()) {
                        echo "<tr><td>{$rank}</td><td>" . htmlspecialchars($row["name"]) . "</td><td>" . (int)$row["score"] . "</td></tr>";
                        $rank++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>

<?php $conn->close(); ?>
