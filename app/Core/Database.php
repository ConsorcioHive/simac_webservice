<?php
// app/Core/Database.php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {
            self::$instance = self::crearConexion();
        }
        return self::$instance;
    }

    private static function crearConexion(): PDO
    {
        $config = require BASE_PATH . '/config/database.php';

        $driver = $config['driver'] ?? 'pgsql';

        if ($driver === 'pgsql') {
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};sslmode={$config['sslmode']}";
        } else {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => $config['emulate_prepares'] ?? false,
        ];

        if (!empty($config['persistent'])) {
            $options[PDO::ATTR_PERSISTENT] = true;
        }

        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);

        if ($driver === 'pgsql') {
            $pdo->exec("SET CLIENT_ENCODING TO 'UTF8'");
        } elseif ($driver === 'mysql') {
            $pdo->exec("SET NAMES utf8mb4");
        }

        return $pdo;
    }

    public static function close()
    {
        self::$instance = null;
    }
}
