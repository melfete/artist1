<?php
// Vorlage: nach config.php kopieren und echte Zugangsdaten eintragen.
// config.php wird nicht ins Git-Repository übernommen.

return [
    'local' => [
        'host'   => 'localhost',
        'user'   => 'root',
        'pass'   => '',
        'dbname' => 'projektm',
    ],
    'production' => [
        'host'   => 'SQL_HOST',
        'user'   => 'SQL_USER',
        'pass'   => 'SQL_PASSWORD',
        'dbname' => 'SQL_DATABASE',
    ],
];
