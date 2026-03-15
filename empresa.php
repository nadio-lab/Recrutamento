<?php
require_once 'includes/config.php';
$cfg = allCfg();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: '.url('empresas.php')); exit; }

$empresa = DB::row(
    "SELECT e.*, u.criado_em as membro_desde, p.nome as prov
     FROM empresas e
     JOIN utilizadores u ON u.id=e.utilizador_id
     LEFT JOIN provincias p ON p.id=e.provincia_id
     WHERE e.id=? AND e.estado='aprovada'", [$id]
);
if (!$empresa) { header('Location: '.url('empresas.php')); exit; }

$vagas = DB::all(
    "SELECT v.*, c.nome as cn, p.nome as pn FROM vagas v
     LEFT JOIN categorias c ON c.id=v.categoria_id
     LEFT JOIN provincias p ON p.id=v.provincia_id
     WHERE v.empresa_id=? AND v.estado='publicada'
     ORDER BY v.destaque DESC, v.data_publicacao DESC",
    [$id]
);

$titulo    = h($empresa['nome']).' — Vagas de Emprego';
$descricao = mb_substr(strip_tags($empresa['sobre'] ?? ''), 0, 160);
require_once 'includes/header_publico.php';
?>

<div class="container py-4">

  <!-- CABEÇALHO DA EMPRESA -->
  <div class="p-card mb-4">
    <div class="p-card-body">
      <div class="d-flex align-items-start gap-4 flex-wrap">
        <?php if ($empresa['logo']): ?>
          <img src="<?= url('uploads/logos/'.h($empresa['logo'])) ?>"
            style="width:90px;height:90px;border-radius:16px;object-fit:contain;background:#f8f9fc;padding:8px;border:1px solid #e4e9f0;flex-shrink:0;" alt="">
        <?php else: ?>
          <div style="width:90px;height:90px;border-radius:16px;background:linear-gradient(135deg,var(--pri),var(--sec));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:2rem;flex-shrink:0;">
            <?= mb_strtoupper(mb_substr($empresa['nome'],0,1)) ?>
          </div>
        <?php endif; ?>
        <div class="flex-grow-1 min-w-0">
          <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
            <h1 style="font-size:1.5rem;font-weight:700;color:var(--pri);margin:0;"><?= h($empresa['nome']) ?></h1>
            <?php if ($empresa['verificada']): ?>
            <span style="font-size:.78rem;color:#2d6a4f;font-weight:600;background:#f0fdf4;padding:.2rem .55rem;border-radius:999px;display:flex;align-items:center;gap:.25rem;">
              <i data-feather="check-circle" style="width:13px;height:13px;"></i>Verificada
            </span>
            <?php endif; ?>
          </div>
          <?php if ($empresa['setor']): ?>
          <div style="font-size:.9rem;color:var(--sec);font-weight:500;margin-bottom:.5rem;"><?= h($empresa['setor']) ?></div>
          <?php endif; ?>
          <div class="d-flex flex-wrap gap-3" style="font-size:.84rem;color:#6c757d;">
            <?php if ($empresa['prov']): ?><span><i data-feather="map-pin" style="width:13px;height:13px;"></i> <?= h($empresa['prov']) ?></span><?php endif; ?>
            <?php if ($empresa['dimensao']): ?><span><i data-feather="users" style="width:13px;height:13px;"></i> <?= ucfirst($empresa['dimensao']) ?></span><?php endif; ?>
            <?php if ($empresa['ano_fundacao']): ?><span><i data-feather="calendar" style="width:13px;height:13px;"></i> Desde <?= $empresa['ano_fundacao'] ?></span><?php endif; ?>
            <?php if ($empresa['website']): ?>
            <a href="<?= h($empresa['website']) ?>" target="_blank" rel="noopener" style="color:var(--sec);text-decoration:none;">
              <i data-feather="globe" style="width:13px;height:13px;"></i> <?= h(preg_replace('#^https?://#','',$empresa['website'])) ?>
            </a>
            <?php endif; ?>
            <span><i data-feather="briefcase" style="width:13px;height:13px;"></i> <?= count($vagas) ?> vaga<?= count($vagas)!=1?'s':'' ?> activa<?= count($vagas)!=1?'s':'' ?></span>
          </div>
        </div>
      </div>
      <?php if ($empresa['sobre']): ?>
      <div class="mt-3 pt-3 border-top" style="font-size:.9rem;line-height:1.75;color:#374151;">
        <?= nl2br(h($empresa['sobre'])) ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- VAGAS DA EMPRESA -->
  <h2 style="font-size:1.15rem;font-weight:700;color:var(--pri);margin-bottom:1rem;">
    Vagas em aberto <span style="font-size:.85rem;font-weight:400;color:#6c757d;">(<?= count($vagas) ?>)</span>
  </h2>

  <?php if (empty($vagas)): ?>
  <div class="text-center py-5" style="color:#6c757d;">
    <div style="font-size:2.5rem;margin-bottom:.5rem;">🔍</div>
    <h5>Sem vagas publicadas de momento</h5>
    <p>Volta mais tarde ou pesquisa outras empresas.</p>
    <a href="<?= url('index.php') ?>" class="btn btn-sm btn-ace mt-2">Ver todas as vagas</a>
  </div>
  <?php else: ?>
  <div class="d-flex flex-column gap-3">
    <?php foreach ($vagas as $v): ?>
    <div class="vaga-card <?= $v['destaque']?'destaque':'' ?>">
      <?php if ($v['destaque']): ?>
      <div style="position:absolute;top:.75rem;right:.75rem;"><span class="badge-destaque">⭐ Destaque</span></div>
      <?php endif; ?>
      <div class="flex-grow-1 min-w-0">
        <h3 class="vaga-titulo mb-1">
          <a href="<?= url('vaga.php?id='.$v['id']) ?>"><?= h($v['titulo']) ?></a>
        </h3>
        <div class="d-flex flex-wrap gap-1 mt-1 mb-2">
          <span class="badge-contrato"><?= labelContrato($v['tipo_contrato']) ?></span>
          <span class="badge-modalidade"><?= labelModalidade($v['modalidade']) ?></span>
          <span class="badge-experiencia"><?= labelExperiencia($v['nivel_experiencia']) ?></span>
        </div>
        <div class="d-flex flex-wrap gap-3 align-items-center" style="font-size:.82rem;color:#6c757d;">
          <?php if ($v['pn']): ?><span><i data-feather="map-pin" style="width:12px;height:12px;"></i> <?= h($v['pn']) ?></span><?php endif; ?>
          <span><i data-feather="clock" style="width:12px;height:12px;"></i> <?= tempo($v['data_publicacao']) ?></span>
          <span style="font-weight:600;color:#2d6a4f;"><?= formatSalario($v['salario_min'],$v['salario_max'],$v['moeda_salario'],$v['salario_visivel']) ?></span>
        </div>
      </div>
      <div class="d-flex justify-content-end gap-2 mt-2">
        <a href="<?= url('vaga.php?id='.$v['id']) ?>" class="btn btn-sm btn-outline-secondary">Ver detalhes</a>
        <a href="<?= url('vaga.php?id='.$v['id'].'#candidatar') ?>" class="btn-candidatar">Candidatar-se</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer_publico.php'; ?>
