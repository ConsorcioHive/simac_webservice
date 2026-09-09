<?php
// app/Controllers/AlmacenesController.php
namespace App\Controllers;

use App\Models\Empresa;
use App\Models\SyncLog;
use App\Services\SimacCloudClient;
use App\Core\Database;

class AlmacenesController
{
    private $empresa;
    private $syncLog;
    private $client;
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->empresa = new Empresa();
        $this->syncLog = new SyncLog();

        $e = $this->empresa->obtenerUnica();
        $this->client = new SimacCloudClient(
            $e['simac_cloud_url'] ?? null,
            $e['simac_api_token'] ?? null
        );
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $msg = $_GET['m'] ?? null;
        $empresa = $this->empresa->obtenerUnica();
        $carpeta = $this->carpetaControlParametros($empresa);
        $almacenesActuales = $this->leerAlmacenesActuales($carpeta);

        $GLOBALS['_page_title'] = 'Almacenes';
        $GLOBALS['_sidebar_current'] = 'almacenes';
        require APP_PATH . '/views/almacenes/index.php';
    }

    public function colocarJson(): array
    {
        $empresa = $this->empresa->obtenerUnica();
        $carpeta = $this->carpetaControlParametros($empresa);
        if (!is_dir($carpeta)) {
            @mkdir($carpeta, 0755, true);
        }

        if (empty($_FILES['almacenes_json']['name']) || ($_FILES['almacenes_json']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'Selecciona un archivo almacenes.json.'];
        }

        $contenido = (string)@file_get_contents($_FILES['almacenes_json']['tmp_name']);
        $decoded = json_decode($contenido, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'message' => 'El archivo no contiene un JSON válido.'];
        }

        file_put_contents($carpeta . 'almacenes.json', $contenido);
        $this->syncLog->registrar('control_parametros', 'colocacion', 'ok', 1, 'almacenes.json colocado en ' . $carpeta);

        // Subir a la nube de inmediato
        $res = $this->subirAlmacenes($carpeta);
        if (empty($res['ok'])) {
            return ['ok' => false, 'message' => 'JSON colocado pero falló la subida: ' . ($res['message'] ?? 'desconocido')];
        }
        return [
            'ok'      => true,
            'message' => 'almacenes.json colocado y subido a la nube: ' . ($res['message'] ?? 'OK'),
        ];
    }

    private function subirAlmacenes(string $carpeta): array
    {
        $empresa = $this->empresa->obtenerUnica();
        $companyCode = trim((string)($empresa['company_code'] ?? ''));

        $archivo = $carpeta . 'almacenes.json';
        if (!is_file($archivo)) {
            return ['ok' => false, 'message' => 'No existe almacenes.json en la carpeta.'];
        }

        $multipart = ['almacenes' => $archivo];
        $manifiesto = [
            'token'          => $this->client->token(),
            'codigo_clinica' => $companyCode,
            'empresa'        => $empresa['nombre'] ?? null,
            'fecha_envio'    => date('c'),
            'enviado_por'    => 'simac_webservice',
            'estado'         => 'pendiente',
            'archivos'       => ['almacenes.json'],
        ];

        $res = $this->client->postMultipart('/index.php?page=control_parametros_sync_recibir', $multipart, $manifiesto);

        $this->syncLog->registrar(
            'control_parametros',
            'subida',
            $res['ok'] ? 'ok' : 'error',
            1,
            $res['message'] ?? json_encode($res)
        );

        return $res;
    }

    private function leerAlmacenesActuales(string $carpeta): ?array
    {
        $archivo = $carpeta . 'almacenes.json';
        if (!is_file($archivo)) {
            return null;
        }
        $contenido = @file_get_contents($archivo);
        $decoded = json_decode($contenido, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function carpetaControlParametros(array $empresa): string
    {
        $rutaConfig = trim((string)($empresa['ruta_control_parametros'] ?? ''));
        if ($rutaConfig !== '') {
            $rutaConfig = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaConfig);
            if (preg_match('#^[A-Za-z]:[\\\\/]#', $rutaConfig) || preg_match('#^[\\\\/]#', $rutaConfig)) {
                return rtrim($rutaConfig, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }
            return rtrim(BASE_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                . ltrim($rutaConfig, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        $companyCode = trim((string)($empresa['company_code'] ?? ''));
        return BASE_PATH . '/public/uploads/empresas/'
            . ($companyCode !== '' ? $companyCode : 'empresa')
            . '/archivos/control_parametros/';
    }
}
