<?php
// database/setup.php  — CLI: crea la BD y carga esquema + seed.
// Uso:  php database/setup.php
require __DIR__ . '/../config/config.php';

$cfg = require __DIR__ . '/../config/database.php';

$host = $cfg['host'];
$port = $cfg['port'];
$db   = $cfg['dbname'];
$user = $cfg['user'];
$pass = $cfg['pass'];

// 1) Conectar sin DB para crearla.
$dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
echo "BD '$db' lista." . PHP_EOL;

// 2) Conectar a la DB y cargar esquema + seed.
$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

foreach (['schema.sql', 'seed.sql'] as $file) {
    $sql = file_get_contents(__DIR__ . '/' . $file);
    // Dividir en statements por ";"
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if ($stmt === '') continue;
        $pdo->exec($stmt);
    }
    echo "Cargado: $file" . PHP_EOL;
}

echo "Setup completado." . PHP_EOL;
