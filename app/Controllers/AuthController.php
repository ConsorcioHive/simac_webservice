<?php
// app/Controllers/AuthController.php
namespace App\Controllers;

use App\Models\Usuario;
use App\Models\Empresa;
use App\Helpers\Session;

class AuthController
{
    public function showLogin()
    {
        if (Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }
        $empresaModel = new Empresa();
        $empresa = $empresaModel->obtenerUnica();
        $error = $_GET['error'] ?? null;
        require APP_PATH . '/views/login.php';
    }

    public function loginProcess()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        $identificador = trim($_POST['login'] ?? '');
        $password      = $_POST['password'] ?? '';

        if ($identificador === '' || $password === '') {
            header('Location: ' . BASE_URL . 'login.php?error=1');
            exit;
        }

        $usuarioModel = new Usuario();
        $usuario      = $usuarioModel->buscarPorLoginOEmail($identificador);

        if (!$usuario || !password_verify($password, $usuario['password_hash'])
            || $usuario['estado'] !== 'activo') {
            header('Location: ' . BASE_URL . 'login.php?error=1');
            exit;
        }

        $empresaModel = new Empresa();
        $empresa      = $empresaModel->obtenerUnica();

        Session::login($usuario, $empresa ?: ['id' => $usuario['empresa_id']]);

        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }

    public function logout()
    {
        Session::logout();
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}
