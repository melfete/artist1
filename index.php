<?php
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="Homepage.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body>

<h1>Welcome to the Artist Website</h1>

<div class="button-container">
    <a href="higher_lower_startpage.php"><button class="game-button">Higher Lower Game</button></a>
    <a href="KünstlerSeite.php"><button class="artist-button">Artists</button></a>
    <a href="music_player.php"><button class="music-button">Music Player</button></a>
</div>


<div class="carousel">
    <div class="image-container">
        <img src="image/reezy.jpg" alt="reezy">
        <img src="image/kendricklamar.jpg" alt="kendricklamar">
        <img src="image/michaeljackson.jpg" alt="michaeljackson">
        <img src="image/rin.jpg" alt="rin">
        <img src="image/jazeek.jpg" alt="jazeek">
        <img src="image/nimo.jpg" alt="nimo">
        <img src="image/luciano.jpg" alt="luciano">
        <img src="image/xatar.jpg" alt="xatar">
        <img src="image/jamal.jpg" alt="jamal">

        <img src="image/reezy.jpg" alt="reezy">
        <img src="image/kendricklamar.jpg" alt="kendricklamar">
        <img src="image/michaeljackson.jpg" alt="michaeljackson">
        <img src="image/rin.jpg" alt="rin">
        <img src="image/jazeek.jpg" alt="jazeek">
        <img src="image/nimo.jpg" alt="nimo">
        <img src="image/luciano.jpg" alt="luciano">
        <img src="image/xatar.jpg" alt="xatar">
        <img src="image/jamal.jpg" alt="jamal">

        <img src="image/reezy.jpg" alt="reezy">
        <img src="image/kendricklamar.jpg" alt="kendricklamar">
        <img src="image/michaeljackson.jpg" alt="michaeljackson">
        <img src="image/rin.jpg" alt="rin">
        <img src="image/jazeek.jpg" alt="jazeek">
        <img src="image/nimo.jpg" alt="nimo">
        <img src="image/luciano.jpg" alt="luciano">
        <img src="image/xatar.jpg" alt="xatar">
        <img src="image/jamal.jpg" alt="jamal">
    
        <img src="image/reezy.jpg" alt="reezy">
        <img src="image/kendricklamar.jpg" alt="kendricklamar">
        <img src="image/michaeljackson.jpg" alt="michaeljackson">
        <img src="image/rin.jpg" alt="rin">
        <img src="image/jazeek.jpg" alt="jazeek">
        <img src="image/nimo.jpg" alt="nimo">
        <img src="image/luciano.jpg" alt="luciano">
        <img src="image/xatar.jpg" alt="xatar">
        <img src="image/jamal.jpg" alt="jamal">
        <img src="image/reezy.jpg" alt="reezy">
        <img src="image/kendricklamar.jpg" alt="kendricklamar">
        <img src="image/michaeljackson.jpg" alt="michaeljackson">
        <img src="image/rin.jpg" alt="rin">
        <img src="image/jazeek.jpg" alt="jazeek">
        <img src="image/nimo.jpg" alt="nimo">
        <img src="image/luciano.jpg" alt="luciano">
        <img src="image/xatar.jpg" alt="xatar">
        <img src="image/jamal.jpg" alt="jamal">
        
    </div>
</div>

<div id="news">
    <div>
        <h2>Top Songs</h2>
        <?php
        $sql = "SELECT place, name, artist FROM topsongs ORDER BY place ASC";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<p>{$row['place']}. {$row['name']} - {$row['artist']}</p>";
            }
        } else {
            echo "<p>Keine Songs gefunden</p>";
        }
        ?>
    </div>

    <div>
        <h2>Top Albums</h2>
        <?php
        $sql = "SELECT place, name, artist FROM topalbums ORDER BY place ASC";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<p>{$row['place']}. {$row['name']} - {$row['artist']}</p>";
            }
        } else {
            echo "<p>Keine Alben gefunden</p>";
        }
        ?>
    </div>

    <div>
        <h2>Top Artists</h2>
        <?php
        $sql = "SELECT place, name, streams FROM topartist ORDER BY place ASC";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<p>{$row['place']}. {$row['name']} - {$row['streams']} Mio. Streams</p>";
            }
        } else {
            echo "<p>Keine Künstler gefunden</p>";
        }
        ?>
    </div>
</div>

<div id="faq">
    <h2>Frequently Asked Questions</h2>
    <div class="faq-item">
        <button class="faq-question">Where can I find more information about an artist?</button>
        <div class="faq-answer">
            <p>You need to click on the "Artist" button, then select a picture of the artist you are interested in. This will show you some details and information about them.</p>
        </div>
    </div>
    
    <div class="faq-item">
        <button class="faq-question">Why do artists mostly release music at 11:59 PM on Thursday?</button>
        <div class="faq-answer">
            <p>Artists often release music at 11:59 PM on Thursdays because of the timing related to the global music charts and streaming services. The release time allows their new songs to be counted toward that week's chart data, which typically begins fresh at midnight on Friday. By releasing just before midnight on Thursday, it maximizes the amount of time their music can accumulate streams and play counts for the week, boosting chart performance.</p>
        </div>
    </div>
    
    <div class="faq-item">
        <button class="faq-question">Where can I order a box of the album?</button>
        <div class="faq-answer">
            <p>You can order a box of the album directly from the artist's official website or various online music retailers. Many artists offer special editions or box sets of their albums for purchase, including exclusive merchandise, limited edition items, and more. Be sure to check out platforms like Amazon, eBay, or specialized music shops for these exclusive offers.</p>
        </div>
    </div>
    
    <div class="faq-item">
        <button class="faq-question">Who worked on this project?</button>
        <div class="faq-answer">
            <p> Maxim Androssov, Timofey Kefalianakis, Hadi Abouatie, Christopher Mätze, Yusuf Görgülü, Saltuk Kalender</p>
        </div>
    </div>
</div>


    <script>
  document.querySelectorAll('.faq-question').forEach(item => {
    item.addEventListener('click', function() {
        let answer = this.nextElementSibling;
        
        if (answer.style.display === "block") {
            answer.style.display = "none";
        } else {
            answer.style.display = "block";
        }
    });
});

</script>

</body>
</html>