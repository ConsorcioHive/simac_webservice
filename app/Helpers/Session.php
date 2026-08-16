<?php
// app/Helpers/Session.php
namespace App\Helpers;

class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(array $usuario, array $empresa)
    {
        self::start();
        session_unset();
        $_SESSION['usuario_id']      = $usuario['id'];
        $_SESSION['usuario_nombre']  = $usuario['nombre'];
        $_SESSION['usuario_rol']     = $usuario['rol'] ?? 'operador';
        $_SESSION['empresa_id']      = $empresa['id'];
        $_SESSION['empresa_nombre']  = $empresa['nombre'] ?? $empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? '';
        $_SESSION['empresa_codigo']  = $empresa['company_code'] ?? $empresa['codigo_clinica'] ?? null;
    }

    public static function isLoggedIn()
    {
        self::start();
        return !empty($_SESSION['usuario_id']);
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'index.php?page=login');
            exit;
        }
    }

    public static function get($key)
    {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    public static function logout()
    {
        self::start();
        session_unset();
        session_destroy();
    }
}
