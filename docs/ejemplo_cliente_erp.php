<?php
/**
 * ejemplo_cliente_erp.php — Cliente de ejemplo para consumir la API v1 del webservice.
 *
 * Copiar este archivo al ERP y ajustar $BASE y $KEY.
 * No requiere sesión de usuario; solo la API key en el header X-API-Key.
 *
 * Uso desde línea de comandos:
 *   php ejemplo_cliente_erp.php           → ejecuta el flujo completo (lista, detalle, archivos, consumir)
 *   php ejemplo_cliente_erp.php --dry-run → no marca consumir (solo consulta)
 *
 * Documentación completa: docs/api_v1.md
 */

// ── Configuración ──────────────────────────────────────────────────────────
$BASE = 'http://localhost/simac_webservice/api/v1'; // URL base de la API
$KEY  = 'erp_1013_1c4e76d7b8ff65a8bdb086181a1bcd2a7e51c922'; // API key del tenant
$DRY  = in_array('--dry-run', $argv ?? [], true);

// ── Helper genérico cURL ───────────────────────────────────────────────────
function api(string $method, string $path, string $base, string $key): array
{
    $ch = curl_init(rtrim($base, '/') . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => [
            'X-API-Key: ' . $key,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        return ['ok' => false, 'error' => 'cURL: ' . $err, '_http' => 0];
    }
    $json = json_decode((string)$body, true);
    if (!is_array($json)) {
        return ['ok' => false, 'error' => 'Respuesta no JSON (HTTP ' . $code . ')', 'raw' => $body, '_http' => $code];
    }
    $json['_http'] = $code;
    return $json;
}

// ── 1) Salud: verificar conexión + key ────────────────────────────────────
echo "==> GET /salud\n";
$salud = api('GET', '/salud', $BASE, $KEY);
if (empty($salud['ok'])) {
    fwrite(STDERR, 'API no disponible: ' . ($salud['error'] ?? '?') . "\n");
    exit(1);
}
echo '    Tenant: ' . ($salud['data']['empresa'] ?? '?') . ' | ' . ($salud['data']['time'] ?? '') . "\n\n";

// ── 2) Listar lotes pendientes ────────────────────────────────────────────
echo "==> GET /lotes?estado=disponible\n";
$lista = api('GET', '/lotes?estado=disponible', $BASE, $KEY);
if (empty($lista['ok'])) {
    fwrite(STDERR, 'Error listando lotes: ' . ($lista['error'] ?? '?') . "\n");
    exit(1);
}
$lotes = $lista['data']['lotes'] ?? [];
$total = $lista['data']['total'] ?? count($lotes);
echo "    $total lote(s) disponible(s)\n\n";

if (empty($lotes)) {
    echo "No hay lotes pendientes. Nada que procesar.\n";
    exit(0);
}

// ── 3) Por cada lote: detalle → archivos → consumir ───────────────────────
foreach ($lotes as $l) {
    $codigo = (string)$l['codigo'];
    echo "── Lote: $codigo ──\n";
    echo '    Estado: ' . ($l['estado'] ?? '?') . ' | Admisiones: ' . ($l['total_admisiones'] ?? '?')
        . ' | Documentos: ' . ($l['total_documentos'] ?? '?') . "\n";
    echo '    Ruta local: ' . ($l['ruta_local'] ?? '?') . "\n";

    // 3a) Detalle (manifiesto)
    $detalle = api('GET', '/lotes/' . $codigo, $BASE, $KEY);
    if (!empty($detalle['ok'])) {
        $man = $detalle['data']['manifiesto'] ?? [];
        echo '    Empresa: ' . ($man['empresa'] ?? '?') . ' | Fecha: ' . ($man['fecha_hora'] ?? '?') . "\n";
        foreach (($man['admisiones'] ?? []) as $a) {
            echo '      - Admisión id=' . ($a['id'] ?? '?')
                . ' | Correlativo #' . ($a['correlativo'] ?? '?')
                . ' | Paciente: ' . ($a['paciente_nombre'] ?? '(sin dato)')
                . ' | Docs: ' . ($a['total_documentos'] ?? 0) . "\n";
        }
    } else {
        echo '    (detalle falló: ' . ($detalle['error'] ?? '?') . ")\n";
    }

    // 3b) Archivos
    $archivos = api('GET', '/lotes/' . $codigo . '/archivos', $BASE, $KEY);
    if (!empty($archivos['ok'])) {
        foreach (($archivos['data']['items'] ?? []) as $it) {
            $tipo = $it['tipo'] === 'dir' ? '[DIR ]' : '[FILE]';
            $tam  = $it['tam'] !== null ? str_pad(number_format((int)$it['tam']) . ' B', 14, ' ', STR_PAD_LEFT) : '';
            echo "      $tipo " . $it['ruta'] . " $tam\n";
            // $it['ruta_local'] = ruta absoluta para copiar/leer el archivo
        }
    } else {
        echo '    (archivos falló: ' . ($archivos['error'] ?? '?') . ")\n";
    }

    // 3c) Consumir (marcar como procesado)
    if ($DRY) {
        echo "    [dry-run] no se marca consumir\n";
    } else {
        $consumir = api('POST', '/lotes/' . $codigo . '/consumir', $BASE, $KEY);
        if (!empty($consumir['ok'])) {
            $d = $consumir['data'];
            $extra = !empty($d['ya_consumido']) ? ' (ya estaba consumido)' : '';
            echo '    Consumir → ' . ($d['estado'] ?? '?') . $extra
                . ' | ' . ($d['consumido_en'] ?? '') . "\n";
        } else {
            echo '    Consumir falló: ' . ($consumir['error'] ?? '?') . "\n";
        }
    }
    echo "\n";
}

echo "Proceso finalizado.\n";
