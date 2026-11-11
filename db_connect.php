<?php
// Umgebung serverseitig erkennen – NICHT über den Host-Header, den der Client fälschen kann.
// Lokal = XAMPP unter Windows (oder APP_ENV=local gesetzt), sonst Produktion (InfinityFree/Linux).
$isLocal = PHP_OS_FAMILY === 'Windows' || getenv('APP_ENV') === 'local';

if ($isLocal) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    error_log('config.php fehlt – siehe config.example.php');
    http_response_code(500);
    die('Konfigurationsfehler.');
}
$config = (require $configFile)[$isLocal ? 'local' : 'production'];

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($config['host'], $config['user'], $config['pass'], $config['dbname']);
unset($config);

if ($conn->connect_error) {
    error_log('DB-Verbindung fehlgeschlagen: ' . $conn->connect_error);
    http_response_code(500);
    die($isLocal ? 'Verbindung fehlgeschlagen: ' . htmlspecialchars($conn->connect_error) : 'Datenbank derzeit nicht erreichbar.');
}

$conn->set_charset('utf8mb4');
