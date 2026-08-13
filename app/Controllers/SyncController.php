<?php
// app/Controllers/SyncController.php
namespace App\Controllers;

use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\SyncLog;
use App\Services\SimacCloudClient;
use App\Services\FileDropService;
use App\Helpers\Session;

class SyncController
{
    private $empresa;
    private $usuario;
    private $syncLog;
    private $client;
    private $drop;

    public function __construct()
    {
        $this->empresa = new Empresa();
        $this->usuario = new Usuario();
        $this->syncLog = new SyncLog();
        $this->client  = new SimacCloudClient();
        $this->drop    = new FileDropService();
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
}
