<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm px-4 py-2">
  <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary me-3">
    <i class="bi bi-list"></i>
  </button>
  <span class="navbar-brand fw-semibold text-primary mb-0 h1">
    <?= Sanitizador::html($tituloPagina ?? '') ?>
  </span>
  <div class="ms-auto d-flex align-items-center gap-3">
    <span class="text-muted small">
      <i class="bi bi-person-circle me-1"></i>
      <?= Sanitizador::html($_SESSION['usuario']['nombre'] ?? '') ?>
      <span class="badge bg-secondary ms-1"><?= Sanitizador::html($_SESSION['usuario']['rol'] ?? '') ?></span>
    </span>
    <span class="text-muted small">
      <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i') ?>
    </span>
  </div>
</nav>
