<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= Sanitizador::html($tituloPagina ?? 'Biblioteca Digital') ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body class="bg-light">
<?php if (!empty($_SESSION['usuario'])): ?>
<div class="d-flex" id="wrapper">
  <?php require_once SRC_PATH . '/views/layout/sidebar.php'; ?>
  <div id="page-content-wrapper" class="flex-grow-1">
    <?php require_once SRC_PATH . '/views/layout/navbar.php'; ?>
    <div class="container-fluid px-4 py-3">
      <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>
<?php endif; ?>
