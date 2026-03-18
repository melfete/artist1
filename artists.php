<?php
include 'db_connect.php';

function findExistingImage($name) {
    $search  = array('Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß', 'é', 'è', 'ê', 'á', 'à', 'â', 'í', 'ì', 'î', 'ó', 'ò', 'ô', 'ú', 'ù', 'û', 'ñ');
    $replace = array('ae', 'oe', 'ue', 'ae', 'oe', 'ue', 'ss', 'e', 'e', 'e', 'a', 'a', 'a', 'i', 'i', 'i', 'o', 'o', 'o', 'u', 'u', 'u', 'n');
    $cleanName = str_replace($search, $replace, $name);

    $formatted = str_replace(' ', '', $cleanName);
    $formatted = str_replace('.', '', $formatted);
    $formatted = strtolower($formatted);
    
    $extensions = ['png', 'jpg', 'jpeg', 'webp'];
    
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

    return null;
}

$sql = "SELECT künstler_name FROM `künstler` ORDER BY künstler_name ASC";
$result = $conn->query($sql);

$uniqueArtists = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $originalName = trim($row['künstler_name']);
        if (empty($originalName)) continue;

        $comparisonKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $originalName));
        
        $currentImagePath = findExistingImage($originalName);
        $foundMatchKey = null;

        if (isset($uniqueArtists[$comparisonKey])) {
            $foundMatchKey = $comparisonKey;
        } else {
            foreach ($uniqueArtists as $key => $data) {
                if (levenshtein($comparisonKey, $key) < 3) {
                    $foundMatchKey = $key;
                    break;
                }
            }
        }

        if ($foundMatchKey === null) {
            $uniqueArtists[$comparisonKey] = [
                'name' => $originalName,
                'image' => $currentImagePath ?? "image/placeholder.jpg"
            ];
        } else {
            if ($uniqueArtists[$foundMatchKey]['image'] === "image/placeholder.jpg" && $currentImagePath !== null) {
                $uniqueArtists[$foundMatchKey]['image'] = $currentImagePath;
                $uniqueArtists[$foundMatchKey]['name'] = $originalName;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alle Künstler</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #121212;
            color: white;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #1db954;
            margin-bottom: 40px;
            font-size: 2.5rem;
        }
        .artist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .artist-card {
            background: #181818;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .artist-card:hover {
            background: #282828;
            transform: translateY(-5px);
        }
        .image-container {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 50%; 
            overflow: hidden;
            margin-bottom: 15px;
            background-color: #333;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        }
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .artist-name {
            font-size: 1rem;
            font-weight: bold;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .back-btn {
            display: block;
            width: fit-content;
            margin: 50px auto;
            background: #1db954;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .back-btn:hover {
            transform: scale(1.05);
            background: #1ed760;
        }
    </style>
</head>
<body>

<h1>Künstler Mediathek</h1>

<div class="artist-grid">
    <?php
    if (!empty($uniqueArtists)) {
        foreach ($uniqueArtists as $artistData) {
            ?>
            <div class="artist-card">
                <div class="image-container">
                    <img src="<?= $artistData['image'] ?>" alt="<?= htmlspecialchars($artistData['name']) ?>">
                </div>
                <div class="artist-name"><?= htmlspecialchars($artistData['name']) ?></div>
            </div>
            <?php
        }
    } else {
        echo "<p style='text-align:center;'>Keine Künstler gefunden.</p>";
    }
    ?>
</div>

<a href="index.php" class="back-btn">Zurück zum Dashboard</a>

</body>
</html>