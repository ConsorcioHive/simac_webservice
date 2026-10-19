<?php
// app/Controllers/LotesEgresosController.php
// Descarga lotes de egreso desde la nube (simacweb.app) a:
//   public/uploads/empresas/<code>/archivos/lotes_egresos/<codigo_lote>/
//     consolidado_<codigo>.json
//     manifiesto_<codigo>.json
//     admision_<id>/<adjuntos...>
namespace App\Controllers;

use App\Models\Empresa;
use App\Models\SyncLog;
use App\Services\SimacCloudClient;
use App\Services\LoteNotify;
use App\Core\Database;
use App\Controllers\ApiController;

class LotesEgresosController
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
        $lotesLocales = $this->listarLotesLocales();
        $pendientesNube = $this->cloudPendientes();
        $pendientesError = null;
        if ($pendientesNube === null) {
            $pendientesError = 'No se pudo consultar lotes pendientes en la nube (token/conexión).';
            $pendientesNube = [];
        }

        $GLOBALS['_page_title'] = 'Lotes de Egreso';
        $GLOBALS['_sidebar_current'] = 'lotes_egresos';
        require APP_PATH . '/views/lotes_egresos/index.php';
    }

    /** POST: trae un lote por código. */
    public function traer(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $codigo = trim((string)($_POST['codigo_lote'] ?? $_GET['codigo_lote'] ?? ''));
        if ($codigo === '') {
            return ['ok' => false, 'message' => 'Indica el código del lote.'];
        }
        return $this->traerLote($codigo);
    }

    /** POST/CLI: trae todos los pendientes de la nube. */
    public function traerTodos(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $pendientes = $this->cloudPendientes();
        if ($pendientes === null) {
            return ['ok' => false, 'message' => 'No se pudo consultar la lista de pendientes en la nube.'];
        }
        if (empty($pendientes)) {
            return ['ok' => true, 'message' => 'No hay lotes pendientes en la nube.'];
        }
        $detalles = [];
        $oks = 0;
        foreach ($pendientes as $p) {
            $cod = trim((string)($p['codigo_lote'] ?? ''));
            if ($cod === '') {
                continue;
            }
            $r = $this->traerLote($cod);
            $r['codigo'] = $cod;
            $detalles[] = $r;
            if (!empty($r['ok'])) {
                $oks++;
            }
        }
        return [
            'ok' => true,
            'message' => $oks . ' de ' . count($pendientes) . ' lote(s) procesado(s).',
            'detalles' => $detalles,
        ];
    }

    /**
     * AJAX: explora el árbol de archivos de un lote descargado.
     * GET codigo=<lote>&sub=<ruta relativa opcional dentro del lote>
     */
    public function explorar(): void
    {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $codigo = trim((string)($_GET['codigo'] ?? ''));
        $sub = trim((string)($_GET['sub'] ?? ''));

        if ($codigo === '' || !preg_match('/^[A-Za-z0-9_\-]+$/', $codigo)) {
            echo json_encode(['ok' => false, 'msg' => 'Código de lote inválido.']);
            return;
        }

        $base = rtrim($this->carpetaLotes(), '/\\') . '/';
        $dirLote = $base . $codigo . '/';
        if (!is_dir($dirLote)) {
            echo json_encode(['ok' => false, 'msg' => 'El lote no existe en disco.']);
            return;
        }

        // Ruta relativa: sin .., sin absoluta, solo segmentos seguros
        $ruta = $dirLote;
        if ($sub !== '') {
            $sub = str_replace('\\', '/', $sub);
            if (strpos($sub, '..') !== false || $sub[0] === '/' || !preg_match('#^[A-Za-z0-9_\-./ ]+$#', $sub)) {
                echo json_encode(['ok' => false, 'msg' => 'Ruta no permitida.']);
                return;
            }
            $ruta = rtrim($dirLote, '/\\') . '/' . trim($sub, '/') . '/';
        }

        $realBase = realpath($dirLote);
        $realRuta = realpath(rtrim($ruta, '/\\'));
        if ($realBase === false || $realRuta === false || strpos($realRuta, $realBase) !== 0 || !is_dir($realRuta)) {
            echo json_encode(['ok' => false, 'msg' => 'Ruta fuera del lote.']);
            return;
        }

        $items = [];
        $manInfo = null;
        $manPath = $dirLote . 'manifiesto_' . $codigo . '.json';
        if (is_file($manPath)) {
            $manTmp = json_decode((string)@file_get_contents($manPath), true);
            if (is_array($manTmp)) {
                $manInfo = $manTmp;
            }
        }

        // Mapa id de admisión → etiquetas legibles (correlativo, paciente…)
        $mapaAdm = [];
        if (is_array($manInfo)) {
            foreach (($manInfo['admisiones'] ?? []) as $mAdm) {
                $mid = (int)($mAdm['id'] ?? 0);
                if ($mid > 0) {
                    $mapaAdm[$mid] = [
                        'correlativo'      => $mAdm['correlativo'] ?? null,
                        'fecha'            => $mAdm['fecha'] ?? null,
                        'responsable_tipo' => $mAdm['responsable_tipo'] ?? null,
                        'paciente_nombre'  => $mAdm['paciente_nombre'] ?? null,
                    ];
                }
            }
        }

        foreach (scandir($realRuta) ?: [] as $nombre) {
            if ($nombre === '.' || $nombre === '..') {
                continue;
            }
            $full = $realRuta . DIRECTORY_SEPARATOR . $nombre;
            $esDir = is_dir($full);
            $rel = ($sub !== '' ? trim($sub, '/') . '/' : '') . $nombre;
            $item = [
                'nombre' => $nombre,
                'tipo'   => $esDir ? 'dir' : 'file',
                'ruta'   => $rel,
                'ruta_local' => $full . ($esDir ? DIRECTORY_SEPARATOR : ''),
                'tam'    => $esDir ? null : (int)@filesize($full),
                'mtime'  => date('Y-m-d H:i', (int)@filemtime($full)),
            ];
            // admision_<id> → correlativo y etiqueta desde el manifiesto
            if ($esDir && preg_match('/^admision_(\d+)$/', $nombre, $mm)) {
                $item['admision_id'] = (int)$mm[1];
                $item['admision'] = $mapaAdm[(int)$mm[1]] ?? null;
            }
            $items[] = $item;
        }

        usort($items, function ($a, $b) {
            if ($a['tipo'] !== $b['tipo']) {
                return $a['tipo'] === 'dir' ? -1 : 1;
            }
            return strcmp($a['nombre'], $b['nombre']);
        });

        // Resumen del manifiesto si estamos en la raíz del lote
        $meta = [];
        if (is_array($manInfo)) {
            $meta = [
                'total_admisiones' => (int)($manInfo['total_admisiones'] ?? 0),
                'total_documentos' => (int)($manInfo['total_documentos'] ?? 0),
                'empresa'          => (string)($manInfo['empresa']['nombre'] ?? ''),
                'fecha'            => (string)($manInfo['fecha_hora'] ?? ''),
            ];
        }

        echo json_encode([
            'ok'     => true,
            'codigo' => $codigo,
            'sub'    => $sub,
            'items'  => $items,
            'meta'   => $meta,
            'ruta_local'  => $realBase . DIRECTORY_SEPARATOR,
            'ruta_relativa' => 'public/uploads/empresas/'
                . ($this->empresa->obtenerUnica()['company_code'] ?? '')
                . '/archivos/lotes_egresos/' . $codigo . '/',
            // URL base para abrir archivos (ruta web relativa al webservice)
            'url_base' => BASE_URL . 'public/uploads/empresas/'
                . ($this->empresa->obtenerUnica()['company_code'] ?? '')
                . '/archivos/lotes_egresos/' . rawurlencode($codigo) . '/',
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * AJAX: devuelve el contenido de un JSON del lote (manifiesto, consolidado,
     * admision_<id>/datos.json, datos.local.json) formateado para el visor.
     * GET codigo=<lote>&archivo=<ruta relativa .json>
     */
    public function verJson(): void
    {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $codigo = trim((string)($_GET['codigo'] ?? ''));
        $archivo = trim((string)($_GET['archivo'] ?? ''));

        if ($codigo === '' || !preg_match('/^[A-Za-z0-9_\-]+$/', $codigo)) {
            echo json_encode(['ok' => false, 'msg' => 'Código de lote inválido.']);
            return;
        }
        $archivo = str_replace('\\', '/', $archivo);
        if ($archivo === '' || substr($archivo, -5) !== '.json'
            || strpos($archivo, '..') !== false || $archivo[0] === '/'
            || !preg_match('#^[A-Za-z0-9_\-./ ]+$#', $archivo)) {
            echo json_encode(['ok' => false, 'msg' => 'Archivo no permitido.']);
            return;
        }

        $dirLote = rtrim($this->carpetaLotes(), '/\\') . '/' . $codigo . '/';
        $realBase = realpath($dirLote);
        $realFile = realpath(rtrim($dirLote, '/\\') . '/' . ltrim($archivo, '/'));
        if ($realBase === false || $realFile === false
            || strpos($realFile, $realBase) !== 0 || !is_file($realFile)) {
            echo json_encode(['ok' => false, 'msg' => 'No existe el archivo en el lote.']);
            return;
        }

        $raw = (string)@file_get_contents($realFile);
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $pretty = $raw;
        }

        echo json_encode([
            'ok'        => true,
            'codigo'    => $codigo,
            'archivo'   => $archivo,
            'es_json'   => json_last_error() === JSON_ERROR_NONE,
            'contenido' => $pretty,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Descarga un lote completo: JSONs raíz + adjuntos por admisión + confirmación.
     * Idempotente: si el archivo ya existe no lo vuelve a bajar.
     */
    public function traerLote(string $codigo): array
    {
        $codigo = trim($codigo);
        if ($codigo === '' || !preg_match('/^[A-Za-z0-9_\-]+$/', $codigo)) {
            return ['ok' => false, 'message' => 'Código de lote inválido.'];
        }

        $res = $this->client->get('/index.php', ['page' => 'egreso_lote_pull', 'codigo_lote' => $codigo]);
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        if (empty($res['ok']) || empty($data['ok'])) {
            $msg = $data['error'] ?? ($res['error'] ?? ($res['data']['msg'] ?? 'HTTP ' . ($res['http'] ?? '?')));
            $this->syncLog->registrar('lotes_egresos', 'descarga', 'error', 0, "Pull $codigo: $msg");
            return ['ok' => false, 'message' => 'Pull falló: ' . $msg];
        }

        $payload = $data['json'] ?? null;
        $manifiesto = $data['manifiesto'] ?? null;
        if (!is_array($payload) || !is_array($manifiesto)) {
            return ['ok' => false, 'message' => 'El lote no trae payload/manifiesto válido.'];
        }

        $dirLote = rtrim($this->carpetaLotes(), '/') . '/' . $codigo . '/';
        $yaExistia = is_dir($dirLote) && is_file($dirLote . 'manifiesto_' . $codigo . '.json');
        if (!is_dir($dirLote)) {
            @mkdir($dirLote, 0775, true);
        }

        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        @file_put_contents($dirLote . 'consolidado_' . $codigo . '.json', json_encode($payload, $flags));
        @file_put_contents($dirLote . 'manifiesto_' . $codigo . '.json', json_encode($manifiesto, $flags));

        $formato = (string)($manifiesto['formato_version'] ?? '1.0');
        $manifestHash = hash('sha256', json_encode($manifiesto, JSON_UNESCAPED_UNICODE));
        $admisiones = is_array($manifiesto['admisiones'] ?? null) ? $manifiesto['admisiones'] : [];

        // Índice del payload consolidado por id (respaldo del formato 2.0 / legacy).
        $jsonPorId = [];
        foreach ((array)($payload['admisiones'] ?? []) as $j) {
            $jid = (int)($j['id'] ?? 0);
            if ($jid > 0) {
                $jsonPorId[$jid] = $j;
            }
        }

        $docsOk = 0;
        $docsErr = 0;
        $docsYa = 0;
        $datosOk = 0;
        $datosErr = 0;
        $totalAdm = 0;
        $docsPorAdm = [];

        foreach ($admisiones as $adm) {
            $admId = (int)($adm['id'] ?? 0);
            if ($admId <= 0) {
                continue;
            }
            $totalAdm++;
            $dirAdm = $dirLote . 'admision_' . $admId . '/';
            if (!is_dir($dirAdm)) {
                @mkdir($dirAdm, 0775, true);
            }

            // 1) datos.json canónico (formato 2.0 vía endpoint; respaldo del payload).
            $json = null;
            $hashEsperado = trim((string)($adm['hash'] ?? ''));
            if ($formato === '2.0') {
                $resAdm = $this->client->get('/index.php', [
                    'page'        => 'egreso_lote_admision',
                    'codigo_lote' => $codigo,
                    'admision_id' => $admId,
                ]);
                $dAdm = is_array($resAdm['data'] ?? null) ? $resAdm['data'] : [];
                if (!empty($resAdm['ok']) && !empty($dAdm['ok']) && is_array($dAdm['json'] ?? null)) {
                    $json = $dAdm['json'];
                    if ($hashEsperado === '' && !empty($dAdm['hash'])) {
                        $hashEsperado = (string)$dAdm['hash'];
                    }
                }
            }
            if (!is_array($json) && isset($jsonPorId[$admId])) {
                $json = $jsonPorId[$admId];
            }
            if (!is_array($json)) {
                $datosErr++;
                continue;
            }
            if ($hashEsperado !== '') {
                $calc = hash('sha256', json_encode($json, JSON_UNESCAPED_UNICODE));
                if (!hash_equals($hashEsperado, $calc)) {
                    $this->syncLog->registrar('lotes_egresos', 'descarga', 'error', 0,
                        "Lote $codigo admisión $admId: hash de datos.json no coincide.");
                    $datosErr++;
                    continue;
                }
            }
            @file_put_contents($dirAdm . 'datos.json', json_encode($json, $flags));
            $datosOk++;

            // 2) Adjuntos de esta admisión.
            $docs = is_array($adm['documentos'] ?? null) ? $adm['documentos'] : [];
            foreach ($docs as $doc) {
                $url = trim((string)($doc['url'] ?? ''));
                $nombre = $this->nombreSeguro((string)($doc['nombre'] ?? ''));
                if ($url === '' || $nombre === '') {
                    $docsErr++;
                    continue;
                }
                $destino = $dirAdm . $nombre;
                if (is_file($destino) && filesize($destino) > 0) {
                    $docsOk++;
                    $docsYa++;
                    $docsPorAdm[$admId][] = $nombre;
                    continue;
                }
                if ($this->descargarArchivo($url, $destino)) {
                    $docsOk++;
                    $docsPorAdm[$admId][] = $nombre;
                } else {
                    $docsErr++;
                }
            }
        }

        // 3) datos.local.json: rutas locales y documentos. NO se mete en datos.json
        //    (que debe conservar su hash).
        $companyCode = trim((string)($this->empresa->obtenerUnica()['company_code'] ?? ''));
        foreach ($admisiones as $adm) {
            $admId = (int)($adm['id'] ?? 0);
            if ($admId <= 0) {
                continue;
            }
            $dirAdm = $dirLote . 'admision_' . $admId . '/';
            $local = [
                'id'              => $admId,
                'correlativo'     => $adm['correlativo'] ?? null,
                'paciente_nombre' => $adm['paciente_nombre'] ?? null,
                'ruta_local'      => $dirAdm,
                'ruta_relativa'   => 'public/uploads/empresas/' . $companyCode
                    . '/archivos/lotes_egresos/' . $codigo . '/admision_' . $admId . '/',
                'documentos'      => array_map(function ($n) use ($dirAdm) {
                    return ['nombre' => $n, 'ruta_local' => $dirAdm . $n];
                }, $docsPorAdm[$admId] ?? []),
            ];
            @file_put_contents($dirAdm . 'datos.local.json', json_encode($local, $flags));
        }

        $totalDocs = 0;
        foreach ($docsPorAdm as $nombres) {
            $totalDocs += count($nombres);
        }
        $nAdm = $totalAdm;
        $prefijo = $yaExistia ? 'Re-descarga de lote local' : 'Lote nuevo';
        $reusa = $docsYa > 0 ? " ($docsYa archivo(s) ya existían, no se repitieron)" : '';
        $resumen = "$prefijo $codigo: $datosOk/$totalAdm datos.json, $docsOk archivos$reusa";

        // Solo confirmar si bajó todo (datos.json + documentos): así "pendientes"
        // permite reintentar lo que faltó.
        if ($datosErr > 0 || $docsErr > 0) {
            $this->syncLog->registrar('lotes_egresos', 'descarga', 'error', $docsOk,
                "$resumen (datos err=$datosErr, docs err=$docsErr, sin confirmar)");
            return [
                'ok' => false,
                'message' => "$resumen — $datosErr dato(s) y $docsErr archivo(s) fallaron. No se confirmó; reintenta.",
            ];
        }

        $confirm = $this->client->post('/index.php?page=egreso_lote_confirmar', ['codigo_lote' => $codigo]);
        $confirmData = is_array($confirm['data'] ?? null) ? $confirm['data'] : [];
        $confirmado = !empty($confirm['ok']) && !empty($confirmData['ok']);

        if (!$confirmado) {
            $this->syncLog->registrar('lotes_egresos', 'descarga', 'error', $docsOk, "$resumen pero falló confirmación nube");
            return [
                'ok' => true,
                'message' => "$resumen — guardado, pero falló la confirmación a la nube.",
            ];
        }

        $this->syncLog->registrar('lotes_egresos', 'descarga', 'ok', $docsOk, $resumen . ' (confirmado)');
        // Marcar en lotes_locales como disponible para que el ERP externo lo consuma.
        try {
            $api = new ApiController();
            $companyCode = trim((string)($this->empresa->obtenerUnica()['company_code'] ?? ''));
            $man = is_array($manifiesto) ? $manifiesto : [];
            $api->marcarDisponible($companyCode, $codigo, [
                'ruta_local' => rtrim($dirLote, '/\\') . DIRECTORY_SEPARATOR,
                'total_admisiones' => (int)($man['total_admisiones'] ?? $nAdm),
                'total_documentos' => (int)($man['total_documentos'] ?? $totalDocs),
                'docs_ok' => $docsOk,
                'estado_nube' => 'recibido_local',
                'formato' => $formato,
                'manifest_hash' => $manifestHash,
                'metadata' => [
                    'confirmado_nube' => true,
                    'ya_existia' => $yaExistia,
                ],
            ]);
        } catch (\Throwable $e) {
            // No romper la descarga si falla el estado local
            $this->syncLog->registrar('lotes_egresos', 'estado', 'error', 0,
                "No se pudo marcar disponible $codigo: " . $e->getMessage());
        }
        // Aviso solo en lote nuevo (no en re-descarga)
        if (!$yaExistia) {
            try {
                LoteNotify::loteNuevoDisponible($codigo, [
                    'admisiones' => $nAdm,
                    'documentos' => $docsOk,
                    'ruta_local' => rtrim($dirLote, '/\\') . DIRECTORY_SEPARATOR,
                ]);
                try {
                    $this->pdo->prepare(
                        'UPDATE lotes_locales SET notificado_en = :n
                         WHERE company_code = :c AND codigo = :k'
                    )->execute(['n' => date('Y-m-d H:i:s'), 'c' => $companyCode, 'k' => $codigo]);
                } catch (\Throwable $e2) {
                    // La marca de notificación es best-effort.
                }
            } catch (\Throwable $e) {
                $this->syncLog->registrar('lotes_egresos', 'notify', 'error', 0,
                    "Email lote nuevo falló: " . $e->getMessage());
            }
        }
        return [
            'ok' => true,
            'message' => "$resumen descargado y confirmado.",
        ];
    }

    /**
     * Contadores para el badge del sidebar (sin llamar a la nube).
     * disponibles = lotes listos que el ERP aún no consume.
     */
    public static function contadoresBadge(): array
    {
        $out = ['disponibles' => 0, 'pendientes_nube' => null];
        try {
            $pdo = Database::getConnection();
            $e = (new Empresa())->obtenerUnica();
            $code = trim((string)($e['company_code'] ?? ''));
            if ($code !== '') {
                $stmt = $pdo->prepare(
                    "SELECT COUNT(*) AS n FROM lotes_locales WHERE company_code = :c AND estado = 'disponible'"
                );
                $stmt->execute(['c' => $code]);
                $out['disponibles'] = (int)($stmt->fetchColumn() ?: 0);
            }
        } catch (\Throwable $e) {
            // tabla puede no existir aún
        }
        return $out;
    }

    /** Lotes ya bajados a disco (carpetas). */
    private function listarLotesLocales(): array
    {
        $base = $this->carpetaLotes();
        if (!is_dir($base)) {
            return [];
        }
        $out = [];
        foreach (scandir($base) ?: [] as $nombre) {
            if ($nombre === '.' || $nombre === '..') {
                continue;
            }
            $dir = $base . $nombre;
            if (!is_dir($dir)) {
                continue;
            }
            $info = $this->infoLote($nombre);
            $item = [
                'codigo' => $nombre,
                'fecha' => date('Y-m-d H:i', @filemtime($dir) ?: time()),
                'orden_fecha' => @filemtime($dir) ?: 0,
                'ruta_local' => rtrim($dir, '/\\') . DIRECTORY_SEPARATOR,
                'estado' => 'descargado',
                'estado_nube' => $info['estado_nube'] ?? null,
                'formato' => $info['formato'] ?? '1.0',
                'procesado_nube_en' => $info['procesado_nube_en'] ?? null,
                'total_admisiones' => null,
                'total_documentos' => null,
                'admisiones' => [],
                'docs_totales' => 0,
                'datos_ok' => 0,
                'json_ok' => false,
            ];
            $item['estado'] = $this->estadoLote($nombre) ?: 'descargado';

            $manPath = $dir . '/manifiesto_' . $nombre . '.json';
            $mapaAdm = [];
            $manOk = false;
            if (is_file($manPath)) {
                $man = json_decode((string)@file_get_contents($manPath), true);
                if (is_array($man)) {
                    $manOk = true;
                    $item['total_admisiones'] = (int)($man['total_admisiones'] ?? 0);
                    $item['total_documentos'] = (int)($man['total_documentos'] ?? 0);
                    $item['empresa'] = (string)($man['empresa']['nombre'] ?? '');
                    $item['formato'] = (string)($man['formato_version'] ?? ($item['formato'] ?: '1.0'));
                    // Fecha real del envío (más confiable que mtime de la carpeta)
                    $fh = (string)($man['fecha_hora'] ?? '');
                    if ($fh !== '') {
                        $item['fecha'] = substr($fh, 0, 16);
                        $ts = strtotime($fh);
                        if ($ts !== false) {
                            $item['orden_fecha'] = $ts;
                        }
                    }
                    // Mapa id → correlativo/paciente para las carpetas admision_*
                    foreach (($man['admisiones'] ?? []) as $mAdm) {
                        $mid = (int)($mAdm['id'] ?? 0);
                        if ($mid > 0) {
                            $mapaAdm[$mid] = [
                                'correlativo' => $mAdm['correlativo'] ?? null,
                                'paciente_nombre' => $mAdm['paciente_nombre'] ?? null,
                            ];
                        }
                    }
                }
            }
            foreach (scandir($dir) ?: [] as $sub) {
                if (strpos($sub, 'admision_') !== 0 || !is_dir($dir . '/' . $sub)) {
                    continue;
                }
                $archivos = array_diff(scandir($dir . '/' . $sub) ?: [], ['.', '..']);
                $docs = [];
                $tieneDatos = false;
                foreach ($archivos as $f) {
                    if ($f === 'datos.json' || $f === 'datos.local.json') {
                        if ($f === 'datos.json') {
                            $tieneDatos = true;
                        }
                        continue;
                    }
                    $docs[] = $f;
                }
                $nDocs = count($docs);
                $item['docs_totales'] += $nDocs;
                if ($tieneDatos) {
                    $item['datos_ok']++;
                }
                $aid = 0;
                if (preg_match('/^admision_(\d+)$/', $sub, $mm)) {
                    $aid = (int)$mm[1];
                }
                $item['admisiones'][] = [
                    'carpeta' => $sub,
                    'docs' => $nDocs,
                    'datos_json' => $tieneDatos,
                    'admision_id' => $aid,
                    'correlativo' => $mapaAdm[$aid]['correlativo'] ?? null,
                    'paciente_nombre' => $mapaAdm[$aid]['paciente_nombre'] ?? null,
                ];
            }
            // json_ok: manifiesto + consolidado (compat) y, en formato 2.0, un
            // datos.json por cada admisión del manifiesto.
            $item['json_ok'] = $manOk
                && is_file($dir . '/consolidado_' . $nombre . '.json')
                && ($item['formato'] !== '2.0'
                    || $item['total_admisiones'] === 0
                    || $item['datos_ok'] >= $item['total_admisiones']);
            $out[] = $item;
        }
        // Más reciente primero (fecha del manifiesto / carpeta)
        usort($out, function ($a, $b) {
            $fa = (int)($a['orden_fecha'] ?? 0);
            $fb = (int)($b['orden_fecha'] ?? 0);
            if ($fa !== $fb) {
                return $fb <=> $fa;
            }
            return strcmp((string)$b['codigo'], (string)$a['codigo']);
        });
        return $out;
    }

    /** null = no se pudo consultar; array = lista de lotes. */
    private function cloudPendientes(): ?array
    {
        $res = $this->client->get('/index.php', ['page' => 'egreso_lotes_pendientes']);
        if (empty($res['ok']) || empty($res['data']['ok'])) {
            return null;
        }
        $lotes = $res['data']['lotes'] ?? [];
        return is_array($lotes) ? $lotes : [];
    }

    private function carpetaLotes(): string
    {
        $empresa = $this->empresa->obtenerUnica();
        $companyCode = trim((string)($empresa['company_code'] ?? ''));
        $base = BASE_PATH . '/public/uploads/empresas/'
            . ($companyCode !== '' ? $companyCode : 'empresa')
            . '/archivos/lotes_egresos';
        if (!is_dir($base)) {
            @mkdir($base, 0775, true);
        }
        return rtrim($base, '/\\') . '/';
    }

    /** Estado del lote en lotes_locales (descargado|disponible|consumido). */
    private function estadoLote(string $codigo): string
    {
        try {
            $companyCode = trim((string)($this->empresa->obtenerUnica()['company_code'] ?? ''));
            $stmt = $this->pdo->prepare(
                'SELECT estado FROM lotes_locales WHERE company_code = :c AND codigo = :k LIMIT 1'
            );
            $stmt->execute(['c' => $companyCode, 'k' => $codigo]);
            $row = $stmt->fetch();
            return $row ? (string)$row['estado'] : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /** Columnas nuevas de seguimiento (estado_nube, formato, procesado_nube_en). */
    private function infoLote(string $codigo): array
    {
        try {
            $companyCode = trim((string)($this->empresa->obtenerUnica()['company_code'] ?? ''));
            $stmt = $this->pdo->prepare(
                'SELECT estado_nube, formato, procesado_nube_en
                 FROM lotes_locales WHERE company_code = :c AND codigo = :k LIMIT 1'
            );
            $stmt->execute(['c' => $companyCode, 'k' => $codigo]);
            $row = $stmt->fetch();
            return $row ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function nombreSeguro(string $n): string
    {
        $n = basename(str_replace('\\', '/', $n));
        $n = preg_replace('/[^\w.\- ]+/u', '_', $n);
        $n = trim((string)$n, '._ ');
        return $n !== '' ? $n : 'archivo_' . date('YmdHis') . '.bin';
    }

    private function descargarArchivo(string $url, string $destino): bool
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 60,
                'user_agent' => 'simac_webservice-lotes_egresos',
                'follow_location' => 1,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $data = @file_get_contents($url, false, $ctx);
        if ($data === false || $data === '') {
            return false;
        }
        return @file_put_contents($destino, $data) !== false;
    }
}
