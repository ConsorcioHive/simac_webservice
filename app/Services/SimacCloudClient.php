<?php
// app/Services/SimacCloudClient.php
namespace App\Services;

use App\Helpers\Json;

class SimacCloudClient
{
    private $baseUrl;
    private $token;
    private $stub;
    private $stubDir;

    public function __construct(
        $baseUrl = null,
        $token = null,
        $stub = null
    ) {
        // Por defecto usa los parámetros globales; se sobreescriben con los
        // valores configurados por clínica en la pestaña "Parámetros de conexión".
        $this->baseUrl = rtrim($baseUrl ?: SIMAC_CLOUD_URL, '/');
        $this->token   = $token ?: SIMAC_API_TOKEN;
        // Modo real automático: si hay token configurado, se desactiva el stub.
        $this->stub    = $stub ?? (empty($this->token) ? SIMAC_API_STUB : false);
        $this->stubDir = BASE_PATH . '/storage/sync';
        if (!is_dir($this->stubDir)) {
            @mkdir($this->stubDir, 0755, true);
        }
    }

    public function post($endpoint, $data)
    {
        return $this->request('POST', $endpoint, $data);
    }

    public function get($endpoint, $params = [])
    {
        return $this->request('GET', $endpoint, $params);
    }

    public function token()
    {
        return $this->token;
    }

    // Envío multipart de archivos con manifiesto (para control_parametros).
    public function postMultipart($endpoint, $archivos, $manifiesto)
    {
        if ($this->stub) {
            file_put_contents(
                $this->stubDir . '/stub_last_multipart.json',
                Json::encode(['endpoint' => $endpoint, 'archivos' => array_keys($archivos), 'manifiesto' => $manifiesto])
            );
            return [
                'ok'      => true,
                'stub'    => true,
                'message' => 'Simulado: multipart guardado en storage/sync/stub_last_multipart.json',
            ];
        }

        $url = $this->baseUrl . $endpoint;
        $tmpManifiesto = $this->stubDir . '/tmp_manifest.json';
        file_put_contents($tmpManifiesto, Json::encode($manifiesto));

        $post = [
            'manifest' => new \CURLFile($tmpManifiesto, 'application/json', 'manifest.json'),
        ];
        foreach ($archivos as $campo => $ruta) {
            $post[$campo] = new \CURLFile($ruta);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $this->token],
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_TIMEOUT        => 60,
        ]);

        $resp     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);
        @unlink($tmpManifiesto);

        if ($err) {
            return ['ok' => false, 'message' => 'Error de conexión: ' . $err];
        }
        $decoded = json_decode($resp, true);
        return [
            'ok'      => $httpCode >= 200 && $httpCode < 300 && !empty($decoded['ok']),
            'http'    => $httpCode,
            'message' => $decoded['mensaje'] ?? ($decoded['error'] ?? ('HTTP ' . $httpCode)),
            'data'    => $decoded,
        ];
    }

    private function request($method, $endpoint, $data)
    {
        if ($this->stub) {
            return $this->stubRequest($method, $endpoint, $data);
        }

        $url = $this->baseUrl . $endpoint;
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER    => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
            CURLOPT_POSTFIELDS    => ($method === 'POST') ? Json::encode($data) : null,
            CURLOPT_TIMEOUT       => 30,
        ]);

        $resp    = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err     = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['ok' => false, 'error' => $err];
        }
        $decoded = json_decode($resp, true);
        return [
            'ok'   => $httpCode >= 200 && $httpCode < 300,
            'http' => $httpCode,
            'data' => $decoded,
        ];
    }

    // Modo stub: simula la nube escribiendo/leyendo archivos locales.
    private function stubRequest($method, $endpoint, $data)
    {
        if ($method === 'POST') {
            $payload = [
                'endpoint' => $endpoint,
                'data'     => $data,
                'at'       => date('c'),
            ];
            file_put_contents(
                $this->stubDir . '/stub_last_upload.json',
                Json::encode($payload)
            );
            return [
                'ok'      => true,
                'stub'    => true,
                'message' => 'Simulado: payload guardado en storage/sync/stub_last_upload.json',
            ];
        }

        $file = $this->stubDir . '/stub_last_download.json';
        $data = file_exists($file)
            ? json_decode(file_get_contents($file), true)
            : ['sample' => 'datos de ejemplo desde la nube', 'at' => date('c')];
        return ['ok' => true, 'stub' => true, 'data' => $data];
    }
}
