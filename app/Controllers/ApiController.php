<?php
// app/Controllers/ApiController.php
// API v1 del webservice — contrato para sistemas externos (ERP).
// Auth: header X-API-Key o Authorization: Bearer <token> (tabla api_tokens).
// Rutas (PATH_INFO o ?page=api_v1&path=...):
//   GET  /api/v1/salud
//   GET  /api/v1/lotes[?estado=disponible|descargado|consumido]
//   GET  /api/v1/lotes/{codigo}
//   GET  /api/v1/lotes/{codigo}/archivos[?sub=ruta]
//   POST /api/v1/lotes/{codigo}/consumir
namespace App\Controllers;

use App\Core\Database;
use App\Helpers\Json;
use App\Models\Empresa;

class ApiController
{
    /** @var array|null Fila api_tokens autenticada */
    private $auth;
    private $pdo;
    private $empresa;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->empresa = new Empresa();
    }

    public function handle(string $path, string $method): void
    {
        $path = trim($path, '/');
        $method = strtoupper($method);

        if ($path === '' || $path === 'salud') {
            $this->requerirAuth();
            Json::response([
                'ok' => true,
                'data' => [
                    'servicio' => 'simac_webservice',
                    'api' => 'v1',
                    'empresa' => $this->auth['company_code'] ?? null,
                    'time' => date('c'),
                ],
            ]);
        }

        if (strpos($path, 'lotes') !== 0) {
            $this->jsonError(404, 'Ruta no encontrada. Ver docs/api_v1.md');
        }

        $this->requerirAuth();
        $rest = ltrim(substr($path, strlen('lotes')), '/');
        $parts = $rest === '' ? [] : explode('/', $rest);

        // GET /lotes
        if (empty($parts)) {
            if ($method !== 'GET') {
                $this->jsonError(405, 'Método no permitido. Use GET.');
            }
            $this->listarLotes();
            return;
        }

        $codigo = $parts[0];
        if (!preg_match('/^[A-Za-z0-9_\-]+$/', $codigo)) {
            $this->jsonError(400, 'Código de lote inválido.');
        }

        // GET /lotes/{codigo}
        if (count($parts) === 1) {
            if ($method !== 'GET') {
                $this->jsonError(405, 'Método no permitido. Use GET.');
            }
            $this->detalleLote($codigo);
            return;
        }

        // GET /lotes/{codigo}/archivos
        if (count($parts) === 2 && $parts[1] === 'archivos') {
            if ($method !== 'GET') {
                $this->jsonError(405, 'Método no permitido. Use GET.');
            }
            $this->archivosLote($codigo);
            return;
        }

        // POST /lotes/{codigo}/consumir
        if (count($parts) === 2 && $parts[1] === 'consumir') {
            if ($method !== 'POST') {
                $this->jsonError(405, 'Método no permitido. Use POST.');
            }
            $this->consumirLote($codigo);
            return;
        }

        $this->jsonError(404, 'Ruta no encontrada. Ver docs/api_v1.md');
    }

    // ── Auth ───────────────────────────────────────────────────────────────

    private function requerirAuth(): void
    {
        if ($this->auth !== null) {
            return;
        }
        $token = $this->extraerToken();
        if ($token === '') {
            $this->jsonError(401, 'Falta API key. Envíe header X-API-Key o Authorization: Bearer <token>.');
        }
        $stmt = $this->pdo->prepare(
            'SELECT id, company_code, token, nombre FROM api_tokens WHERE token = :t AND activo = 1 LIMIT 1'
        );
        $stmt->execute(['t' => $token]);
        $row = $stmt->fetch();
        if (!$row) {
            $this->jsonError(401, 'API key inválida o inactiva.');
        }
        $this->auth = $row;
    }

    private function extraerToken(): string
    {
        foreach (['HTTP_X_API_KEY', 'HTTP_X_APIKEY'] as $k) {
            if (!empty($_SERVER[$k])) {
                return trim((string)$_SERVER[$k]);
            }
        }
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
        if ($auth !== '' && preg_match('/Bearer\s+(\S+)/i', $auth, $m)) {
            return trim($m[1]);
        }
        if (!empty($_GET['api_key'])) {
            return trim((string)$_GET['api_key']);
        }
        if (!empty($_POST['api_key'])) {
            return trim((string)$_POST['api_key']);
        }
        // PHP bajo Apache a veces no pasa Authorization al FastCGI
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strcasecmp($name, 'X-API-Key') === 0) {
                    return trim((string)$value);
                }
                if (strcasecmp($name, 'Authorization') === 0 && preg_match('/Bearer\s+(\S+)/i', (string)$value, $m)) {
                    return trim($m[1]);
                }
            }
        }
        return '';
    }

    private function companyCode(): string
    {
        return trim((string)($this->auth['company_code'] ?? ''));
    }

    // ── Endpoints ──────────────────────────────────────────────────────────

    private function listarLotes(): void
    {
        $codigoEmpresa = $this->companyCode();
        $estado = trim((string)($_GET['estado'] ?? ''));
        $permitidos = ['descargado', 'disponible', 'consumido'];
        if ($estado !== '' && !in_array($estado, $permitidos, true)) {
            $this->jsonError(400, 'estado inválido. Use: ' . implode('|', $permitidos) . ' (o vacío = todos).');
        }

        $sql = 'SELECT codigo, estado, ruta_local, total_admisiones, total_documentos,
                       descargado_en, disponible_en, consumido_en, updated_at
                FROM lotes_locales WHERE company_code = :c';
        $params = ['c' => $codigoEmpresa];
        if ($estado !== '') {
            $sql .= ' AND estado = :e';
            $params['e'] = $estado;
        }
        $sql .= ' ORDER BY descargado_en DESC, codigo DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll() ?: [];

        // Cruzar con disco: si existe carpeta pero no hay fila, reportarla como disponible si JSON ok
        $enDisco = $this->lotesEnDisco($codigoEmpresa);
        $vistas = [];
        foreach ($rows as $r) {
            $vistas[$r['codigo']] = true;
            $r['en_disco'] = !empty($enDisco[$r['codigo']]);
            $r['ruta_local'] = $this->normalizarRuta($r['ruta_local'] ?? '', $codigoEmpresa, $r['codigo']);
        }
        foreach ($enDisco as $codigo => $dir) {
            if (isset($vistas[$codigo])) {
                continue;
            }
            $rows[] = [
                'codigo' => $codigo,
                'estado' => 'descargado',
                'ruta_local' => rtrim($dir, '/\\') . DIRECTORY_SEPARATOR,
                'total_admisiones' => null,
                'total_documentos' => null,
                'descargado_en' => null,
                'disponible_en' => null,
                'consumido_en' => null,
                'updated_at' => null,
                'en_disco' => true,
            ];
        }
        if ($estado !== '') {
            $rows = array_values(array_filter($rows, function ($r) use ($estado) {
                return ($r['estado'] ?? '') === $estado;
            }));
        }

        Json::response(['ok' => true, 'data' => ['lotes' => $rows, 'total' => count($rows)]]);
    }

    private function detalleLote(string $codigo): void
    {
        $codigoEmpresa = $this->companyCode();
        $fila = $this->buscarFila($codigoEmpresa, $codigo);
        $dir = $this->rutaLote($codigoEmpresa, $codigo);
        if (!$fila && !is_dir($dir)) {
            $this->jsonError(404, 'Lote no existe en disco ni en el registro local.');
        }

        $meta = [];
        $manPath = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . 'manifiesto_' . $codigo . '.json';
        if (is_file($manPath)) {
            $man = json_decode((string)@file_get_contents($manPath), true);
            if (is_array($man)) {
                $meta = [
                    'empresa' => (string)($man['empresa']['nombre'] ?? ''),
                    'fecha_hora' => (string)($man['fecha_hora'] ?? ''),
                    'total_admisiones' => (int)($man['total_admisiones'] ?? 0),
                    'total_documentos' => (int)($man['total_documentos'] ?? 0),
                    'admisiones' => array_map(function ($a) {
                        return [
                            'id' => (int)($a['id'] ?? 0),
                            'correlativo' => $a['correlativo'] ?? null,
                            'fecha' => $a['fecha'] ?? null,
                            'paciente_nombre' => $a['paciente_nombre'] ?? null,
                            'responsable_tipo' => $a['responsable_tipo'] ?? null,
                            'total_documentos' => (int)($a['total_documentos'] ?? 0),
                        ];
                    }, (array)($man['admisiones'] ?? [])),
                ];
            }
        }

        $estado = $fila['estado'] ?? ($meta ? 'descargado' : 'descargado');
        Json::response([
            'ok' => true,
            'data' => [
                'codigo' => $codigo,
                'company_code' => $codigoEmpresa,
                'estado' => $estado,
                'ruta_local' => rtrim($dir, '/\\') . DIRECTORY_SEPARATOR,
                'ruta_relativa' => 'public/uploads/empresas/' . $codigoEmpresa
                    . '/archivos/lotes_egresos/' . $codigo . '/',
                'descargado_en' => $fila['descargado_en'] ?? null,
                'disponible_en' => $fila['disponible_en'] ?? null,
                'consumido_en' => $fila['consumido_en'] ?? null,
                'manifiesto' => $meta,
            ],
        ]);
    }

    private function archivosLote(string $codigo): void
    {
        $codigoEmpresa = $this->companyCode();
        $dirLote = $this->rutaLote($codigoEmpresa, $codigo);
        if (!is_dir($dirLote)) {
            $this->jsonError(404, 'Lote no existe en disco.');
        }

        $sub = trim((string)($_GET['sub'] ?? ''));
        $ruta = $dirLote;
        if ($sub !== '') {
            $sub = str_replace('\\', '/', $sub);
            if (strpos($sub, '..') !== false || $sub[0] === '/' || !preg_match('#^[A-Za-z0-9_\-./ ]+$#', $sub)) {
                $this->jsonError(400, 'Ruta sub inválida.');
            }
            $ruta = rtrim($dirLote, '/\\') . '/' . trim($sub, '/') . '/';
        }
        $realBase = realpath($dirLote);
        $realRuta = realpath(rtrim($ruta, '/\\'));
        if ($realBase === false || $realRuta === false || strpos($realRuta, $realBase) !== 0 || !is_dir($realRuta)) {
            $this->jsonError(400, 'Ruta fuera del lote.');
        }

        $items = [];
        foreach (scandir($realRuta) ?: [] as $nombre) {
            if ($nombre === '.' || $nombre === '..') {
                continue;
            }
            $full = $realRuta . DIRECTORY_SEPARATOR . $nombre;
            $esDir = is_dir($full);
            $rel = ($sub !== '' ? trim($sub, '/') . '/' : '') . $nombre;
            $item = [
                'nombre' => $nombre,
                'tipo' => $esDir ? 'dir' : 'file',
                'ruta' => $rel,
                'ruta_local' => $full . ($esDir ? DIRECTORY_SEPARATOR : ''),
                'tam' => $esDir ? null : (int)@filesize($full),
                'mtime' => date('c', (int)@filemtime($full)),
            ];
            if ($esDir && preg_match('/^admision_(\d+)$/', $nombre, $mm)) {
                $item['admision_id'] = (int)$mm[1];
            }
            $items[] = $item;
        }
        usort($items, function ($a, $b) {
            if ($a['tipo'] !== $b['tipo']) {
                return $a['tipo'] === 'dir' ? -1 : 1;
            }
            return strcmp($a['nombre'], $b['nombre']);
        });

        Json::response([
            'ok' => true,
            'data' => [
                'codigo' => $codigo,
                'sub' => $sub,
                'ruta_local' => $realBase . DIRECTORY_SEPARATOR,
                'items' => $items,
            ],
        ]);
    }

    private function consumirLote(string $codigo): void
    {
        $codigoEmpresa = $this->companyCode();
        $fila = $this->buscarFila($codigoEmpresa, $codigo);
        $dir = $this->rutaLote($codigoEmpresa, $codigo);

        if (!$fila) {
            // Auto-registrar si está en disco (descarga previa a la migración)
            if (!is_dir($dir)) {
                $this->jsonError(404, 'Lote no existe.');
            }
            $this->upsertLote($codigoEmpresa, $codigo, 'disponible', $dir);
            $fila = $this->buscarFila($codigoEmpresa, $codigo);
        }

        $estado = (string)($fila['estado'] ?? '');
        if ($estado === 'consumido') {
            Json::response([
                'ok' => true,
                'data' => [
                    'codigo' => $codigo,
                    'estado' => 'consumido',
                    'ya_consumido' => true,
                    'consumido_en' => $fila['consumido_en'] ?? null,
                ],
            ]);
            return;
        }
        if ($estado !== 'disponible' && $estado !== 'descargado') {
            $this->jsonError(409, "Estado actual '$estado' no permite consumir. Se espera disponible.");
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            "UPDATE lotes_locales
             SET estado = 'consumido', consumido_en = :n, updated_at = :n
             WHERE company_code = :c AND codigo = :k"
        );
        $stmt->execute(['n' => $now, 'c' => $codigoEmpresa, 'k' => $codigo]);

        Json::response([
            'ok' => true,
            'data' => [
                'codigo' => $codigo,
                'estado' => 'consumido',
                'consumido_en' => $now,
            ],
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function buscarFila(string $codigoEmpresa, string $codigo): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM lotes_locales WHERE company_code = :c AND codigo = :k LIMIT 1'
        );
        $stmt->execute(['c' => $codigoEmpresa, 'k' => $codigo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function rutaLote(string $codigoEmpresa, string $codigo): string
    {
        return BASE_PATH . '/public/uploads/empresas/' . $codigoEmpresa
            . '/archivos/lotes_egresos/' . $codigo . '/';
    }

    private function normalizarRuta(string $ruta, string $codigoEmpresa, string $codigo): string
    {
        if ($ruta !== '') {
            return rtrim($ruta, '/\\') . DIRECTORY_SEPARATOR;
        }
        return $this->rutaLote($codigoEmpresa, $codigo);
    }

    private function lotesEnDisco(string $codigoEmpresa): array
    {
        $base = BASE_PATH . '/public/uploads/empresas/' . $codigoEmpresa . '/archivos/lotes_egresos';
        $out = [];
        if (!is_dir($base)) {
            return $out;
        }
        foreach (scandir($base) ?: [] as $n) {
            if ($n === '.' || $n === '..') {
                continue;
            }
            $dir = $base . '/' . $n;
            if (is_dir($dir)) {
                $out[$n] = $dir;
            }
        }
        return $out;
    }

    /** Marca un lote como disponible para el ERP (usado por LotesEgresosController). */
    public function marcarDisponible(string $codigoEmpresa, string $codigo, array $extra = []): void
    {
        $this->upsertLote($codigoEmpresa, $codigo, 'disponible', $extra['ruta_local'] ?? null, $extra);
    }

    private function upsertLote(
        string $codigoEmpresa,
        string $codigo,
        string $estado,
        ?string $rutaLocal = null,
        array $extra = []
    ): void {
        $now = date('Y-m-d H:i:s');
        if ($rutaLocal === null || $rutaLocal === '') {
            $rutaLocal = $this->rutaLote($codigoEmpresa, $codigo);
        }
        $meta = json_encode($extra['metadata'] ?? null, JSON_UNESCAPED_UNICODE);

        if ($estado === 'disponible') {
            $sql = "INSERT INTO lotes_locales
                (company_code, codigo, estado, ruta_local, total_admisiones, total_documentos, docs_ok,
                 metadata, descargado_en, disponible_en, updated_at)
                VALUES (:c, :k, :e, :r, :ta, :td, :dok, :m, :n, :n, :n)
                ON DUPLICATE KEY UPDATE
                    estado = IF(estado = 'consumido', 'consumido', VALUES(estado)),
                    ruta_local = VALUES(ruta_local),
                    total_admisiones = VALUES(total_admisiones),
                    total_documentos = VALUES(total_documentos),
                    docs_ok = VALUES(docs_ok),
                    metadata = VALUES(metadata),
                    descargado_en = COALESCE(descargado_en, VALUES(descargado_en)),
                    disponible_en = IF(estado = 'consumido', disponible_en, VALUES(disponible_en)),
                    updated_at = VALUES(updated_at)";
        } else {
            $sql = "INSERT INTO lotes_locales
                (company_code, codigo, estado, ruta_local, updated_at)
                VALUES (:c, :k, :e, :r, :n)
                ON DUPLICATE KEY UPDATE
                    estado = VALUES(estado),
                    ruta_local = VALUES(ruta_local),
                    updated_at = VALUES(updated_at)";
            $extra = [];
        }

        $stmt = $this->pdo->prepare($sql);
        $params = [
            'c' => $codigoEmpresa,
            'k' => $codigo,
            'e' => $estado,
            'r' => $rutaLocal,
            'n' => $now,
        ];
        if ($estado === 'disponible') {
            $params['ta'] = (int)($extra['total_admisiones'] ?? 0);
            $params['td'] = (int)($extra['total_documentos'] ?? 0);
            $params['dok'] = (int)($extra['docs_ok'] ?? 0);
            $params['m'] = $meta;
        }
        $stmt->execute($params);
    }

    private function jsonError(int $code, string $msg): void
    {
        Json::response(['ok' => false, 'error' => $msg], $code);
    }
}
