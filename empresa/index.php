<?php
require_once '../includes/config.php';
requireAuth('empresa');
$cfg = allCfg();
$emp = meEmpresa();
if (!$emp) { redirect('logout.php'); }
$titulo_pag = 'Dashboard — ' . h($emp['nome']);

$stats = [
    'pub'   => DB::val("SELECT COUNT(*) FROM vagas WHERE empresa_id=? AND estado='publicada'",[$emp['id']]) ?? 0,
    'total' => DB::val("SELECT COUNT(*) FROM vagas WHERE empresa_id=?",[$emp['id']]) ?? 0,
    'cands' => DB::val("SELECT COUNT(*) FROM candidaturas c JOIN vagas v ON v.id=c.vaga_id WHERE v.empresa_id=?",[$emp['id']]) ?? 0,
    'novas' => DB::val("SELECT COUNT(*) FROM candidaturas c JOIN vagas v ON v.id=c.vaga_id WHERE v.empresa_id=? AND c.estado='enviada'",[$emp['id']]) ?? 0,
];

$vagas_rec = DB::all("SELECT v.*,COUNT(c.id) as tc FROM vagas v LEFT JOIN candidaturas c ON c.vaga_id=v.id WHERE v.empresa_id=? GROUP BY v.id ORDER BY v.criado_em DESC LIMIT 5",[$emp['id']]);
$cands_rec = DB::all("SELECT c.*,v.titulo as vt,u.nome as cn,ca.titulo_profissional as tp,ca.foto FROM candidaturas c JOIN vagas v ON v.id=c.vaga_id JOIN candidatos ca ON ca.id=c.candidato_id JOIN utilizadores u ON u.id=ca.utilizador_id WHERE v.empresa_id=? ORDER BY c.data_candidatura DESC LIMIT 8",[$emp['id']]);

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['upd'])) {
    $cid=$_POST['cid']??0; $est=$_POST['est']??'';
    DB::exec("UPDATE candidaturas SET estado=? WHERE id=? AND vaga_id IN(SELECT id FROM vagas WHERE empresa_id=?)",[$est,(int)$cid,$emp['id']]);
    
    // CORREÇÃO: Adicionado [] em volta do (int)$cid
    $c=DB::row("SELECT c.*,ca.utilizador_id FROM candidaturas c JOIN candidatos ca ON ca.id=c.candidato_id WHERE c.id=?", [(int)$cid]);
    
    if($c){
        [$lbl]=estadoCandidaturaLabel($est); 
        notificar($c['utilizador_id'],'estado_candidatura','Candidatura actualizada',"Estado: $lbl.");
    }
    redirect('empresa/index.php','Estado actualizado!');
}

require_once '../includes/header_painel.php';
?>

<?php if ($emp['estado']==='pendente'): ?>
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
  <i data-feather="clock" style="width:16px;height:16px;flex-shrink:0;"></i>
  A tua empresa aguarda aprovação. Assim que aprovada poderás publicar vagas.
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3"><div class="stat-box s-blue"><div class="stat-icon s-blue"><i data-feather="briefcase"></i></div><div class="stat-val"><?= $stats['pub'] ?></div><div class="stat-lbl">Vagas Activas</div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-box s-amber"><div class="stat-icon s-amber"><i data-feather="layers"></i></div><div class="stat-val"><?= $stats['total'] ?></div><div class="stat-lbl">Total Vagas</div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-box s-green"><div class="stat-icon s-green"><i data-feather="users"></i></div><div class="stat-val"><?= $stats['cands'] ?></div><div class="stat-lbl">Candidaturas</div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-box s-red"><div class="stat-icon s-red"><i data-feather="inbox"></i></div><div class="stat-val"><?= $stats['novas'] ?></div><div class="stat-lbl">Por Analisar</div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="p-card h-100">
      <div class="p-card-header d-flex justify-content-between align-items-center">
        <span><i data-feather="briefcase" style="width:15px;height:15px;" class="me-1"></i>Minhas Vagas</span>
        <a href="<?= url('empresa/nova_vaga.php') ?>" class="btn btn-sm btn-ace py-1">+ Nova</a>
      </div>
      <div class="p-card-body p-0">
        <?php if (empty($vagas_rec)): ?>
        <div class="text-center py-4" style="font-size:.875rem;color:#6c757d;">
          Nenhuma vaga ainda.<br>
          <a href="<?= url('empresa/nova_vaga.php') ?>" style="color:var(--ace);">Publicar a primeira →</a>
        </div>
        <?php else: foreach ($vagas_rec as $v):
          $cls=match($v['estado']){'publicada'=>'b-pub','pendente'=>'b-pend','encerrada'=>'b-enc',default=>'b-enc'};
          $lbl=match($v['estado']){'publicada'=>'Publicada','pendente'=>'Pendente','encerrada'=>'Encerrada','rascunho'=>'Rascunho',default=>ucfirst($v['estado'])};
        ?>
        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center gap-2">
          <div class="min-w-0">
            <div class="fw-600" style="font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($v['titulo']) ?></div>
            <div class="text-muted-sm"><i data-feather="users" style="width:11px;height:11px;"></i> <?= $v['tc'] ?> · <?= tempo($v['criado_em']) ?></div>
          </div>
          <div class="d-flex align-items-center gap-1 flex-shrink-0">
            <span class="b-status <?= $cls ?>" style="font-size:.7rem;"><?= $lbl ?></span>
            <a href="<?= url('empresa/nova_vaga.php?id='.$v['id']) ?>" class="btn btn-sm btn-outline-secondary py-0 px-1"><i data-feather="edit-2" style="width:12px;height:12px;"></i></a>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
      <div class="p-card-footer"><a href="<?= url('empresa/vagas.php') ?>" class="btn btn-sm btn-outline-secondary">Ver todas</a></div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="p-card h-100">
      <div class="p-card-header d-flex justify-content-between align-items-center">
        <span><i data-feather="file-text" style="width:15px;height:15px;" class="me-1"></i>Candidaturas Recentes</span>
        <a href="<?= url('empresa/candidaturas.php') ?>" class="btn btn-sm btn-outline-secondary py-1">Ver todas</a>
      </div>
      <div class="p-card-body p-0">
        <?php if (empty($cands_rec)): ?>
        <div class="text-center py-4" style="font-size:.875rem;color:#6c757d;">Sem candidaturas ainda.</div>
        <?php else: foreach ($cands_rec as $c): [$lbl,$cls]=estadoCandidaturaLabel($c['estado']); ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <?php if ($c['foto']): ?>
            <img src="<?= url('uploads/fotos/'.h($c['foto'])) ?>" class="avatar-sm">
          <?php else: ?>
            <div class="avatar-sm d-flex align-items-center justify-content-center text-white fw-bold" style="background:var(--sec);font-size:.7rem;"><?= mb_strtoupper(mb_substr($c['cn'],0,1)) ?></div>
          <?php endif; ?>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-600" style="font-size:.84rem;"><?= h($c['cn']) ?></div>
            <div class="text-muted-sm"><?= h($c['vt']) ?> · <?= tempo($c['data_candidatura']) ?></div>
          </div>
          <form method="POST" class="d-flex align-items-center gap-1 flex-shrink-0">
            <input type="hidden" name="upd" value="1">
            <input type="hidden" name="cid" value="<?= $c['id'] ?>">
            <select name="est" class="form-select form-select-sm" style="width:125px;font-size:.76rem;" onchange="this.form.submit()">
              <?php foreach(['enviada','vista','em_analise','entrevista','oferta','aceite','rejeitada'] as $e): [$el]=estadoCandidaturaLabel($e); ?>
              <option value="<?= $e ?>" <?= $c['estado']===$e?'selected':'' ?>><?= $el ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer_painel.php'; ?>
