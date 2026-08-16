<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$bodyClasses = '';

// Modo oscuro según preferencias de usuario
if (!empty($_SESSION['user_preferences']['dark_mode']) &&
    (int)$_SESSION['user_preferences']['dark_mode'] === 1) {
    $bodyClasses .= ' dark-only';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Cuba admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
  <meta name="keywords" content="admin template, Cuba admin template, dashboard template, flat admin template, responsive admin template, web app">
  <meta name="author" content="pixelstrap">
  <link rel="icon" href="<?= BASE_URL ?>assets/images/simac-favicon.png" type="image/x-icon">
  <link rel="shortcut icon" href="<?= BASE_URL ?>assets/images/simac-favicon.png" type="image/x-icon">
  <title><?= $page_title ?? 'Simac - Dashboard'; ?></title>
  <?php include BASE_PATH . '/partial/style.php'; ?>
  <link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>assets/css/custom.css?v=<?= time() ?>">
  
  <?php if (!empty($page_css) && is_array($page_css)): ?>
    <?php foreach ($page_css as $css): ?>
      <link rel="stylesheet" type="text/css" href="<?= BASE_URL . $css ?>">
    <?php endforeach; ?>
  <?php endif; ?>
</head>
<body class="<?= trim($bodyClasses) ?>">
