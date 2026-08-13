<?php
// logout.php
require __DIR__ . '/config/config.php';

use App\Controllers\AuthController;

(new AuthController())->logout();
