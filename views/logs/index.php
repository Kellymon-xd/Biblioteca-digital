<?php
$tituloPagina = 'Bitácora de Acceso';
require_once SRC_PATH . '/views/layout/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h3 mb-0">Bitácora de acceso</h1><p class="text-muted mb-0">El administrador puede ver intentos de login, cierres de sesión, IP y navegador.</p></div>
  </div>
  <div class="card border-0 shadow-sm mb-4"><div class="card-body"><form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2"><input type="hidden" name="mod" value="logs"><div class="col-md-10"><input type="search" name="q" class="form-control" placeholder="Buscar por usuario, IP, acción o tipo" value="<?= Sanitizador::html($busqueda ?? '') ?>"></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Buscar</button></div></form></div></div>
  <?php if (!empty($logs)): ?>
    <div class="card border-0 shadow-sm"><div class="card-body table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Fecha</th><th>Identificador</th><th>Tipo</th><th>Acción</th><th>IP</th><th>Descripción</th><th>Navegador</th></tr></thead>
        <tbody><?php foreach ($logs as $log): ?><tr>
          <td><?= !empty($log['fecha']) ? date('d/m/Y H:i:s', strtotime($log['fecha'])) : '' ?></td>
          <td><span class="badge bg-light text-dark border"><?= Sanitizador::html($log['identificador'] ?? $log['username'] ?? '') ?></span></td>
          <td><?= Sanitizador::html($log['tipo_actor'] ?? '') ?></td>
          <td><?= Sanitizador::html($log['accion'] ?? '') ?></td>
          <td><?= Sanitizador::html($log['ip_address'] ?? '') ?></td>
          <td><?= Sanitizador::html($log['descripcion'] ?? '') ?></td>
          <td class="small text-muted"><?= Sanitizador::html(mb_strimwidth((string)($log['user_agent'] ?? ''), 0, 90, '...')) ?></td>
        </tr><?php endforeach; ?></tbody>
      </table>
    </div></div>
  <?php else: ?><div class="alert alert-info">No hay logs registrados.</div><?php endif; ?>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
