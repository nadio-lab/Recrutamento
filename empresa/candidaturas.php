<?php
require_once '../includes/config.php';
requireAuth('empresa');
$cfg=allCfg(); $emp=meEmpresa();
if(!$emp) redirect('logout.php');
$titulo_pag='Candidaturas Recebidas';

$vaga_id =(int)($_GET['vaga']   ?? 0);
$estado_f=$_GET['estado'] ?? '';

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['upd'])){
    $cid=(int)$_POST['cid']; $est=$_POST['est']; $nota=trim($_POST['nota']??'');
    
    // Atualiza o estado e a nota
    DB::exec("UPDATE candidaturas SET estado=?, nota_empresa=? WHERE id=? AND vaga_id IN(SELECT id FROM vagas WHERE empresa_id=?)", [$est, $nota, $cid, $emp['id']]);
    
    // CORREÇÃO: Adicionado [] em volta do $cid
    $c=DB::row("SELECT c.*, ca.utilizador_id FROM candidaturas c JOIN candidatos ca ON ca.id=c.candidato_id WHERE c.id=?", [$cid]);
    
    if($c){
        [$lbl]=estadoCandidaturaLabel($est); 
        notificar($c['utilizador_id'], 'estado_candidatura', 'Candidatura actualizada', "Estado actualizado para: $lbl.");
    }
    redirect('empresa/candidaturas.php?vaga='.$vaga_id.'&estado='.$estado_f, 'Estado actualizado!');
}

$w=["v.empresa_id=?"]; $p=[$emp['id']];
if($vaga_id){$w[]="c.vaga_id=?"; $p[]=$vaga_id;}
if($estado_f){$w[]="c.estado=?"; $p[]=$estado_f;}

$sql = "SELECT c.*, v.titulo as vt, u.nome as cn, ca.titulo_profissional as tp, ca.foto, ca.cv_ficheiro, ca.linkedin, ca.portfolio, p.nome as prov 
        FROM candidaturas c 
        JOIN vagas v ON v.id=c.vaga_id 
        JOIN candidatos ca ON ca.id=c.candidato_id 
        JOIN utilizadores u ON u.id=ca.utilizador_id 
        LEFT JOIN provincias p ON p.id=ca.provincia_id 
        WHERE ".implode(' AND ',$w)." 
        ORDER BY c.data_candidatura DESC";

$candidaturas = DB::all($sql, $p);
$vagas_emp = DB::all("SELECT id, titulo FROM vagas WHERE empresa_id=? ORDER BY titulo", [$emp['id']]);

require_once '../includes/header_painel.php';
?>

<div class="p-card mb-3">
  <div class="p-card-body py-2">
    <form method="GET" action="<?= url('empresa/candidaturas.php') ?>" class="d-flex gap-2 flex-wrap align-items-end">
      <div>
        <label class="form-label mb-1" style="font-size:.8rem;">Vaga</label>
        <select name="vaga" class="form-select form-select-sm" style="min-width:180px;">
          <option value="">Todas as vagas</option>
          <?php foreach($vagas_emp as $v): ?>
          <option value="<?= $v['id'] ?>" <?= $vaga_id==$v['id']?'selected':'' ?>><?= h($v['titulo']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label mb-1" style="font-size:.8rem;">Estado</label>
        <select name="estado" class="form-select form-select-sm">
          <option value="">Todos</option>
          <?php foreach(['enviada'=>'Enviada','vista'=>'Vista','em_analise'=>'Em Análise','entrevista'=>'Entrevista','oferta'=>'Oferta','aceite'=>'Aceite','rejeitada'=>'Rejeitada'] as $k=>$l): ?>
          <option value="<?= $k ?>" <?= $estado_f===$k?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-sm btn-pri" style="margin-top:auto;">Filtrar</button>
      <a href="<?= url('empresa/candidaturas.php') ?>" class="btn btn-sm btn-outline-secondary" style="margin-top:auto;">Limpar</a>
    </form>
  </div>
</div>

<?php if(empty($candidaturas)): ?>
<div class="text-center py-5">
  <div style="font-size:2.5rem;margin-bottom:.5rem;">📬</div>
  <h5>Nenhuma candidatura encontrada</h5>
  <p style="color:#6c757d;">Publica vagas para começar a receber candidatos.</p>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
  <?php foreach($candidaturas as $c): [$lbl_e,$cls_e]=estadoCandidaturaLabel($c['estado']); ?>
  <div class="p-card">
    <div class="p-card-body">
      <div class="d-flex align-items-start gap-3 flex-wrap">
        <?php if($c['foto']): ?>
          <img src="<?= url('uploads/fotos/'.h($c['foto'])) ?>" class="avatar-md flex-shrink-0">
        <?php else: ?>
          <div class="avatar-md d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="background:var(--sec);font-size:1rem;"><?= mb_strtoupper(mb_substr($c['cn'],0,1)) ?></div>
        <?php endif; ?>
        <div class="flex-grow-1 min-w-0">
          <div class="d-flex justify-content-between flex-wrap gap-2 align-items-start">
            <div>
              <div class="fw-bold" style="font-size:1rem;color:var(--pri);"><?= h($c['cn']) ?></div>
              <?php if($c['tp']): ?><div class="text-muted-sm"><?= h($c['tp']) ?></div><?php endif; ?>
              <div class="text-muted-sm mt-1">
                <strong><?= h($c['vt']) ?></strong> · <?= tempo($c['data_candidatura']) ?>
                <?php if($c['prov']): ?> · <?= h($c['prov']) ?><?php endif; ?>
              </div>
            </div>
            <span class="badge bg-<?= $cls_e ?> flex-shrink-0"><?= $lbl_e ?></span>
          </div>

          <div class="d-flex gap-2 mt-2 flex-wrap">
            <?php if($c['cv_ficheiro']): ?>
            <a href="<?= url('uploads/cvs/'.h($c['cv_ficheiro'])) ?>" target="_blank" class="btn btn-sm btn-outline-primary py-1">
              <i data-feather="download" style="width:13px;height:13px;" class="me-1"></i>Download CV
            </a>
            <?php endif; ?>
            <?php if($c['linkedin']): ?><a href="<?= h($c['linkedin']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary py-1">LinkedIn</a><?php endif; ?>
            <?php if($c['portfolio']): ?><a href="<?= h($c['portfolio']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary py-1">Portfólio</a><?php endif; ?>
          </div>

          <?php if($c['carta_apresentacao']): ?>
          <div class="mt-2 p-3 rounded" style="background:#f8f9fc;font-size:.85rem;line-height:1.65;max-height:100px;overflow-y:auto;">
            <?= nl2br(h($c['carta_apresentacao'])) ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="border-top mt-3 pt-3">
        <form method="POST" class="row g-2 align-items-end">
          <input type="hidden" name="upd" value="1">
          <input type="hidden" name="cid" value="<?= $c['id'] ?>">
          <div class="col-sm-4">
            <label class="form-label" style="font-size:.8rem;">Actualizar Estado</label>
            <select name="est" class="form-select form-select-sm">
              <?php foreach(['enviada'=>'Enviada','vista'=>'Vista','em_analise'=>'Em Análise','entrevista'=>'Entrevista','oferta'=>'Oferta','aceite'=>'Aceite','rejeitada'=>'Rejeitada'] as $k=>$l): ?>
              <option value="<?= $k ?>" <?= $c['estado']===$k?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-5">
            <label class="form-label" style="font-size:.8rem;">Nota Interna</label>
            <input type="text" name="nota" class="form-control form-control-sm" placeholder="Nota privada..." value="<?= h($c['nota_empresa']??'') ?>">
          </div>
          <div class="col-sm-3">
            <button type="submit" class="btn btn-sm btn-pri w-100">
              <i data-feather="save" style="width:13px;height:13px;" class="me-1"></i>Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/footer_painel.php'; ?>
