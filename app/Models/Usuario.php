<?php
// app/Models/Usuario.php
namespace App\Models;

use App\Core\Database;

class Usuario
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function buscarPorLoginOEmail($identificador)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM usuarios WHERE login = :id OR email = :id LIMIT 1"
        );
        $stmt->execute(['id' => $identificador]);
        return $stmt->fetch();
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function listarPorEmpresa($empresaId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, cedula, nombre, apellido, email, login, rol, estado, fecha_registro
             FROM usuarios WHERE empresa_id = :e ORDER BY nombre"
        );
        $stmt->execute(['e' => $empresaId]);
        return $stmt->fetchAll();
    }

    public function crear($datos)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO usuarios
                (empresa_id, cedula, nombre, apellido, email, login, password_hash, rol, estado, cargo, telefono)
             VALUES
                (:empresa_id, :cedula, :nombre, :apellido, :email, :login, :password_hash, :rol, :estado, :cargo, :telefono)"
        );
        $stmt->execute([
            'empresa_id'    => $datos['empresa_id'],
            'cedula'        => $datos['cedula'] ?? null,
            'nombre'        => $datos['nombre'],
            'apellido'      => $datos['apellido'] ?? null,
            'email'         => $datos['email'],
            'login'         => $datos['login'],
            'password_hash' => password_hash($datos['password'], PASSWORD_BCRYPT),
            'rol'           => $datos['rol'] ?? 'operador',
            'estado'        => $datos['estado'] ?? 'activo',
            'cargo'         => $datos['cargo'] ?? null,
            'telefono'      => $datos['telefono'] ?? null,
        ]);
        return $this->pdo->lastInsertId();
    }

    public function actualizar($id, $datos)
    {
        $campos = "cedula = :cedula, nombre = :nombre, apellido = :apellido, email = :email,
                   login = :login, rol = :rol, estado = :estado, cargo = :cargo, telefono = :telefono";
        $params = [
            'cedula'   => $datos['cedula'] ?? null,
            'nombre'   => $datos['nombre'],
            'apellido' => $datos['apellido'] ?? null,
            'email'    => $datos['email'],
            'login'    => $datos['login'],
            'rol'      => $datos['rol'] ?? 'operador',
            'estado'   => $datos['estado'] ?? 'activo',
            'cargo'    => $datos['cargo'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'id'       => $id,
        ];
        if (!empty($datos['password'])) {
            $campos .= ", password_hash = :password_hash";
            $params['password_hash'] = password_hash($datos['password'], PASSWORD_BCRYPT);
        }
        $stmt = $this->pdo->prepare("UPDATE usuarios SET $campos WHERE id = :id");
        return $stmt->execute($params);
    }

    public function eliminar($id, $empresaId)
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM usuarios WHERE id = :id AND empresa_id = :e"
        );
        return $stmt->execute(['id' => $id, 'e' => $empresaId]);
    }
}
