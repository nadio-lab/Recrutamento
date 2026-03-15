<?php
require_once 'includes/config.php';
$cfg = allCfg();
$q   = trim($_GET['q'] ?? '');
$w   = ["e.estado='aprovada'"]; $p = [];
if ($q) { $w[] = "(e.nome LIKE ? OR e.setor LIKE ?)"; $b="%$q%"; $p=[$b,$b]; }

$empresas = DB::all(
    "SELECT e.*, pv.nome as prov, COUNT(DISTINCT v.id) as vagas_activas
     FROM empresas e
     LEFT JOIN provincias pv ON pv.id=e.provincia_id
     LEFT JOIN vagas v ON v.empresa_id=e.id AND v.estado='publicada'
     WHERE ".implode(' AND ',$w)."
     GROUP BY e.id ORDER BY e.verificada DESC, vagas_activas DESC LIMIT 60",
    $p
);
$titulo = 'Empresas — '.($cfg['site_nome']??'Emprega');
require_once 'includes/header_publico.php';
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
    <div>
      <h1 style="font-size:1.6rem;font-weight:700;color:var(--pri);">Empresas a Contratar</h1>
      <p style="color:#6c757d;margin:0;"><?= count($empresas) ?> empresa<?= count($empresas)!=1?'s':'' ?> com vagas activas</p>
    </div>
    <form method="GET" class="d-flex gap-2">
      <input type="text" name="q" class="form-control" placeholder="Pesquisar empresa ou sector..." value="<?= h($q) ?>" style="width:240px;">
      <button class="btn btn-ace fw-600">Pesquisar</button>
      <?php if ($q): ?><a href="<?= url('empresas.php') ?>" class="btn btn-outline-secondary">Limpar</a><?php endif; ?>
    </form>
  </div>

  <?php if (empty($empresas)): ?>
  <div class="text-center py-5" style="color:#6c757d;">
    <div style="font-size:3rem;margin-bottom:.5rem;">🏢</div>
    <h5>Nenhuma empresa encontrada</h5>
    <a href="<?= url('empresas.php') ?>" class="btn btn-sm btn-ace mt-2">Ver todas</a>
  </div>
  <?php else: ?>
  <div class="row g-3">
    <?php foreach ($empresas as $e): ?>
    <div class="col-sm-6 col-lg-4">
      <a href="<?= url('empresa.php?id='.$e['id']) ?>" class="emp-card text-decoration-none d-block h-100" style="color:inherit;">
        <div class="d-flex align-items-start gap-3 mb-3">
          <?php if ($e['logo']): ?>
            <img src="<?= url('uploads/logos/'.h($e['logo'])) ?>" class="emp-logo flex-shrink-0" alt="">
          <?php else: ?>
            <div class="emp-logo-ph flex-shrink-0"><?= mb_strtoupper(mb_substr($e['nome'],0,1)) ?></div>
          <?php endif; ?>
          <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-center gap-1 flex-wrap">
              <span class="fw-bold" style="color:var(--pri);font-size:.95rem;"><?= h($e['nome']) ?></span>
              <?php if ($e['verificada']): ?>
              <span style="font-size:.68rem;color:#2d6a4f;font-weight:600;display:inline-flex;align-items:center;gap:.2rem;">
                <i data-feather="check-circle" style="width:11px;height:11px;"></i>
              </span>
              <?php endif; ?>
            </div>
            <?php if ($e['setor']): ?><div style="font-size:.82rem;color:var(--sec);"><?= h($e['setor']) ?></div><?php endif; ?>
          </div>
        </div>
        <?php if ($e['sobre']): ?>
        <p style="font-size:.83rem;color:#6c757d;line-height:1.6;margin-bottom:.75rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
          <?= h($e['sobre']) ?>
        </p>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mt-auto">
          <div class="d-flex gap-2" style="font-size:.78rem;color:#6c757d;">
            <?php if ($e['prov']): ?><span><?= h($e['prov']) ?></span><?php endif; ?>
            <?php if ($e['dimensao']): ?><span>· <?= ucfirst($e['dimensao']) ?></span><?php endif; ?>
          </div>
          <span style="font-size:.78rem;font-weight:600;color:var(--ace);background:#fff0f1;padding:.2rem .55rem;border-radius:999px;">
            <?= $e['vagas_activas'] ?> vaga<?= $e['vagas_activas']!=1?'s':'' ?>
          </span>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer_publico.php'; ?>
