<?php
// app/Models/Empresa.php
namespace App\Models;

use App\Core\Database;

class Empresa
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Single-tenant: solo existe una empresa por instalación.
    public function obtenerUnica()
    {
        $stmt = $this->pdo->query("SELECT * FROM empresas ORDER BY id LIMIT 1");
        return $stmt->fetch();
    }

    public function actualizar($id, $datos)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE empresas SET
                company_code     = :company_code,
                nombre           = :nombre,
                rif              = :rif,
                direccion_fiscal = :direccion_fiscal,
                telefono         = :telefono,
                email            = :email,
                logo_url         = :logo_url,
                empresa_descripcion = :empresa_descripcion
             WHERE id = :id"
        );
        return $stmt->execute([
            'company_code'        => $datos['company_code'],
            'nombre'              => $datos['nombre'],
            'rif'                 => $datos['rif'] ?? null,
            'direccion_fiscal'    => $datos['direccion_fiscal'] ?? null,
            'telefono'            => $datos['telefono'] ?? null,
            'email'               => $datos['email'] ?? null,
            'logo_url'            => $datos['logo_url'] ?? null,
            'empresa_descripcion' => $datos['empresa_descripcion'] ?? null,
            'id'                  => $id,
        ]);
    }
}
