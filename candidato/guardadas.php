<?php
require_once '../includes/config.php';
requireAuth('candidato');
$cfg=allCfg(); $cand=meCandidato();
if(!$cand) redirect('logout.php');
$titulo_pag='Vagas Guardadas';

$guardadas=DB::all("SELECT v.*,e.nome as en,e.logo as el,vg.criado_em as ge FROM vagas_guardadas vg JOIN vagas v ON v.id=vg.vaga_id JOIN empresas e ON e.id=v.empresa_id WHERE vg.candidato_id=? ORDER BY vg.criado_em DESC",[$cand['id']]);
require_once '../includes/header_painel.php';
?>

<?php if(empty($guardadas)): ?>
<div class="text-center py-5">
  <div style="font-size:3rem;margin-bottom:.75rem;">❤️</div>
  <h5>Nenhuma vaga guardada ainda</h5>
  <p style="color:#6c757d;">Usa o botão ❤ nas vagas para as guardar para mais tarde.</p>
  <a href="<?= url('index.php') ?>" class="btn btn-ace mt-2">Explorar Vagas</a>
</div>
<?php else: ?>
<div class="row g-3">
  <?php foreach($guardadas as $v): ?>
  <div class="col-md-6 col-xl-4">
    <div class="vaga-card h-100">
      <div class="d-flex align-items-start gap-3">
        <?php if($v['el']): ?>
          <img src="<?= url('uploads/logos/'.h($v['el'])) ?>" class="vaga-empresa-logo">
        <?php else: ?>
          <div class="vaga-empresa-logo-placeholder"><?= mb_strtoupper(mb_substr($v['en'],0,1)) ?></div>
        <?php endif; ?>
        <div class="flex-grow-1 min-w-0">
          <h6 class="vaga-titulo mb-1"><a href="<?= url('vaga.php?id='.$v['id']) ?>"><?= h($v['titulo']) ?></a></h6>
          <div class="vaga-empresa-nome"><?= h($v['en']) ?></div>
          <div class="d-flex flex-wrap gap-1 mt-1">
            <span class="badge-contrato" style="font-size:.7rem;"><?= labelContrato($v['tipo_contrato']) ?></span>
            <span class="badge-modalidade" style="font-size:.7rem;"><?= labelModalidade($v['modalidade']) ?></span>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
        <span class="text-muted-sm">Guardada <?= tempo($v['ge']) ?></span>
        <div class="d-flex gap-2">
          <button class="btn-guardar guardada" onclick="removerGuardada(<?= $v['id'] ?>,this)" title="Remover">
            <i data-feather="heart" style="width:14px;height:14px;fill:currentColor;"></i>
          </button>
          <a href="<?= url('vaga.php?id='.$v['id'].'#candidatar') ?>" class="btn-candidatar">Candidatar-se</a>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function removerGuardada(id, btn) {
  fetch('<?= url('ajax/guardar_vaga.php') ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'vaga_id='+id})
  .then(r=>r.json()).then(d=>{ if(!d.guardada) btn.closest('[class*="col-"]').remove(); });
}
</script>

<?php require_once '../includes/footer_painel.php'; ?>
