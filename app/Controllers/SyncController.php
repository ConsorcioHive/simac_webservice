<?php
// app/Controllers/SyncController.php
namespace App\Controllers;

use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\SyncLog;
use App\Services\SimacCloudClient;
use App\Services\FileDropService;
use App\Helpers\Session;
use App\Core\Database;

class SyncController
{
    private $empresa;
    private $usuario;
    private $syncLog;
    private $client;
    private $drop;
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->empresa = new Empresa();
        $this->usuario = new Usuario();
        $this->syncLog = new SyncLog();
        $this->drop    = new FileDropService();

        // Cliente de nube usando los parámetros configurados por clínica
        // (pestaña Administración). Si están vacíos, usa los valores globales.
        $e = $this->empresa->obtenerUnica();
        $this->client = new SimacCloudClient(
            $e['simac_cloud_url'] ?? null,
            $e['simac_api_token'] ?? null
        );
    }

    // Sube un paquete JSON de la clínica a SIMAC cloud.
    public function subir()
    {
        $empresa = $this->empresa->obtenerUnica();
        $usuarios = $this->usuario->listarPorEmpresa($empresa['id'] ?? 0);

        $payload = [
            'codigo_clinica' => $empresa['company_code'] ?? null,
            'empresa'        => $empresa,
            'usuarios'       => $usuarios,
            'generado_en'    => date('c'),
        ];

        $res = $this->client->post('/api/sync/upload', $payload);

        $this->syncLog->registrar(
            'paquete_clinica',
            'subida',
            $res['ok'] ? 'ok' : 'error',
            count($usuarios),
            $res['message'] ?? json_encode($res)
        );

        return $res;
    }

    // Sube los archivos de control de parámetros (prespuestos/servicios) de la
    // clínica a SIMAC cloud junto con su manifiesto.
    // Si $soloSiCambio=true, solo envía si el hash de los archivos difiere del
    // último hash subido con éxito (detecta archivos nuevos del sistema externo).
    public function subirArchivos($soloSiCambio = false)
    {
        $empresa = $this->empresa->obtenerUnica();
        $empresaId = (int)($empresa['id'] ?? 0);
        $companyCode = trim((string)($empresa['company_code'] ?? ''));

        // Carpeta local donde la clínica deposita los JSON (configurable por
        // clínica en Administración; por defecto igual estructura que la nube)
        $carpeta = $this->carpetaControlParametros($empresa);

        $prespuestos = $carpeta . 'prespuestos.json';
        $servicios   = $carpeta . 'servicios.json';
        if (!is_file($prespuestos) || !is_file($servicios)) {
            return ['ok' => false, 'message' => 'No existen prespuestos.json y servicios.json en ' . $carpeta];
        }

        // Hash combinado de los archivos actuales
        $hashActual = hash('sha256',
            (string)file_get_contents($prespuestos) .
            (string)file_get_contents($servicios)
        );

        if ($soloSiCambio) {
            $ultimoHash = $this->ultimoHashSubido();
            if ($ultimoHash !== null && $ultimoHash === $hashActual) {
                return ['ok' => true, 'message' => 'Sin cambios: los archivos ya fueron subidos.', 'sin_cambios' => true];
            }
        }

        $archivos = ['prespuestos.json', 'servicios.json'];
        $manifiesto = [
            'token'         => $this->client->token(),
            'codigo_clinica'=> $companyCode,
            'empresa'       => $empresa['nombre'] ?? null,
            'fecha_envio'   => date('c'),
            'enviado_por'   => 'simac_webservice',
            'estado'        => 'pendiente',
            'archivos'      => $archivos,
        ];

        $res = $this->client->postMultipart('/index.php?page=control_parametros_sync_recibir', [
            'prespuestos' => $prespuestos,
            'servicios'   => $servicios,
        ], $manifiesto);

        $this->syncLog->registrar(
            'control_parametros',
            'subida',
            $res['ok'] ? 'ok' : 'error',
            count($archivos),
            $res['message'] ?? json_encode($res)
        );

        // Si subió bien, recordar el hash para no reenviar lo mismo
        if (!empty($res['ok'])) {
            $this->syncLog->registrar('control_parametros_hash', 'subida', 'ok', 0, $hashActual);
        }

        return $res;
    }

    // Último hash subido con éxito (último registro control_parametros_hash).
    private function ultimoHashSubido()
    {
        $stmt = $this->pdo->prepare(
            "SELECT mensaje FROM sync_log
             WHERE tipo = 'control_parametros_hash' AND estado = 'ok'
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? $row['mensaje'] : null;
    }

    // Lee la respuesta que dejó la nube (respuesta_<token>.json) tras procesar
    // el último envío de control de parámetros.
    public function leerRespuesta()
    {
        $empresa = $this->empresa->obtenerUnica();
        $carpeta = $this->carpetaControlParametros($empresa);

        $archivo = $carpeta . 'respuesta_' . $this->client->token() . '.json';
        if (!is_file($archivo)) {
            return ['ok' => false, 'message' => 'No hay respuesta pendiente de la nube.'];
        }
        $decoded = json_decode((string)file_get_contents($archivo), true);
        return ['ok' => true, 'data' => $decoded, 'archivo' => basename($archivo)];
    }

    // Resuelve la carpeta local de control de parámetros: usa la ruta
    // configurada por la clínica en Administración (ruta_control_parametros)
    // si existe, o la ruta por defecto de la empresa.
    private function carpetaControlParametros(array $empresa): string
    {
        $rutaConfig = trim((string)($empresa['ruta_control_parametros'] ?? ''));
        if ($rutaConfig !== '') {
            $rutaConfig = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaConfig);
            // Absoluta (con letra de unidad o separador inicial) o relativa a la raíz del webservice
            if (preg_match('/^[A-Za-z]:\\\\/', $rutaConfig) || preg_match('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', $rutaConfig)) {
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

    // Baja un paquete JSON desde SIMAC cloud.
    public function bajar()
    {
        $res = $this->client->get('/api/sync/download', [
            'codigo_clinica' => Session::get('empresa_codigo'),
        ]);

        $this->syncLog->registrar(
            'paquete_clinica',
            'bajada',
            $res['ok'] ? 'ok' : 'error',
            0,
            $res['message'] ?? json_encode($res)
        );

        return $res;
    }

    public function listarInbox()
    {
        return $this->drop->listarInbox();
    }

    public function listarOutbox()
    {
        return $this->drop->listarOutbox();
    }

    public function moverAOutbox($nombre)
    {
        return $this->drop->moverAOutbox($nombre);
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action'])) {
            if ($_POST['_action'] === 'subir') {
                $res = $this->subir();
                $msg = $res['ok'] ? 'Subida enviada a SIMAC.' : ('Error: ' . ($res['message'] ?? 'desconocido'));
            } elseif ($_POST['_action'] === 'bajar') {
                $res = $this->bajar();
                $msg = $res['ok'] ? 'Bajada recibida de SIMAC.' : ('Error: ' . ($res['message'] ?? 'desconocido'));
            } elseif ($_POST['_action'] === 'subir_archivos') {
                $res = $this->subirArchivos();
                $msg = $res['ok'] ? 'Control de parámetros subido a SIMAC.' : ('Error: ' . ($res['message'] ?? 'desconocido'));
            } elseif ($_POST['_action'] === 'leer_respuesta') {
                $res = $this->leerRespuesta();
                if ($res['ok']) {
                    $r = $res['data'] ?? [];
                    $msg = 'Respuesta: ' . ($r['estado'] ?? '?') . ' — ' . ($r['mensaje'] ?? '') . ' (' . ($res['archivo'] ?? '') . ')';
                } else {
                    $msg = 'Error: ' . ($res['message'] ?? 'desconocido');
                }
            } elseif ($_POST['_action'] === 'mover_outbox') {
                $this->moverAOutbox($_POST['archivo'] ?? '');
                $msg = 'Archivo movido a outbox.';
            } else {
                $msg = '';
            }
            header('Location: index.php?page=sync&m=' . urlencode($msg));
            exit;
        }

        $inbox  = $this->listarInbox();
        $outbox = $this->listarOutbox();
        $logs   = $this->syncLog->ultimos(15);

        $GLOBALS['_page_title']   = 'Sincronización';
        $GLOBALS['_sidebar_current'] = 'sync';
        require APP_PATH . '/views/sync/index.php';
    }

    // Área de consulta del historial local: todo lo sucedido en este webservice.
    public function log()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $porPagina = 50;
        $offset = ($pagina - 1) * $porPagina;

        $stmt = $this->pdo->prepare(
            "SELECT * FROM sync_log ORDER BY id DESC LIMIT :lim OFFSET :off"
        );
        $stmt->bindValue('lim', $porPagina, \PDO::PARAM_INT);
        $stmt->bindValue('off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll();

        $total = (int)$this->pdo->query("SELECT COUNT(*) FROM sync_log")->fetchColumn();

        $GLOBALS['_page_title']   = 'Historial local';
        $GLOBALS['_sidebar_current'] = 'sync';
        require APP_PATH . '/views/sync/log.php';
    }
}
