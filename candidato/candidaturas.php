<?php
require_once '../includes/config.php';
requireAuth('candidato');
$cfg=allCfg(); $cand=meCandidato();
if(!$cand) redirect('logout.php');
$titulo_pag='Minhas Candidaturas';

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['retirar'])){
    DB::exec("UPDATE candidaturas SET estado='retirada' WHERE id=? AND candidato_id=?",[(int)$_POST['cid'],$cand['id']]);
    redirect('candidato/candidaturas.php','Candidatura retirada.','aviso');
}

$cands=DB::all("SELECT c.*,v.titulo,v.tipo_contrato,v.modalidade,v.estado as vest,e.nome as en,e.logo as el FROM candidaturas c JOIN vagas v ON v.id=c.vaga_id JOIN empresas e ON e.id=v.empresa_id WHERE c.candidato_id=? ORDER BY c.data_candidatura DESC",[$cand['id']]);
$passos=['enviada','vista','em_analise','entrevista','oferta','aceite'];
$labels_p=['enviada'=>'Enviada','vista'=>'Vista','em_analise'=>'Análise','entrevista'=>'Entrevista','oferta'=>'Oferta','aceite'=>'Aceite'];

require_once '../includes/header_painel.php';
?>

<div class="d-flex justify-content-end mb-3">
  <a href="<?= url('index.php') ?>" class="btn btn-sm btn-ace"><i data-feather="search" style="width:14px;height:14px;" class="me-1"></i>Pesquisar Vagas</a>
</div>

<?php if(empty($cands)): ?>
<div class="text-center py-5">
  <div style="font-size:3rem;margin-bottom:.75rem;">📋</div>
  <h5>Ainda não te candidataste a nenhuma vaga</h5>
  <p style="color:#6c757d;">Pesquisa vagas e candita-te para acompanhar o progresso aqui.</p>
  <a href="<?= url('index.php') ?>" class="btn btn-ace mt-2">Pesquisar Vagas</a>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
  <?php foreach($cands as $c): [$lbl,$cls]=estadoCandidaturaLabel($c['estado']); ?>
  <div class="p-card">
    <div class="p-card-body">
      <div class="d-flex align-items-start gap-3 flex-wrap">
        <?php if($c['el']): ?>
          <img src="<?= url('uploads/logos/'.h($c['el'])) ?>" style="width:48px;height:48px;border-radius:10px;object-fit:contain;background:#f8f9fc;padding:4px;border:1px solid #e4e9f0;flex-shrink:0;">
        <?php else: ?>
          <div style="width:48px;height:48px;border-radius:10px;background:var(--pri);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;flex-shrink:0;"><?= mb_strtoupper(mb_substr($c['en'],0,1)) ?></div>
        <?php endif; ?>
        <div class="flex-grow-1 min-w-0">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
            <div>
              <div class="fw-bold" style="color:var(--pri);"><?= h($c['titulo']) ?></div>
              <div class="text-muted-sm"><?= h($c['en']) ?></div>
              <div class="d-flex gap-1 mt-1 flex-wrap">
                <span class="badge-contrato" style="font-size:.7rem;"><?= labelContrato($c['tipo_contrato']) ?></span>
                <span class="badge-modalidade" style="font-size:.7rem;"><?= labelModalidade($c['modalidade']) ?></span>
              </div>
            </div>
            <div class="text-end">
              <span class="badge bg-<?= $cls ?>"><?= $lbl ?></span>
              <div class="text-muted-sm mt-1"><?= tempo($c['data_candidatura']) ?></div>
            </div>
          </div>

          <!-- Barra de progresso por estados -->
          <?php if($c['estado'] !== 'rejeitada' && $c['estado'] !== 'retirada'): ?>
          <div class="mt-3">
            <div class="d-flex gap-1" style="font-size:.65rem;">
              <?php $idx=array_search($c['estado'],$passos); foreach($passos as $i=>$p): $ativo=$idx!==false&&$i<=$idx; ?>
              <div class="flex-fill text-center" style="min-width:0;">
                <div style="height:4px;border-radius:2px;background:<?= $ativo?'var(--ace)':'#e4e9f0' ?>;margin-bottom:3px;"></div>
                <span style="color:<?= $ativo?'var(--ace)':'#94a3b8' ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;"><?= $labels_p[$p] ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php elseif($c['estado']==='rejeitada'): ?>
          <div class="mt-2 px-2 py-1 rounded" style="background:#ffebee;font-size:.8rem;color:#c62828;display:inline-block;">✗ Candidatura não seleccionada</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top flex-wrap gap-2">
        <a href="<?= url('vaga.php?id='.$c['vaga_id']) ?>" class="btn btn-sm btn-outline-secondary">Ver Vaga</a>
        <?php if($c['estado']==='enviada'): ?>
        <form method="POST" class="d-inline" onsubmit="return confirm('Retirar esta candidatura?')">
          <input type="hidden" name="retirar" value="1">
          <input type="hidden" name="cid" value="<?= $c['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Retirar Candidatura</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/footer_painel.php'; ?>
