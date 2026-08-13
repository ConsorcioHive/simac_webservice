<?php
// app/views/layout/header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title ?? APP_NAME) ?></title>
    <style>
        body { font-family: system-ui, Arial, sans-serif; margin: 0; background: #f4f6f8; color: #222; }
        header { background: #14532d; color: #fff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #fff; text-decoration: none; margin-left: 16px; }
        main { max-width: 960px; margin: 24px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; }
        input, select, button { padding: 8px; margin: 4px 0; width: 100%; box-sizing: border-box; }
        button { background: #14532d; color: #fff; border: 0; border-radius: 6px; cursor: pointer; }
        .menu a { margin-right: 14px; color: #14532d; text-decoration: none; font-weight: 600; }
        .error { color: #b91c1c; }
    </style>
</head>
<body>
<header>
    <strong><?= APP_NAME ?></strong>
    <nav>
        <a href="<?= BASE_URL ?>index.php">Inicio</a>
        <a href="<?= BASE_URL ?>usuarios.php">Usuarios</a>
        <a href="<?= BASE_URL ?>empresa.php">Empresa</a>
        <a href="<?= BASE_URL ?>sync.php">Sincronización</a>
        <a href="<?= BASE_URL ?>logout.php">Salir</a>
    </nav>
</header>
<main>
