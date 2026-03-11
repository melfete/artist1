<?php
include 'db_connect.php';

$query = "SELECT * FROM artisthower";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="KünstlerSeite.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KünstlerSeite</title>
</head>
<body>
    <form action="index.php">
        <button>Homepage</button>
    </form>

    <div class="image-container">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="image-item">
                <a href="artist<?= $row['id']; ?>.php">
                    <img src="image/<?= strtolower(str_replace(' ', '', $row['name'])); ?>.jpg" alt="<?= htmlspecialchars($row['name']); ?>">
                    <div class="hover-text"><?= htmlspecialchars($row['name']); ?></div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
