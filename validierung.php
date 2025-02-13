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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $player_name = trim($_POST["player_name"]);
    $score = isset($_POST["score_count"]) ? intval($_POST["score_count"]) : 0; 

    if (!empty($player_name) && !empty($score)) {
        $stmt = $conn->prepare("INSERT INTO leaderboard (name, score) VALUES (?, ?)");
        $stmt->bind_param("si", $player_name, $score);
        
        if ($stmt->execute()) {
            header("Location: leaderboard.php"); 
            exit;
        } else {
            echo "Fehler beim Speichern des Scores: " . $conn->error;
        }

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
    <script>
        function validateForm(event) {
            var name = document.forms["scoreForm"]["player_name"].value;
            var score = document.forms["scoreForm"]["score_count"].value;
            var submitButton = document.getElementById("submitBtn");
            var errorMessage = document.getElementById("errorMessage");

            if (name == "" || score == "") {
                errorMessage.style.display = "block"; 
                submitButton.disabled = true; 
                event.preventDefault(); 
            } else {
                submitButton.disabled = false; 
                errorMessage.style.display = "none"; 
            }
        }
    </script>
</head>
<body>
    <div class="game_over">
        <div class="logo-container">
            <img src="logoHigherLower.png" alt="Higher Lower Logo" class="logo">
        </div>
        <h1 class="game-over">Validierung</h1>
        <h2 class="score">Gib deinen Namen und Score ein:</h2>
        
        <form method="POST" name="scoreForm" onsubmit="validateForm(event)">
            <input type="text" name="player_name" placeholder="Dein Name">
            <input type="number" name="score_count" min="0" placeholder="Dein Score">
            <br><br>
            
           
            <button class="sendebutton" type="submit" id="submitBtn" >Senden</button>
            
           
            <button class="zurückbutton" onclick="window.location.href='higher_lower_game.php';">Zurück</button>
        </form>

        
        <div id="errorMessage" style="color: red; display: none;">
            <br>
            Bitte Name und Score eingeben!
        </div>
    </div>  
</body>
</html>
