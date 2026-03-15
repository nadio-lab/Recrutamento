<?php
require_once 'includes/config.php';
$cfg = allCfg();
$cats = DB::all(
    "SELECT c.*, COUNT(v.id) as total FROM categorias c
     LEFT JOIN vagas v ON v.categoria_id=c.id AND v.estado='publicada'
     WHERE c.ativo=1 GROUP BY c.id ORDER BY total DESC, c.nome"
);
$titulo = 'Categorias — '.($cfg['site_nome']??'Emprega');
require_once 'includes/header_publico.php';
?>

<div class="container py-5">
  <div class="mb-4">
    <h1 style="font-size:1.6rem;font-weight:700;color:var(--pri);">Todas as Categorias</h1>
    <p style="color:#6c757d;">Explora vagas por área profissional</p>
  </div>
  <div class="row g-3">
    <?php foreach ($cats as $c): ?>
    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
      <a href="<?= url('index.php?cat='.$c['id']) ?>" class="cat-card text-decoration-none d-block">
        <div class="cat-icon">
          <i data-feather="<?= h($c['icone']??'briefcase') ?>" style="width:22px;height:22px;"></i>
        </div>
        <div class="cat-nome"><?= h($c['nome']) ?></div>
        <div class="cat-vagas"><?= $c['total'] ?> vaga<?= $c['total']!=1?'s':'' ?></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once 'includes/footer_publico.php'; ?>
