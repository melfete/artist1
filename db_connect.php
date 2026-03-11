<?php
if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['HTTP_HOST'] == 'localhost') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "projektm"; 
} else {
    $host = "sqlXXX.infinityfree.com"; 
    $user = "if0_XXXXXXXX";           
    $pass = "DEINpvaN0SHDgBN4ixg";          
    $dbname = "if0_XXXXXXXX_db_quiz"; 
}

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>