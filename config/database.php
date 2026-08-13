<?php
// config/database.php
// Conexión local: MariaDB/MySQL nativo de XAMPP (sin instalar nada extra).
return [
    'driver'           => 'mysql',
    'host'             => '127.0.0.1',
    'port'             => 3306,
    'dbname'           => 'simacweb_local',
    'user'             => 'root',
    'pass'             => '',
    'charset'          => 'utf8mb4',
    'sslmode'          => 'disable',
    'persistent'       => false,
    'emulate_prepares' => true,
];
