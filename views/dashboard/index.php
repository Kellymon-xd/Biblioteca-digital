<!-- Dashboard -->
<div class="row g-4 mb-4 dashboard-stats">
  <?php
  $cards = [
    ['icon'=>'journals','color'=>'primary','label'=>'Libros','val'=>$totales['libros']],
    ['icon'=>'mortarboard','color'=>'success','label'=>'Estudiantes','val'=>$totales['estudiantes']],
    ['icon'=>'person-workspace','color'=>'secondary','label'=>'Profesores','val'=>$totales['profesores'] ?? 0],
    ['icon'=>'bookmark-check','color'=>'warning','label'=>'Reservas','val'=>$totales['reservas']],
    ['icon'=>'boxes','color'=>'info','label'=>'Disponibles','val'=>$disponibles],
  ];
  foreach ($cards as $c): ?>
  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100 dashboard-stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="dashboard-stat-icon rounded-3 p-3 bg-<?= $c['color'] ?> bg-opacity-10 text-<?= $c['color'] ?>">
          <i class="bi bi-<?= $c['icon'] ?> fs-2"></i>
        </div>
        <div>
          <div class="fs-2 fw-bold dashboard-stat-value"><?= $c['val'] ?></div>
          <div class="text-muted small dashboard-stat-label"><?= $c['label'] ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <!-- Últimas reservas -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-bookmark-check me-2 text-warning"></i>Últimas Reservas
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light"><tr>
            <th>#</th><th>Libro</th><th>Lector</th><th>Tipo</th><th>Estado</th><th>Fecha</th>
          </tr></thead>
          <tbody>
          <?php foreach ($ultimasReservas as $r): ?>
          <tr>
            <td class="text-muted small"><?= $r['id_reserva'] ?></td>
            <td><?= Sanitizador::html($r['titulo']) ?></td>
            <td><?= Sanitizador::html($r['lector'] ?? '') ?></td>
            <td><?= Sanitizador::html($r['tipo_actor'] ?? '') ?></td>
            <td>
              <?php $bc=['ACTIVA'=>'success','DEVUELTA'=>'secondary','PENDIENTE'=>'warning','CANCELADA'=>'danger']; ?>
              <span class="badge bg-<?= $bc[$r['estado']] ?? 'secondary' ?>"><?= $r['estado'] ?></span>
            </td>
            <td class="small"><?= date('d/m/Y', strtotime($r['fecha_reserva'])) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Log de accesos -->
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-shield-check me-2 text-danger"></i>Bitácora de Accesos
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($ultimosLogs as $log):
          $icons = ['LOGIN_EXITOSO'=>'check-circle text-success','LOGIN_FALLIDO'=>'x-circle text-danger','CUENTA_BLOQUEADA'=>'lock text-warning','CIERRE_SESION'=>'box-arrow-right text-secondary'];
          $icon  = $icons[$log['accion']] ?? 'circle';
        ?>
        <li class="list-group-item px-3 py-2">
          <i class="bi bi-<?= $icon ?> me-2"></i>
          <strong><?= Sanitizador::html($log['identificador'] ?? $log['username'] ?? '?') ?></strong>
          <span class="text-muted small ms-1"><?= $log['ip_address'] ?></span>
          <div class="text-muted" style="font-size:.75rem"><?= date('d/m/Y H:i', strtotime($log['fecha'])) ?></div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
