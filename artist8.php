<?php
include 'db_connect.php';

$sql = "SELECT * FROM artist8";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='artist-container'>";
        
        echo "<div class='artist-text'>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($row['name']) . "</p>";
        echo "<p><strong>Age:</strong> " . htmlspecialchars($row['age']) . "</p>";
        echo "<p><strong>Streams:</strong> " . (isset($row['streams']) ? htmlspecialchars($row['streams']) : "N/A") . "</p>";
        echo "<p><strong>Most Streamed:</strong> " . htmlspecialchars($row['most_streamed']) . "</p>";
        echo "<p><strong>Newes Song:</strong> " . htmlspecialchars($row['newessong']) . "</p>";
        echo "<p><strong>Albums:</strong> ";
        echo "<p>{$row['album1']}</p>";
        echo "<p>{$row['album2']}</p>";
        echo "<p>{$row['album3']}</p>";
        echo "<p>{$row['album4']}</p>";
        echo "<p>{$row['album5']}</p>";
        echo "<p>{$row['album6']}</p>";
        echo "<p>{$row['album7']}</p>";
        echo "<p>{$row['album8']}</p>";
        echo "<p>{$row['album9']}</p>";
        echo "<p>{$row['album10']}</p>";
        echo "</div>";
        
        if (!empty($row['picture'])) {
            echo "<div class='artist-image'>";
            echo "<img src='data:image/jpeg;base64," . base64_encode($row['picture']) . "' width='200' />";
            echo "</div>";
        }
        
        echo "</div>";
    }
} else {
    echo "No Artist found";
}

$conn->close();
?>

<head>
    <link rel="stylesheet" href="artist.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<form action="KünstlerSeite.php">
    <button>Back</button>
</form>
</body>