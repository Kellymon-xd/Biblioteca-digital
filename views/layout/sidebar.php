<?php
$modActual = Sanitizador::alfanumerico($_GET['mod'] ?? '');
function menuActivo(string $mod): string {
    global $modActual;
    return $modActual === $mod ? 'active' : '';
}
function mostrarMenu(string $mod): bool {
    return usuarioPuede($mod);
}
?>
<nav id="sidebar" class="bg-dark text-white d-flex flex-column" style="min-width:230px;min-height:100vh;">
  <div class="p-3 border-bottom border-secondary">
    <i class="bi bi-book-half fs-4 text-warning me-2"></i>
    <span class="fw-bold fs-5">Biblioteca Digital</span>
  </div>
  <ul class="nav flex-column p-2 flex-grow-1">
    <?php if (mostrarMenu('dashboard')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=dashboard" class="nav-link text-white <?= menuActivo('dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li><?php endif; ?>

    <li class="nav-item mt-2"><small class="text-secondary px-3">CATÁLOGO</small></li>
    <?php if (mostrarMenu('libros')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=libros" class="nav-link text-white <?= menuActivo('libros') ?>"><i class="bi bi-journals me-2"></i>Libros</a></li><?php endif; ?>
    <?php if (mostrarMenu('categorias')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=categorias" class="nav-link text-white <?= menuActivo('categorias') ?>"><i class="bi bi-tags me-2"></i>Categorías</a></li><?php endif; ?>
    <?php if (mostrarMenu('solicitudes')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=solicitudes" class="nav-link text-white <?= menuActivo('solicitudes') ?>"><i class="bi bi-envelope-plus me-2"></i>Solicitudes</a></li><?php endif; ?>
    <?php if (mostrarMenu('reservas')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=reservas&accion=admin" class="nav-link text-white <?= menuActivo('reservas') ?>"><i class="bi bi-calendar-check me-2"></i>Reservas</a></li><?php endif; ?>

    <li class="nav-item mt-2"><small class="text-secondary px-3">PERSONAS</small></li>
    <?php if (mostrarMenu('estudiantes')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=estudiantes" class="nav-link text-white <?= menuActivo('estudiantes') ?>"><i class="bi bi-mortarboard me-2"></i>Estudiantes</a></li><?php endif; ?>
    <?php if (mostrarMenu('profesores')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=profesores" class="nav-link text-white <?= menuActivo('profesores') ?>"><i class="bi bi-person-workspace me-2"></i>Profesores</a></li><?php endif; ?>
    <?php if (mostrarMenu('carreras')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=carreras" class="nav-link text-white <?= menuActivo('carreras') ?>"><i class="bi bi-building-check me-2"></i>Carreras</a></li><?php endif; ?>

    <li class="nav-item mt-2"><small class="text-secondary px-3">ADMINISTRACIÓN</small></li>
    <?php if (mostrarMenu('usuarios')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=usuarios" class="nav-link text-white <?= menuActivo('usuarios') ?>"><i class="bi bi-people me-2"></i>Usuarios</a></li><?php endif; ?>
    <?php if (mostrarMenu('roles')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=roles" class="nav-link text-white <?= menuActivo('roles') ?>"><i class="bi bi-shield-lock me-2"></i>Roles</a></li><?php endif; ?>
    <?php if (mostrarMenu('configuracion')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=configuracion" class="nav-link text-white <?= menuActivo('configuracion') ?>"><i class="bi bi-gear me-2"></i>Configuración</a></li><?php endif; ?>
    <?php if (mostrarMenu('logs')): ?><li class="nav-item"><a href="<?= BASE_URL ?>/index.php?mod=logs" class="nav-link text-white <?= menuActivo('logs') ?>"><i class="bi bi-clipboard-data me-2"></i>Logs</a></li><?php endif; ?>
  </ul>
  <div class="p-3 border-top border-secondary">
    <form method="POST" action="<?= BASE_URL ?>/index.php?mod=auth&accion=logout">
      <?= CsrfToken::campoOculto() ?>
      <button class="btn btn-sm btn-outline-light w-100"><i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión</button>
    </form>
  </div>
</nav>
