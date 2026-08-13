<?php
// app/Services/UploadService.php
namespace App\Services;

// Gestión de archivos usando el MISMO contenedor por empresa que SIMAC cloud:
//   public/uploads/empresas/{company_code}/...
// Así los archivos locales sincronizan con la nube sin reestructurar rutas.
class UploadService
{
    const BASE = BASE_PATH . '/public/uploads';

    // Normaliza el código de empresa para usarlo como nombre de carpeta.
    private static function normalizar($companyCode)
    {
        $code = preg_replace('/[^a-zA-Z0-9_-]/', '', $companyCode ?? '');
        return $code ?: 'sin_codigo';
    }

    public static function empresaBase($companyCode)
    {
        return self::BASE . '/empresas/' . self::normalizar($companyCode) . '/';
    }

    // Guarda un archivo subido ($_FILES[$fileKey]) dentro del contenedor de la empresa.
    public static function guardarEmpresa($companyCode, $fileKey, $subcarpeta = 'archivos', $nombre = null)
    {
        if (!isset($_FILES[$fileKey])) {
            return ['ok' => false, 'msg' => 'No se recibió el archivo.'];
        }
        $f = $_FILES[$fileKey];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'msg' => 'Error de subida: ' . $f['error']];
        }

        $code = self::normalizar($companyCode);
        $dir  = self::empresaBase($code) . trim($subcarpeta, '/') . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombre = $nombre ?: preg_replace('/[^a-zA-Z0-9._-]/', '_', $f['name']);
        $dest   = $dir . $nombre;

        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            return ['ok' => false, 'msg' => 'No se pudo mover el archivo.'];
        }

        $rel = 'public/uploads/empresas/' . $code . '/' . trim($subcarpeta, '/') . '/' . $nombre;
        return ['ok' => true, 'ruta_rel' => $rel, 'ruta_fs' => $dest];
    }

    // Guarda un archivo cualquiera en una subcarpeta del contenedor de la empresa.
    public static function guardarContenido($companyCode, $contenido, $subcarpeta, $nombre)
    {
        $code = self::normalizar($companyCode);
        $dir  = self::empresaBase($code) . trim($subcarpeta, '/') . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $dest = $dir . $nombre;
        file_put_contents($dest, $contenido);
        return 'public/uploads/empresas/' . $code . '/' . trim($subcarpeta, '/') . '/' . $nombre;
    }

    public static function url($rutaRelativa)
    {
        return BASE_URL . ltrim($rutaRelativa, '/');
    }
}
