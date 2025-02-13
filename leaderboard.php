<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "higher_lower";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$sql = "SELECT name, score FROM leaderboard ORDER BY score DESC ";
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
            <button class="playbutton" onclick="window.location.href='higher_lower_game.php';">Nochmal spielen</button>
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
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>{$rank}</td><td>" . htmlspecialchars($row["name"]) . "</td><td>{$row["score"]}</td></tr>";
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
