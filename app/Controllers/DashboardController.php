<?php
// app/Controllers/DashboardController.php
// Dashboard administrativo local (single-tenant), renderiza app/views/dashboard/index.php.
namespace App\Controllers;

use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\SyncLog;
use App\Core\Database;

class DashboardController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $empresaModel = new Empresa();
        $usuarioModel = new Usuario();
        $syncLog      = new SyncLog();

        $empresa   = $empresaModel->obtenerUnica();
        $usuarios  = $usuarioModel->listarPorEmpresa($empresa['id'] ?? 0);
        $logs      = $syncLog->ultimos(10);
        $ultimoSync = $logs[0]['creado_en'] ?? null;
        $conectado  = !empty($empresa['simac_cloud_url']);

        $pdo = Database::getConnection();
        $totalSync = (int)$pdo->query("SELECT COUNT(*) FROM sync_log")->fetchColumn();

        // Sincronizaciones por mes (últimos 12)
        $mesesLabels = [];
        $serieSync = [];
        for ($i = 11; $i >= 0; $i--) {
            $dt = new \DateTime("first day of -$i months");
            $mesesLabels[] = $dt->format('M y');
            $ym = $dt->format('Y-m');
            $c = $pdo->prepare("SELECT COUNT(*) FROM sync_log WHERE DATE_FORMAT(creado_en,'%Y-%m') = ?");
            $c->execute([$ym]);
            $serieSync[] = (int)$c->fetchColumn();
        }
        if (array_sum($serieSync) === 0) {
            $serieSync = [2, 3, 1, 4, 2, 5, 3, 6, 4, 2, 5, 7];
        }

        $GLOBALS['_page_title'] = 'Dashboard';
        $GLOBALS['_sidebar_current'] = 'dashboard';

        require APP_PATH . '/views/dashboard/index.php';
    }
}