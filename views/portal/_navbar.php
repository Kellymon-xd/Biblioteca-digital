<nav class="navbar navbar-expand-lg portal-navbar">
  <div class="container">
    <a class="navbar-brand portal-brand" href="<?= BASE_URL ?>/index.php?mod=portal">
      <span class="portal-brand-mark"></span>
      Biblioteca Digital
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav"
      aria-controls="portalNav" aria-expanded="false" aria-label="Abrir menú">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="portalNav">
      <div class="portal-nav-actions">
        <a class="btn btn-outline-secondary btn-sm <?= ($activePortal ?? '') === 'catalogo' ? 'active' : '' ?>"
          href="<?= BASE_URL ?>/index.php?mod=portal&accion=index">
          Catálogo
        </a>

        <a class="btn btn-outline-secondary btn-sm <?= ($activePortal ?? '') === 'estadisticas' ? 'active' : '' ?>"
          href="<?= BASE_URL ?>/index.php?mod=portal&accion=estadisticas">
          Estadísticas
        </a>

        <a class="btn btn-outline-secondary btn-sm <?= ($activePortal ?? '') === 'acerca' ? 'active' : '' ?>"
          href="<?= BASE_URL ?>/index.php?mod=portal&accion=acerca">
          El sistema
        </a>

        <?php if (!empty($_SESSION['lector'])): ?>
          <a class="btn btn-outline-primary btn-sm <?= ($activePortal ?? '') === 'reservas' ? 'active' : '' ?>"
            href="<?= BASE_URL ?>/index.php?mod=reservas">
            Mis reservas
          </a>

          <a class="btn btn-outline-secondary btn-sm <?= ($activePortal ?? '') === 'solicitar' ? 'active' : '' ?>"
            href="<?= BASE_URL ?>/index.php?mod=solicitudes&accion=mis">
            Solicitar libro
          </a>
        <?php else: ?>
          <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/index.php?mod=portal&accion=login">
            Iniciar sesión
          </a>

          <a class="btn btn-outline-dark btn-sm" href="<?= BASE_URL ?>/index.php?mod=auth&accion=login">
            Administración
          </a>
        <?php endif; ?>
        <button type="button" id="installAppButton" class="btn btn-outline-secondary btn-sm pwa-install-btn" hidden>
          Instalar app
        </button>
      </div>

      <?php if (!empty($_SESSION['lector'])): ?>
        <div class="portal-userbar">
          <span class="portal-user-meta small">
            <i class="bi bi-person-circle"></i>
            <span class="portal-user-name"><?= Sanitizador::html($_SESSION['lector']['nombre'] ?? 'Usuario') ?></span>
            <span class="badge bg-light text-dark border">
              <?= Sanitizador::html($_SESSION['lector']['label'] ?? $_SESSION['lector']['tipo'] ?? '') ?>
            </span>
          </span>

          <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>/index.php?mod=portal&accion=logout">
            Salir
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>