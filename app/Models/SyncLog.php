<?php
// app/Models/SyncLog.php
namespace App\Models;

use App\Core\Database;

class SyncLog
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function registrar($tipo, $direccion, $estado, $registros = 0, $mensaje = null)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sync_log (tipo, direccion, estado, registros, mensaje)
             VALUES (:tipo, :direccion, :estado, :registros, :mensaje)"
        );
        $stmt->execute([
            'tipo'      => $tipo,
            'direccion' => $direccion,
            'estado'    => $estado,
            'registros' => $registros,
            'mensaje'   => $mensaje,
        ]);
        return $this->pdo->lastInsertId();
    }

    public function ultimos($limite = 50)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sync_log ORDER BY creado_en DESC LIMIT :limite"
        );
        $stmt->bindValue('limite', (int)$limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
