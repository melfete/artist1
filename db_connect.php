<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$isLocal = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1');

if ($isLocal) {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "projektm"; 
} else {
    $host = "sql207.infinityfree.com"; 
    $user = "if0_41364848";           
    $pass = "pvaN0SHDgBN4ixg"; 
    $dbname = "if0_41364848_artists"; 
}

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>