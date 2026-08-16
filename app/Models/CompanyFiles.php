<?php
// app/Models/CompanyFiles.php
namespace App\Models;

use App\Core\Database;

class CompanyFiles
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM company_files WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function listarPorCarpeta($companyId, $parentId = null)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM company_files
            WHERE company_id = :company_id AND parent_id " . ($parentId ? "= :parent_id" : "IS NULL") . "
            ORDER BY is_folder DESC, nombre ASC
        ");
        $params = ['company_id' => $companyId];
        if ($parentId) $params['parent_id'] = $parentId;
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function crearArchivo($datos)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO company_files
            (company_id, company_code, parent_id, is_folder, nombre, path_relativo, extension, mime_type, size_bytes, subido_por)
            VALUES
            (:company_id, :company_code, :parent_id, :is_folder, :nombre, :path_relativo, :extension, :mime_type, :size_bytes, :subido_por)
        ");
        $stmt->execute([
            'company_id'    => $datos['company_id'],
            'company_code'  => $datos['company_code'],
            'parent_id'     => $datos['parent_id'] ?? null,
            'is_folder'     => $datos['is_folder'] ? 1 : 0,
            'nombre'        => $datos['nombre'],
            'path_relativo' => $datos['path_relativo'],
            'extension'     => $datos['extension'] ?? null,
            'mime_type'     => $datos['mime_type'] ?? null,
            'size_bytes'    => $datos['size_bytes'] ?? null,
            'subido_por'    => $datos['subido_por'] ?? null,
        ]);
        return $this->pdo->lastInsertId();
    }

    public function actualizar($id, $datos)
    {
        $campos = [];
        foreach ($datos as $k => $v) {
            $campos[] = "$k = :$k";
        }
        if (empty($campos)) return false;
        $params = array_merge($datos, ['id' => $id]);
        $stmt = $this->pdo->prepare("UPDATE company_files SET " . implode(', ', $campos) . " WHERE id = :id");
        return $stmt->execute($params);
    }

    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM company_files WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function contarArchivosPorCarpeta($companyId, $parentId = null)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM company_files
            WHERE company_id = :company_id AND parent_id " . ($parentId ? "= :parent_id" : "IS NULL") . "
        ");
        $stmt->execute(['company_id' => $companyId]);
        if ($parentId) $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM company_files
            WHERE company_id = :company_id AND parent_id = :parent_id
        ");
        $stmt->execute(['company_id' => $companyId, 'parent_id' => $parentId]);
        return (int)$stmt->fetchColumn();
    }
}
