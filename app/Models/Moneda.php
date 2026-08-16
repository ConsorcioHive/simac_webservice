<?php
// app/Models/Moneda.php
// Clonado de SIMAC (app/Models/Moneda.php) para la tabla local `monedas` (MySQL).
namespace App\Models;

use App\Core\Database;

class Moneda
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function obtenerPorCodigo(string $codigo): ?array
    {
        $sql = "SELECT *
                FROM monedas
                WHERE codigo = :codigo
                  AND activa = TRUE
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['codigo' => strtoupper($codigo)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listar(): array
    {
        $sql = "SELECT id, codigo, nombre, simbolo
                FROM monedas
                WHERE activa = TRUE
                ORDER BY es_moneda_base DESC, codigo ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerMonedaBase(): ?array
    {
        $sql = "SELECT id, codigo, nombre, simbolo, es_moneda_base
                FROM monedas
                WHERE activa = TRUE AND es_moneda_base = TRUE
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listarTodas(): array
    {
        $sql = "SELECT id, codigo, nombre, simbolo, es_moneda_base, factor_conversion
                FROM monedas
                WHERE activa = TRUE
                ORDER BY es_moneda_base DESC, codigo ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function actualizarBase(int $nuevaMonedaId): bool
    {
        $nueva  = $this->obtenerPorId($nuevaMonedaId);
        $actual = $this->obtenerMonedaBase();
        if (!$nueva || !$actual) return false;
        if ($nueva['id'] === $actual['id']) return true;

        $factorBaseNueva = (float)$nueva['factor_conversion'];

        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec("UPDATE monedas SET es_moneda_base = FALSE WHERE es_moneda_base = TRUE");
            $stmt = $this->pdo->prepare("UPDATE monedas SET es_moneda_base = TRUE, factor_conversion = 1.0 WHERE id = ?");
            $stmt->execute([$nuevaMonedaId]);

            $todas = $this->listarTodas();
            $updateStmt = $this->pdo->prepare("UPDATE monedas SET factor_conversion = ? WHERE id = ?");
            foreach ($todas as $m) {
                if ((int)$m['id'] === $nuevaMonedaId) continue;
                $nuevoFactor = (float)$m['factor_conversion'] / $factorBaseNueva;
                $updateStmt->execute([$nuevoFactor, $m['id']]);
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT * FROM monedas WHERE id = ? AND activa = TRUE LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}