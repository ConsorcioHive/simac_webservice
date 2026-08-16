<?php
// app/Controllers/EmpresaController.php
// Clonado de SIMAC (app/Controllers/EmpresaController.php) adaptado al sistema local
// single-tenant: form() edita la única empresa (sin crear lista nueva).
namespace App\Controllers;

use App\Models\Empresa;
use App\Models\Moneda;

class EmpresaController
{
    public function form()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $empresaModel = new Empresa();
        $empresa = $empresaModel->obtenerUnica();
        if (!$empresa) {
            $_SESSION['flash_danger'] = 'No hay empresa configurada.';
            header('Location: index.php?page=dashboard');
            exit;
        }
        $id = (int)$empresa['id'];

        // Usuarios de esta empresa (para el contacto principal)
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.id AS usuario_id, u.nombre, u.email
            FROM usuarios u
            WHERE u.empresa_id = :company_id
            ORDER BY u.nombre
        ");
        $stmt->execute(['company_id' => $id]);
        $usuariosEmpresa = $stmt->fetchAll();

        // Países para el Step 2 (Ubicación)
        $stmt = $pdo->query("
            SELECT DISTINCT codigo_pais, 'Venezuela' as nombre
            FROM pais_edo_ciudad
            WHERE codigo_pais IS NOT NULL
            ORDER BY codigo_pais
        ");
        $paises = $stmt->fetchAll();

        // Monedas disponibles para Step 2
        $stmt = $pdo->query("
            SELECT id, codigo, nombre, simbolo, factor_conversion
            FROM monedas
            WHERE activa = TRUE
            ORDER BY es_moneda_base DESC, nombre ASC
        ");
        $monedas = $stmt->fetchAll();

        $monedaModel = new Moneda();
        $monedaBase = $monedaModel->obtenerMonedaBase();
        $todasMonedas = $monedaModel->listarTodas();
        $esSuperAdmin = (isset($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] === 1);

        // Redes sociales (config compartida con SIMAC, copiada local)
        $socialNetworks = [];
        $socialNetworksFile = APP_PATH . '/../config/social_networks.json';
        if (file_exists($socialNetworksFile)) {
            $decoded = json_decode(file_get_contents($socialNetworksFile), true);
            $socialNetworks = $decoded['redes'] ?? [];
        }

        $page_title = 'Editar empresa';
        $GLOBALS['_page_title'] = 'Editar empresa';
        $GLOBALS['_sidebar_current'] = 'empresa';

        require APP_PATH . '/views/empresas/form.php';
    }

    public function guardar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $model = new Empresa();
        $pdo   = \App\Core\Database::getConnection();

        // En single-tenant siempre editamos la única empresa existente.
        $empresa = $model->obtenerUnica();
        if (!$empresa) {
            $_SESSION['flash_danger'] = 'No hay empresa configurada.';
            header('Location: index.php?page=dashboard');
            exit;
        }
        $id = (int)$empresa['id'];

        // 1) Datos base del formulario
        $data = $this->buildBaseDataFromRequest();

        // 2) Validaciones básicas
        $this->validateNombreYEmail($data, $id);
        $this->validateTerminosYPrivacidad($id);

        // 3) Ubicación (país/estado/muni/moneda, etc.)
        $this->buildUbicacionData($pdo, $data, $id);

        // 4) Preferencias (idioma, timezone, notificaciones, checks)
        $this->buildPreferenciasData($data);

        // 4b) Administración (serial_licencia, identificador_saint, fecha_expiracion)
        $this->buildAdministracionData($data);

        // 5) Valores actuales de archivos (por si no se sube nada nuevo)
        $data['logo_url']              = $_POST['logo_url_actual']              ?? null;
        $data['business_card_url']     = $_POST['business_card_url_actual']     ?? null;
        $data['empresa_documento_url'] = $_POST['empresa_documento_url_actual'] ?? null;
        $data['empresa_caratula_url']  = $_POST['empresa_caratula_url_actual']  ?? null;

        // 6) Procesar uploads (usa company_code dentro y sobreescribe URLs si hay archivo nuevo)
        $this->processArchivos($data);

        // 6b) Moneda de pago (solo superadmin)
        $esSuperAdmin = (isset($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] === 1);
        if ($esSuperAdmin && isset($_POST['moneda_pago_id'])) {
            $data['moneda_pago_id'] = (int)$_POST['moneda_pago_id'] ?: null;
        }

        // 6c) Cambio de moneda base (solo superadmin, system-wide)
        if ($esSuperAdmin && isset($_POST['moneda_base_id'])) {
            $nuevaBaseId = (int)$_POST['moneda_base_id'];
            if ($nuevaBaseId > 0) {
                $monedaModel = new Moneda();
                $monedaModel->actualizarBase($nuevaBaseId);
            }
        }

        if ($data['company_code'] === '') {
            $this->flashAndRedirect('El código de empresa es obligatorio.', $id);
        }

        if ($model->existeCompanyCode($data['company_code'], $id)) {
            $this->flashAndRedirect('El código de empresa ya está en uso. Debe ser único.', $id);
        }

        // 7) Guardar en BD (siempre update en single-tenant)
        $model->actualizar($id, $data);

        // 8) Redirigir al formulario de la empresa
        $_SESSION['flash_success'] = 'Empresa actualizada correctamente.';
        header('Location: index.php?page=empresas_form');
        exit;
    }

    // --------- Helpers privados para guardar() ---------

    private function buildBaseDataFromRequest(): array
    {
        return [
            'company_code'          => trim($_POST['company_code'] ?? ''),
            'tipo'                  => isset($_POST['tipo']) ? (int)$_POST['tipo'] : 0,
            'nombre'                => trim($_POST['nombre'] ?? ''),
            'slogan'                => trim($_POST['slogan'] ?? ''),
            'rif'                   => trim($_POST['rif'] ?? ''),
            'direccion_fiscal'      => trim($_POST['direccion_fiscal'] ?? ''),
            'telefono'              => trim($_POST['telefono'] ?? ''),
            'email'                 => trim($_POST['email'] ?? ''),
            'latitud'               => trim($_POST['latitud'] ?? ''),
            'longitud'              => trim($_POST['longitud'] ?? ''),
            'empresa_description'   => trim($_POST['empresa_description'] ?? ''),
            'redes_id'              => trim($_POST['redes_id'] ?? ''),
            'redes'                 => trim($_POST['input_redes'] ?? ''),
            'redes_url'             => trim($_POST['input_redes_url'] ?? ''),
        ];
    }

    private function validateNombreYEmail(array $data, ?int $id): void
    {
        if (empty($data['nombre'])) {
            $this->flashAndRedirect('El nombre de la empresa es obligatorio.', $id);
        }
        if (empty($data['email'])) {
            $this->flashAndRedirect('El email de la empresa es obligatorio.', $id);
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flashAndRedirect('El email no tiene un formato válido.', $id);
        }
    }

    private function validateTerminosYPrivacidad(?int $id): void
    {
        $aceptaTerminos   = isset($_POST['acepta_terminos']) ? 1 : 0;
        $aceptaPrivacidad = isset($_POST['acepta_privacidad']) ? 1 : 0;

        if (!$aceptaTerminos || !$aceptaPrivacidad) {
            $this->flashAndRedirect(
                'Debes aceptar los términos y la política de privacidad.',
                $id
            );
        }
    }

    private function buildUbicacionData($pdo, array &$data, ?int $id): void
    {
        if (empty($_POST['codigo_pais'])) {
            return;
        }

        if (empty($_POST['codigo_estado']) || empty($_POST['codigo_municipio'])) {
            $this->flashAndRedirect('Selecciona país, estado y municipio.', $id);
        }

        $parroquia = !empty($_POST['codigo_parroquia']) ? $_POST['codigo_parroquia'] : null;
        $stmt = $pdo->prepare("
            SELECT codigo_postal, moneda_codigo, moneda_nombre, moneda_simbolo
            FROM pais_edo_ciudad
            WHERE codigo_pais = ? AND codigo_estado = ? AND codigo_municipio = ?
              AND (codigo_parroquia = ? OR ? IS NULL)
            LIMIT 1
        ");
        $stmt->execute([
            $_POST['codigo_pais'],
            $_POST['codigo_estado'],
            $_POST['codigo_municipio'],
            $parroquia,
            $parroquia,
        ]);
        $ubicacion = $stmt->fetch();

        if (!$ubicacion) {
            $this->flashAndRedirect('Combinación de ubicación inválida.', $id);
        }

        $data['usuario_contacto_id'] = !empty($_POST['usuario_contacto_id'])
            ? (int)$_POST['usuario_contacto_id']
            : null;

        $data['codigo_pais']      = $_POST['codigo_pais'];
        $data['codigo_estado']    = $_POST['codigo_estado'];
        $data['codigo_municipio'] = $_POST['codigo_municipio'];
        $data['codigo_parroquia'] = $parroquia;
        $data['codigo_postal']    = $_POST['codigo_postal'] ?? $ubicacion['codigo_postal'];

        $data['moneda_codigo']  = $_POST['moneda_codigo'] ?? $ubicacion['moneda_codigo'];
        $data['moneda_nombre']  = $ubicacion['moneda_nombre'];
        $data['moneda_simbolo'] = $ubicacion['moneda_simbolo'];
    }

    private function buildPreferenciasData(array &$data): void
    {
        $data['idioma']            = trim($_POST['idioma'] ?? 'es');
        $data['timezone']          = trim($_POST['timezone'] ?? 'America/Caracas');
        $data['notif_email']       = isset($_POST['notif_email']) ? 1 : 0;
        $data['notif_sms']         = isset($_POST['notif_sms']) ? 1 : 0;
        $data['acepta_terminos']   = isset($_POST['acepta_terminos']) ? 1 : 0;
        $data['acepta_privacidad'] = isset($_POST['acepta_privacidad']) ? 1 : 0;
    }

    private function buildAdministracionData(array &$data): void
    {
        $data['serial_licencia']     = trim($_POST['serial_licencia'] ?? '') ?: null;
        $data['identificador_saint'] = trim($_POST['identificador_saint'] ?? '') ?: null;
        $data['fecha_expiracion']    = trim($_POST['fecha_expiracion'] ?? '') ?: null;

        // Configuración de sincronización con SIMAC cloud (por clínica)
        $data['simac_cloud_url'] = rtrim(trim($_POST['simac_cloud_url'] ?? ''), '/') ?: null;
        $data['simac_api_token'] = trim($_POST['simac_api_token'] ?? '') ?: null;
        // Ruta local donde el sistema externo deposita los JSON de control de parámetros
        $data['ruta_control_parametros'] = trim($_POST['ruta_control_parametros'] ?? '') ?: null;

        $entityFolders = $_POST['entity_folders_json'] ?? null;
        if ($entityFolders) {
            $decoded = json_decode($entityFolders, true);
            if (is_array($decoded)) {
                $data['entity_folders'] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    private function processArchivos(array &$data): void
    {
        // 1) Código de esta empresa
        $companyCode = $data['company_code'] ?? 'sin_codigo';

        // 2) Carpeta base física
        $baseDir = BASE_PATH . '/public/uploads/empresas/' . $companyCode . '/';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        // 3) Mantener valores actuales si no se sube nada nuevo
        $data['logo_url']              = $_POST['logo_url_actual']              ?? ($data['logo_url']              ?? null);
        $data['business_card_url']     = $_POST['business_card_url_actual']     ?? ($data['business_card_url']     ?? null);
        $data['empresa_documento_url'] = $_POST['empresa_documento_url_actual'] ?? ($data['empresa_documento_url'] ?? null);
        $data['empresa_caratula_url']  = $_POST['empresa_caratula_url_actual']  ?? ($data['empresa_caratula_url']  ?? null);

        // 4) Procesar LOGO
        $this->processSingleUpload(
            'logo',
            ['jpg', 'jpeg', 'png', 'gif', 'svg'],
            5 * 1024 * 1024,
            $baseDir,
            'logo',
            'logo_url',
            $companyCode,
            $data
        );

        // 5) TARJETA DE PRESENTACIÓN
        $this->processSingleUpload(
            'tarjeta_presentacion',
            ['jpg', 'jpeg', 'png', 'gif', 'svg', 'pdf'],
            5 * 1024 * 1024,
            $baseDir,
            'business_card',
            'business_card_url',
            $companyCode,
            $data
        );

        // 6) DOCUMENTO PRINCIPAL (PDF)
        $this->processSingleUpload(
            'empresa_documento',
            ['pdf'],
            10 * 1024 * 1024,
            $baseDir,
            'documento_principal',
            'empresa_documento_url',
            $companyCode,
            $data,
            null,
            'pdf'
        );

        // 7) CARÁTULA
        $this->processSingleUpload(
            'empresa_caratula',
            ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
            5 * 1024 * 1024,
            $baseDir,
            'caratula_documento',
            'empresa_caratula_url',
            $companyCode,
            $data
        );
    }

    private function processSingleUpload(
        string $fieldName,
        array $allowedExts,
        int $maxSizeBytes,
        string $baseDir,
        string $targetNameNoExt,
        string $relativeFieldKey,
        string $companyCode,
        array &$data,
        ?string $customErrorType = null,
        ?string $forceExt = null
    ): void {
        if (empty($_FILES[$fieldName]['name'])) {
            return;
        }

        $originalName = $_FILES[$fieldName]['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($forceExt !== null) {
            $ext = $forceExt;
        }

        if (!in_array($ext, $allowedExts, true)) {
            return;
        }

        if ($_FILES[$fieldName]['size'] > $maxSizeBytes) {
            return;
        }

        // Nombre fijo por empresa
        $nombreFinal = $targetNameNoExt . '.' . $ext;
        $rutaFs      = rtrim($baseDir, '/') . '/' . $nombreFinal;

        if (file_exists($rutaFs)) {
            @unlink($rutaFs);
        }

        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $rutaFs)) {
            $data[$relativeFieldKey] = '/public/uploads/empresas/' . $companyCode . '/' . $nombreFinal;
        }
    }

    private function flashAndRedirect(string $message, ?int $id): void
    {
        $_SESSION['flash_danger'] = $message;
        header('Location: index.php?page=empresas_form' . ($id ? '&id=' . $id : ''));
        exit;
    }

    public function crearCarpetaEntidad(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id']) || (int)$_SESSION['usuario_id'] !== 1) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }

        $companyId = (int)($_POST['company_id'] ?? 0);

        if ($companyId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Faltan parámetros']);
            exit;
        }

        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM empresas WHERE id = :id");
        $stmt->execute(['id' => $companyId]);
        $empresa = $stmt->fetch();
        if (!$empresa) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Empresa no encontrada']);
            exit;
        }

        $companyCode = $empresa['company_code'];
        $folderPath = BASE_PATH . '/public/uploads/empresas/' . $companyCode . '/archivos/facturas_pendientes_medico';

        if (!is_dir($folderPath)) {
            if (!mkdir($folderPath, 0755, true)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'No se pudo crear la carpeta']);
                exit;
            }
        }

        $pathRel = '/public/uploads/empresas/' . $companyCode . '/archivos/facturas_pendientes_medico/';
        $data = ['path' => $pathRel, 'created_at' => date('Y-m-d H:i:s')];
        $pdo->prepare("UPDATE empresas SET entity_folders = :ef, updated_at = NOW() WHERE id = :id")
            ->execute(['ef' => json_encode($data, JSON_UNESCAPED_UNICODE), 'id' => $companyId]);

        echo json_encode(['success' => true, 'path' => $pathRel]);
        exit;
    }

    public function contenedor()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            die('Empresa no válida');
        }

        $model = new Empresa();
        $companyFilesModel = new \App\Models\CompanyFiles();

        $empresa = $model->obtenerPorId($id);
        if (!$empresa) {
            die('Empresa no encontrada');
        }

        $companyCode = $empresa['company_code'];
        $currentFolderId = isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;
        $currentFolder = null;

        if ($currentFolderId) {
            $currentFolder = $companyFilesModel->obtenerPorId($currentFolderId);
            if (!$currentFolder || (int)$currentFolder['company_id'] !== (int)$empresa['id'] || !(int)$currentFolder['is_folder']) {
                $currentFolderId = null;
                $currentFolder = null;
            }
        }

        $baseDirContenedor = BASE_PATH . '/public/uploads/empresas/' . $companyCode . '/archivos/';
        if (!is_dir($baseDirContenedor)) {
            mkdir($baseDirContenedor, 0755, true);
        }

        if ($currentFolder && !empty($currentFolder['path_relativo'])) {
            $currentDirFs = BASE_PATH . $currentFolder['path_relativo'];
        } else {
            $currentDirFs = $baseDirContenedor;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'crear_carpeta') {
            $nombreCarpeta = trim($_POST['folder_name'] ?? '');
            if ($nombreCarpeta !== '') {
                $folderDirFs = rtrim($currentDirFs, '/') . '/' . $nombreCarpeta;
                if (!is_dir($folderDirFs)) {
                    mkdir($folderDirFs, 0755, true);
                }
                $pathRel = str_replace(BASE_PATH, '', $folderDirFs) . '/';
                $companyFilesModel->crearArchivo([
                    'company_id'    => (int)$empresa['id'],
                    'company_code'  => $companyCode,
                    'parent_id'     => $currentFolderId,
                    'is_folder'     => 1,
                    'nombre'        => $nombreCarpeta,
                    'path_relativo' => rtrim($pathRel, '/') . '/',
                    'extension'     => null,
                    'mime_type'     => null,
                    'size_bytes'    => null,
                    'subido_por'    => $_SESSION['usuario_id'] ?? null,
                ]);
            }
            header('Location: index.php?page=empresas_contenedor&id=' . (int)$empresa['id'] . ($currentFolderId ? '&folder_id=' . $currentFolderId : ''));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'subir_archivos') {
            if (!empty($_FILES['files']['name']) && is_array($_FILES['files']['name'])) {
                $total = count($_FILES['files']['name']);
                for ($i = 0; $i < $total; $i++) {
                    if (empty($_FILES['files']['name'][$i]) || $_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $tmpName = $_FILES['files']['tmp_name'][$i];
                    $nombre = basename($_FILES['files']['name'][$i]);
                    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                    $mime = $_FILES['files']['type'][$i];
                    $size = $_FILES['files']['size'][$i];
                    $filePath = rtrim($currentDirFs, '/') . '/' . $nombre;
                    if (move_uploaded_file($tmpName, $filePath)) {
                        $pathRel = str_replace(BASE_PATH, '', $filePath);
                        $companyFilesModel->crearArchivo([
                            'company_id'    => (int)$empresa['id'],
                            'company_code'  => $companyCode,
                            'parent_id'     => $currentFolderId,
                            'is_folder'     => 0,
                            'nombre'        => $nombre,
                            'path_relativo' => $pathRel,
                            'extension'     => $ext,
                            'mime_type'     => $mime,
                            'size_bytes'    => $size,
                            'subido_por'    => $_SESSION['usuario_id'] ?? null,
                        ]);
                    }
                }
            }
            header('Location: index.php?page=empresas_contenedor&id=' . (int)$empresa['id'] . ($currentFolderId ? '&folder_id=' . $currentFolderId : ''));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'eliminar') {
            $fileId = (int)($_POST['file_id'] ?? 0);
            $file = $companyFilesModel->obtenerPorId($fileId);
            if ($file && (int)$file['company_id'] === (int)$empresa['id']) {
                if ($file['is_folder']) {
                    $this->eliminarCarpetaRecursiva($file['path_relativo']);
                } else {
                    if (file_exists(BASE_PATH . $file['path_relativo'])) {
                        unlink(BASE_PATH . $file['path_relativo']);
                    }
                }
                $companyFilesModel->eliminar($fileId);
            }
            header('Location: index.php?page=empresas_contenedor&id=' . (int)$empresa['id'] . ($currentFolderId ? '&folder_id=' . $currentFolderId : ''));
            exit;
        }

        if (isset($_GET['action']) && $_GET['action'] === 'descargar' && isset($_GET['file_id'])) {
            $fileId = (int)$_GET['file_id'];
            $file = $companyFilesModel->obtenerPorId($fileId);
            if ($file && !$file['is_folder'] && (int)$file['company_id'] === (int)$empresa['id']) {
                $filePath = BASE_PATH . $file['path_relativo'];
                if (file_exists($filePath)) {
                    header('Content-Type: ' . ($file['mime_type'] ?: 'application/octet-stream'));
                    header('Content-Disposition: attachment; filename="' . basename($file['nombre']) . '"');
                    readfile($filePath);
                    exit;
                }
            }
        }

        $carpetas = [];
        $archivos = [];
        if ($currentFolderId) {
            $todos = $companyFilesModel->listarPorCarpeta($empresa['id'], $currentFolderId);
        } else {
            $todos = $companyFilesModel->listarPorCarpeta($empresa['id'], null);
        }
        foreach ($todos as $item) {
            if ($item['is_folder']) {
                $carpetas[] = $item;
            } else {
                $archivos[] = $item;
            }
        }

        $rutaNavegacion = [];
        if ($currentFolderId) {
            $fid = $currentFolderId;
            while ($fid) {
                $f = $companyFilesModel->obtenerPorId($fid);
                if (!$f) break;
                array_unshift($rutaNavegacion, $f);
                $fid = $f['parent_id'];
            }
        }

        $GLOBALS['_page_title'] = 'Contenedor de archivos';
        $GLOBALS['_sidebar_current'] = 'empresa';
        require APP_PATH . '/views/empresas/contenedor.php';
    }

    private function eliminarCarpetaRecursiva($pathRel)
    {
        $path = BASE_PATH . $pathRel;
        if (!is_dir($path)) return;
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $path . '/' . $file;
            if (is_dir($fullPath)) {
                $this->eliminarCarpetaRecursiva($pathRel . '/' . $file);
            } else {
                unlink($fullPath);
            }
        }
        rmdir($path);
    }
}