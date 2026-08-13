<?php
// login.php
require __DIR__ . '/config/config.php';

use App\Controllers\AuthController;

$auth = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->loginProcess();
} else {
    $auth->showLogin();
}
