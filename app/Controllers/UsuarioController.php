<?php
// app/Controllers/UsuarioController.php
namespace App\Controllers;

use App\Models\Usuario;
use App\Helpers\Session;

class UsuarioController
{
    private $model;

    public function __construct()
    {
        $this->model = new Usuario();
    }

    public function listar()
    {
        $empresaId = Session::get('empresa_id');
        return $this->model->listarPorEmpresa($empresaId);
    }

    public function store($datos)
    {
        $datos['empresa_id'] = Session::get('empresa_id');
        if (empty($datos['password'])) {
            return ['ok' => false, 'msg' => 'La contraseña es obligatoria.'];
        }
        $this->model->crear($datos);
        return ['ok' => true];
    }

    public function update($id, $datos)
    {
        $this->model->actualizar($id, $datos);
        return ['ok' => true];
    }

    public function delete($id)
    {
        $this->model->eliminar($id, Session::get('empresa_id'));
        return ['ok' => true];
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $msg = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action'])) {
            if ($_POST['_action'] === 'crear') {
                $res = $this->store([
                    'nombre'   => $_POST['nombre'] ?? '',
                    'apellido' => $_POST['apellido'] ?? '',
                    'cedula'   => $_POST['cedula'] ?? '',
                    'email'    => $_POST['email'] ?? '',
                    'login'    => $_POST['login'] ?? '',
                    'password' => $_POST['password'] ?? '',
                    'rol'      => $_POST['rol'] ?? 'operador',
                    'estado'   => $_POST['estado'] ?? 'activo',
                    'cargo'    => $_POST['cargo'] ?? '',
                    'telefono' => $_POST['telefono'] ?? '',
                ]);
                $msg = $res['ok'] ? 'Usuario creado.' : ('Error: ' . $res['msg']);
            } elseif ($_POST['_action'] === 'editar') {
                $res = $this->update((int)$_POST['id'], [
                    'nombre'   => $_POST['nombre'] ?? '',
                    'apellido' => $_POST['apellido'] ?? '',
                    'cedula'   => $_POST['cedula'] ?? '',
                    'email'    => $_POST['email'] ?? '',
                    'login'    => $_POST['login'] ?? '',
                    'password' => $_POST['password'] ?? '',
                    'rol'      => $_POST['rol'] ?? 'operador',
                    'estado'   => $_POST['estado'] ?? 'activo',
                    'cargo'    => $_POST['cargo'] ?? '',
                    'telefono' => $_POST['telefono'] ?? '',
                ]);
                $msg = $res['ok'] ? 'Usuario actualizado.' : 'Error al actualizar.';
            } elseif ($_POST['_action'] === 'eliminar') {
                $this->delete((int)$_POST['id']);
                $msg = 'Usuario eliminado.';
            }
            header('Location: index.php?page=usuarios&m=' . urlencode($msg));
            exit;
        }

        $editar = null;
        if (isset($_GET['edit'])) {
            $editar = $this->model->obtenerPorId((int)$_GET['edit']);
        }
        $usuarios = $this->listar();

        $GLOBALS['_page_title']   = 'Usuarios';
        $GLOBALS['_sidebar_current'] = 'usuarios';
        require APP_PATH . '/views/usuarios/index.php';
    }

    public function cambiarFoto()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=usuarios');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_danger'] = 'Usuario inválido.';
            header('Location: index.php?page=usuarios');
            exit;
        }

        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_danger'] = 'Error al subir la foto.';
            header('Location: index.php?page=usuarios&edit=' . $id);
            exit;
        }

        $usuario = $this->model->obtenerPorId($id);
        if (!$usuario) {
            $_SESSION['flash_danger'] = 'Usuario no encontrado.';
            header('Location: index.php?page=usuarios');
            exit;
        }

        $empresaModel = new \App\Models\Empresa();
        $empresa = $empresaModel->obtenerUnica();
        $companyCode = $empresa['company_code'] ?? 'TEMP';

        $uploadDir = BASE_PATH . '/public/uploads/usuarios/' . $companyCode . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $ext = strtolower($ext);
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $_SESSION['flash_danger'] = 'Formato no válido. Use JPG, PNG o GIF.';
            header('Location: index.php?page=usuarios&edit=' . $id);
            exit;
        }

        $filename = 'foto_' . $id . '.' . $ext;
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $filepath)) {
            $_SESSION['flash_danger'] = 'No se pudo guardar la foto.';
            header('Location: index.php?page=usuarios&edit=' . $id);
            exit;
        }

        $fotoUrl = '/public/uploads/usuarios/' . $companyCode . '/' . $filename;
        $this->model->actualizarFoto($id, $fotoUrl);

        $_SESSION['flash_success'] = 'Foto de perfil actualizada.';
        header('Location: index.php?page=usuarios&edit=' . $id);
        exit;
    }
}