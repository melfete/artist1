<?php
$servername = "localhost";
$username = "root";  // Standardbenutzer für XAMPP
$password = "";      // Standard ist kein Passwort
$dbname = "projektm"; // Name deiner Datenbank

// Verbindung herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Verbindung prüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}
?>
