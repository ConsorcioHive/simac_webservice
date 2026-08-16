<?php
// app/Models/Empresa.php
// Clonado de SIMAC (app/Models/Empresa.php) adaptado al sistema local single-tenant:
// tabla `empresas` (MySQL) con las mismas columnas de public.companies.
namespace App\Models;

use App\Core\Database;

class Empresa
{
    public function obtenerTodas()
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM empresas ORDER BY nombre";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }

    // Single-tenant: la única empresa de la instalación.
    public function obtenerUnica()
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM empresas ORDER BY id LIMIT 1";
        $stmt = $pdo->query($sql);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function crear(array $data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO empresas
            (company_code, tipo, nombre, slogan, rif, direccion_fiscal, telefono, email,
             latitud, longitud, usuario_contacto_id,
             codigo_pais, codigo_estado, codigo_municipio, codigo_parroquia, codigo_postal,
             moneda_codigo, moneda_nombre, moneda_simbolo, empresa_description, empresa_documento_url, empresa_caratula_url,
             logo_url, business_card_url, notif_email, notif_sms, acepta_terminos, acepta_privacidad,
             redes_id, redes, redes_url, moneda_pago_id,
             serial_licencia, identificador_saint, fecha_expiracion, entity_folders)
            VALUES
            (:company_code, :tipo, :nombre, :slogan, :rif, :direccion_fiscal, :telefono, :email,
             :latitud, :longitud, :usuario_contacto_id,
             :codigo_pais, :codigo_estado, :codigo_municipio, :codigo_parroquia, :codigo_postal,
             :moneda_codigo, :moneda_nombre, :moneda_simbolo, :empresa_description, :empresa_documento_url, :empresa_caratula_url,
             :logo_url, :business_card_url, :notif_email, :notif_sms, :acepta_terminos, :acepta_privacidad,
             :redes_id, :redes, :redes_url, :moneda_pago_id,
             :serial_licencia, :identificador_saint, :fecha_expiracion, :entity_folders)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'company_code'           => $data['company_code']           ?? null,
            'tipo'                   => (int)($data['tipo'] ?? 0),
            'nombre'                 => $data['nombre'],
            'slogan'                 => $data['slogan']                 ?? null,
            'rif'                    => $data['rif']                    ?? null,
            'direccion_fiscal'       => $data['direccion_fiscal']       ?? null,
            'telefono'               => $data['telefono']               ?? null,
            'email'                  => $data['email']                  ?? null,
            'latitud'                => $data['latitud']                ?? null,
            'longitud'               => $data['longitud']               ?? null,
            'usuario_contacto_id'    => !empty($data['usuario_contacto_id']) ? (int)$data['usuario_contacto_id'] : null,
            'codigo_pais'            => $data['codigo_pais']            ?? null,
            'codigo_estado'          => $data['codigo_estado']          ?? null,
            'codigo_municipio'       => $data['codigo_municipio']       ?? null,
            'codigo_parroquia'       => $data['codigo_parroquia']       ?? null,
            'codigo_postal'          => $data['codigo_postal']          ?? null,
            'moneda_codigo'          => $data['moneda_codigo']          ?? null,
            'moneda_nombre'          => $data['moneda_nombre']          ?? null,
            'moneda_simbolo'         => $data['moneda_simbolo']         ?? null,
            'empresa_description'    => $data['empresa_description']    ?? null,
            'empresa_documento_url'  => $data['empresa_documento_url']  ?? null,
            'empresa_caratula_url'   => $data['empresa_caratula_url']   ?? null,
            'logo_url'               => $data['logo_url']               ?? null,
            'business_card_url'      => $data['business_card_url']      ?? null,
            'notif_email'            => !empty($data['notif_email']) ? 1 : 0,
            'notif_sms'              => !empty($data['notif_sms']) ? 1 : 0,
            'acepta_terminos'        => !empty($data['acepta_terminos']) ? 1 : 0,
            'acepta_privacidad'      => !empty($data['acepta_privacidad']) ? 1 : 0,
            'redes_id'               => $data['redes_id']               ?? null,
            'redes'                  => $data['redes']                  ?? null,
            'redes_url'              => $data['redes_url']              ?? null,
            'moneda_pago_id'         => $data['moneda_pago_id']         ?? null,
            'serial_licencia'        => $data['serial_licencia']        ?? null,
            'identificador_saint'    => $data['identificador_saint']    ?? null,
            'fecha_expiracion'       => $data['fecha_expiracion']       ?? null,
            'entity_folders'         => $data['entity_folders']         ?? '[]',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public function actualizar($id, array $data)
    {
        $pdo = Database::getConnection();
        $sql = "UPDATE empresas SET
            company_code         = :company_code,
            tipo                 = :tipo,
            nombre               = :nombre,
            slogan               = :slogan,
            rif                  = :rif,
            direccion_fiscal     = :direccion_fiscal,
            telefono             = :telefono,
            email                = :email,
            latitud              = :latitud,
            longitud             = :longitud,
            usuario_contacto_id  = :usuario_contacto_id,
            codigo_pais          = :codigo_pais,
            codigo_estado        = :codigo_estado,
            codigo_municipio     = :codigo_municipio,
            codigo_parroquia     = :codigo_parroquia,
            codigo_postal        = :codigo_postal,
            moneda_codigo        = :moneda_codigo,
            moneda_nombre        = :moneda_nombre,
            moneda_simbolo       = :moneda_simbolo,
            empresa_description  = :empresa_description,
            empresa_documento_url = :empresa_documento_url,
            empresa_caratula_url = :empresa_caratula_url,
            notif_email          = :notif_email,
            notif_sms            = :notif_sms,
            acepta_terminos      = :acepta_terminos,
            acepta_privacidad    = :acepta_privacidad,
            logo_url             = :logo_url,
            business_card_url    = :business_card_url,
            redes_id             = :redes_id,
            redes                = :redes,
            redes_url            = :redes_url,
            moneda_pago_id       = :moneda_pago_id,
            serial_licencia      = :serial_licencia,
            identificador_saint  = :identificador_saint,
            fecha_expiracion     = :fecha_expiracion,
            entity_folders       = :entity_folders,
            updated_at           = NOW()
            WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'company_code'        => $data['company_code']        ?? null,
            'tipo'                => (int)($data['tipo'] ?? 0),
            'nombre'              => $data['nombre'],
            'slogan'              => $data['slogan']              ?? null,
            'rif'                 => $data['rif']                 ?? null,
            'direccion_fiscal'    => $data['direccion_fiscal']    ?? null,
            'telefono'            => $data['telefono']            ?? null,
            'email'               => $data['email']               ?? null,
            'latitud'             => $data['latitud']             ?? null,
            'longitud'            => $data['longitud']            ?? null,
            'usuario_contacto_id' => !empty($data['usuario_contacto_id']) ? (int)$data['usuario_contacto_id'] : null,
            'codigo_pais'         => $data['codigo_pais']         ?? null,
            'codigo_estado'       => $data['codigo_estado']       ?? null,
            'codigo_municipio'    => $data['codigo_municipio']    ?? null,
            'codigo_parroquia'    => $data['codigo_parroquia']    ?? null,
            'codigo_postal'       => $data['codigo_postal']       ?? null,
            'moneda_codigo'       => $data['moneda_codigo']       ?? null,
            'moneda_nombre'       => $data['moneda_nombre']       ?? null,
            'moneda_simbolo'      => $data['moneda_simbolo']      ?? null,
            'empresa_description' => $data['empresa_description']  ?? null,
            'empresa_documento_url' => $data['empresa_documento_url'] ?? null,
            'empresa_caratula_url'  => $data['empresa_caratula_url']  ?? null,
            'notif_email'         => !empty($data['notif_email']) ? 1 : 0,
            'notif_sms'           => !empty($data['notif_sms']) ? 1 : 0,
            'acepta_terminos'     => !empty($data['acepta_terminos']) ? 1 : 0,
            'acepta_privacidad'   => !empty($data['acepta_privacidad']) ? 1 : 0,
            'logo_url'            => $data['logo_url']            ?? null,
            'business_card_url'   => $data['business_card_url']   ?? null,
            'redes_id'           => $data['redes_id']           ?? null,
            'redes'              => $data['redes']              ?? null,
            'redes_url'          => $data['redes_url']          ?? null,
            'moneda_pago_id'     => $data['moneda_pago_id']     ?? null,
            'serial_licencia'    => $data['serial_licencia']    ?? null,
            'identificador_saint' => $data['identificador_saint'] ?? null,
            'fecha_expiracion'   => $data['fecha_expiracion']   ?? null,
            'entity_folders'     => $data['entity_folders']     ?? '[]',
            'id'                  => $id,
        ]);
    }

    public function contarSedesPrincipales(): int
    {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM empresas WHERE tipo = 0";
        $stmt = $pdo->query($sql);
        return (int)$stmt->fetchColumn();
    }

    public function obtenerPorId($id)
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM empresas WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerSedePrincipal(): ?array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM empresas WHERE tipo = 0 LIMIT 1";
        $stmt = $pdo->query($sql);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function eliminar($id)
    {
        $pdo = Database::getConnection();
        $sql = "DELETE FROM empresas WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function existeCompanyCode(string $code, int $excludeId = 0): bool
    {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) AS total FROM empresas WHERE company_code = :code";
        if ($excludeId > 0) {
            $sql .= " AND id <> :id";
        }
        $stmt = $pdo->prepare($sql);
        $params = ['code' => $code];
        if ($excludeId > 0) {
            $params['id'] = $excludeId;
        }
        $stmt->execute($params);
        $row = $stmt->fetch();
        return ((int)($row['total'] ?? 0)) > 0;
    }
}
