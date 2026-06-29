<?php if (($paginas ?? 1) > 1): ?>
<div class="card-footer bg-white d-flex justify-content-between align-items-center">
  <small class="text-muted">Total: <strong><?= $total ?? 0 ?></strong> registros</small>
  <nav>
    <ul class="pagination pagination-sm mb-0">
      <?php for ($p = 1; $p <= $paginas; $p++):
        $params = array_merge($_GET, ['pag' => $p]);
        $url    = BASE_URL . '?' . http_build_query($params);
        $activo = ($p === ($pagina ?? 1)) ? 'active' : '';
      ?>
      <li class="page-item <?= $activo ?>">
        <a class="page-link" href="<?= $url ?>"><?= $p ?></a>
      </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>
<?php endif; ?>
