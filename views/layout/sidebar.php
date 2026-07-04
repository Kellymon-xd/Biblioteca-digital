<?php
$modActual = Sanitizador::alfanumerico($_GET['mod'] ?? '');
function menuActivo(string $mod): string {
    global $modActual;
    return $modActual === $mod ? 'active' : '';
}
?>
<nav id="sidebar" class="bg-dark text-white d-flex flex-column" style="min-width:230px;min-height:100vh;">
  <div class="p-3 border-bottom border-secondary">
    <i class="bi bi-book-half fs-4 text-warning me-2"></i>
    <span class="fw-bold fs-5">Biblioteca Digital</span>
  </div>
  <ul class="nav flex-column p-2 flex-grow-1">
    <li class="nav-item">
      <a href="<?= BASE_URL ?>?mod=dashboard" class="nav-link text-white <?= menuActivo('dashboard') ?>">
        <i class="bi bi-speedometer2 me-2"></i>Dashboard
      </a>
    </li>
    <li class="nav-item mt-2">
      <small class="text-secondary px-3">CATÁLOGO</small>
    </li>
    <li class="nav-item">
      <a href="<?= BASE_URL ?>?mod=libros" class="nav-link text-white <?= menuActivo('libros') ?>">
        <i class="bi bi-journals me-2"></i>Libros
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= BASE_URL ?>?mod=categorias" class="nav-link text-white <?= menuActivo('categorias') ?>">
        <i class="bi bi-tags me-2"></i>Categorías
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= BASE_URL ?>?mod=solicitudes" class="nav-link text-white <?= menuActivo('solicitudes') ?>">
        <i class="bi bi-envelope-plus me-2"></i>Solicitudes
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= BASE_URL ?>?mod=reservas&accion=admin" class="nav-link text-white <?= menuActivo('reservas') ?>">
        <i class="bi bi-calendar-check me-2"></i>Reservas
      </a>
    </li>
    <li class="nav-item mt-2">
      <small class="text-secondary px-3">PERSONAS</small>
    </li>
    <li class="nav-item">
      <a href="<?= BASE_URL ?>?mod=estudiantes" class="nav-link text-white <?= menuActivo('estudiantes') ?>">
        <i class="bi bi-mortarboard me-2"></i>Estudiantes
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= BASE_URL ?>?mod=carreras" class="nav-link text-white <?= menuActivo('carreras') ?>">
        <i class="bi bi-building-check me-2"></i>Carreras
      </a>
    </li>
    <?php if (isset($_SESSION['usuario']['rol']) && normalizarRol((string) $_SESSION['usuario']['rol'], (string) ($_SESSION['usuario']['username'] ?? '')) === 'administrador'): ?>
    <li class="nav-item mt-2">
      <small class="text-secondary px-3">ADMINISTRACIÓN</small>
    </li>
    <li class="nav-item">
      <a href="<?= BASE_URL ?>?mod=usuarios" class="nav-link text-white <?= menuActivo('usuarios') ?>">
        <i class="bi bi-people me-2"></i>Usuarios
      </a>
    </li>
    <?php endif; ?>
  </ul>
  <div class="p-3 border-top border-secondary">
    <form method="POST" action="<?= BASE_URL ?>?mod=auth&accion=logout">
      <?= CsrfToken::campoOculto() ?>
      <button class="btn btn-sm btn-outline-light w-100">
        <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
      </button>
    </form>
  </div>
</nav>
