<?php
session_start(); 


if (!isset($_SESSION["score"])) {
    $_SESSION["score"] = 0;
}

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "higher_lower"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Fehler bei der Verbindung: " . $conn->connect_error);
}

if (!isset($_SESSION["current_artist"]) || !isset($_SESSION["next_artist"])) {
    $sql = "SELECT * FROM `künstler` ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Fehler bei der SQL-Abfrage: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $_SESSION["current_artist"] = $result->fetch_assoc(); 
    } else {
        die("Fehler: Kein Künstler in der Datenbank gefunden.");
    }

    $first_artist_name = $_SESSION["current_artist"]["künstler_name"];
    $first_artist_song = $_SESSION["current_artist"]["song"];
    
    $sql = "SELECT * FROM `künstler` WHERE `künstler_name` != '$first_artist_name' AND `song` != '$first_artist_song' ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Fehler bei der SQL-Abfrage: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $_SESSION["next_artist"] = $result->fetch_assoc(); 
    } else {
        $_SESSION["next_artist"] = $_SESSION["current_artist"]; 
    }
}

$sql = "SELECT highscore FROM highscores WHERE id = 1";
$result = $conn->query($sql);
$current_highscore = 0;

if ($result) {
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_highscore = $row['highscore'];
    } else {
        $sql = "INSERT INTO highscores (highscore) VALUES (0)";
        if ($conn->query($sql) === TRUE) {
            $current_highscore = 0;
        } else {
            echo "Fehler beim Erstellen des Highscores: " . $conn->error;
        }
    }
} else {
    echo "Fehler bei der SQL-Abfrage: " . $conn->error;
}

if ($_SESSION["score"] > $current_highscore) {
    $sql = "UPDATE highscores SET highscore = ? WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION["score"]);
    if ($stmt->execute()) {
    } else {
        echo "Fehler beim Aktualisieren des Highscores: " . $conn->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $artist1_streams = $_POST["artist1_streams"];
    $artist2_streams = $_POST["artist2_streams"];
    $guess = $_POST["guess"];

    $correct = false;
    if ($guess == "higher" && $artist2_streams > $artist1_streams) {
        $correct = true;
    } elseif ($guess == "lower" && $artist2_streams < $artist1_streams) {
        $correct = true;
    }

    if ($correct) {
        $_SESSION["score"] += 1;
        $_SESSION["current_artist"] = $_SESSION["next_artist"]; 

        $current_artist_name = $_SESSION["current_artist"]["künstler_name"];
        $current_artist_song = $_SESSION["current_artist"]["song"];
        
        do {
            $sql = "SELECT * FROM `künstler` WHERE `künstler_name` != '$current_artist_name' AND `song` != '$current_artist_song' ORDER BY RAND() LIMIT 1";
            $result = $conn->query($sql);

            if ($result === false) {
                die("Fehler bei der SQL-Abfrage: " . $conn->error);
            }

        } while ($result->num_rows == 0);

        if ($result->num_rows > 0) {
            $_SESSION["next_artist"] = $result->fetch_assoc();
        } else {
            die("Fehler: Kein neuer Künstler gefunden.");
        }
        exit;
    } else {
        header("Location: wrong_answer.php");
        exit;
    }
}

$conn->close();

function getArtistImage($artistName) {
    $basePath = "artists/"; 
    $formattedName = str_replace(" ", "_", trim($artistName)); 
    $extensions = ["webp", "png", "jpg", "jpeg"]; 

    foreach ($extensions as $ext) {
        $filePath = $basePath . $formattedName . "." . $ext;
        if (file_exists($filePath)) {
            return $filePath; 
        }
    }
    return $basePath . "default.webp"; 
}


$artist1 = $_SESSION["current_artist"];
$artist2 = $_SESSION["next_artist"];


$artist1_img = getArtistImage($artist1["künstler_name"]);
$artist2_img = getArtistImage($artist2["künstler_name"]);



?>



<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Higher Lower</title>
    <link rel="stylesheet" href="hl_game_styles.css">
    <audio id="applause-sound" src="sounds/applause.mp3"></audio>
    
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let result = "<?= isset($_SESSION['result']) ? $_SESSION['result'] : '' ?>";

                if (result === "correct") {
                    document.getElementById("applause-sound").play();
                } else if (result === "wrong") {
                    document.getElementById("wrong-sound").play();
                }

                <?php unset($_SESSION["result"]); ?> 
            });
    </script>

</head>
<body>
    
        <div class="container">
        
        <button class="back-button"onclick="window.location.href='higher_lower_startpage.php';">Zurück </button>

        
            <div class="left-side">
                <div class="content_left">
                    <div class="artist_picture_left">
                        <img src="<?= htmlspecialchars($artist1_img) ?>" alt="<?= htmlspecialchars($artist1["künstler_name"]) ?>">
                    </div>
                                <br>
                                    <div class="artist">
                                
                                        <h3>Song 1</h3>
                                        <br>
                                        <p><strong>Name:</strong> <?= htmlspecialchars($artist1["künstler_name"]) ?></p>
                                        <br>
                                        <p><strong>Song:</strong> <?= htmlspecialchars($artist1["song"]) ?></p>
                                        <br>
                                        <p><strong>Streams:</strong> <?= number_format($artist1["künstler_streams"]) ?></p>
                               
                                    </div>
            
                            </div>
                        <div class="highscore">
                    <p><strong>Highscore:</strong> <?= $current_highscore ?></p>
                </div>
            </div>

            
            <div class="middle-line">
                <div class="vs-circle">VS</div>
            </div>

            
            <div class="right-side">
                <div class="content_right">
                <div class="artist_picture_right">
    <img src="<?= htmlspecialchars($artist2_img) ?>" alt="<?= htmlspecialchars($artist2["künstler_name"]) ?>">
</div>
                        <br>
                            <div class="artist">
                                    <h3>Song 2</h3>
                                    <br>
                                    <p><strong>Name:</strong> <?= htmlspecialchars($artist2["künstler_name"]) ?></p>
                                    <br>
                                    <p><strong>Song:</strong> <?= htmlspecialchars($artist2["song"]) ?></p>
                                    <br>
                            </div>
                        <br>
                        <form method="post" action="check_answer.php">
                            <input type="hidden" name="artist1_streams" value="<?= $artist1["künstler_streams"] ?>">
                            <input type="hidden" name="artist2_streams" value="<?= $artist2["künstler_streams"] ?>">
                            <button type="submit" name="guess" value="higher" class="higher_btn">Höher</button>
                            <button type="submit" name="guess" value="lower" class="lower_btn">Niedriger</button>
                        </form>
                   
                </div> 
                <div class="live-score">
                        <?php
                            echo "<p><strong>Score:</strong> " . $_SESSION["score"] . "</p>";
                        ?>
                </div>
                    
            </div>
        </div>
     
</body>
</html>
