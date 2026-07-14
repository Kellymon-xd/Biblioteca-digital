<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>/assets/icons/icon-192.png?v=2">
  <link rel="shortcut icon" type="image/png" href="<?= BASE_URL ?>/assets/icons/icon-192.png?v=2">
  <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icons/icon-192.png?v=2">
  <meta name="theme-color" content="#0f766e">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= Sanitizador::html($tituloPagina ?? 'Biblioteca Digital') ?></title>
  <link rel="manifest" href="<?= BASE_URL ?>/manifest.webmanifest">
  <meta name="theme-color" content="#0f766e">
  <meta name="theme-color" content="#0f766e" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">
  <meta name="msapplication-navbutton-color" content="#0f766e">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="Biblioteca">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icons/icon-192.png">
  <script>window.BD_BASE_URL = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES) ?>;</script>
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
