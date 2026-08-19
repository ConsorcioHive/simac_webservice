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

    // Pagina el historial de mayor a menor (más reciente primero) y permite
    // filtrar por palabras en cualquiera de los campos visibles.
    public function paginar($pagina = 1, $porPagina = 15, $q = '')
    {
        $q = trim((string)$q);
        $where = '';
        $params = [];
        if ($q !== '') {
            $where = "WHERE tipo LIKE :q
                         OR direccion LIKE :q
                         OR estado LIKE :q
                         OR mensaje LIKE :q
                         OR CAST(creado_en AS CHAR) LIKE :q
                         OR CAST(registros AS CHAR) LIKE :q";
            $qEsc = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
            $params['q'] = '%' . $qEsc . '%';
        }

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM sync_log $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = max(0, ($pagina - 1) * $porPagina);
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sync_log $where ORDER BY creado_en DESC, id DESC LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('lim', (int)$porPagina, \PDO::PARAM_INT);
        $stmt->bindValue('off', (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [$stmt->fetchAll(), $total];
    }
}
