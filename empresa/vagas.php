<?php
require_once '../includes/config.php';
requireAuth('empresa');
$cfg=$allCfg=allCfg(); $emp=meEmpresa();
if(!$emp) redirect('logout.php');
$titulo_pag='Minhas Vagas';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $acao=$_POST['acao']??''; $id=(int)($_POST['id']??0);
    if($id && DB::row("SELECT id FROM vagas WHERE id=? AND empresa_id=?",[$id,$emp['id']])){
        if($acao==='encerrar') DB::exec("UPDATE vagas SET estado='encerrada' WHERE id=?",[$id]);
        if($acao==='apagar')   DB::exec("DELETE FROM vagas WHERE id=? AND empresa_id=?",[$id,$emp['id']]);
        redirect('empresa/vagas.php','Acção efectuada!');
    }
}

$vagas=DB::all("SELECT v.*,COUNT(c.id) as tc FROM vagas v LEFT JOIN candidaturas c ON c.vaga_id=v.id WHERE v.empresa_id=? GROUP BY v.id ORDER BY v.criado_em DESC",[$emp['id']]);
require_once '../includes/header_painel.php';
?>

<div class="d-flex justify-content-end mb-3">
  <a href="<?= url('empresa/nova_vaga.php') ?>" class="btn btn-ace"><i data-feather="plus" style="width:14px;height:14px;" class="me-1"></i>Nova Vaga</a>
</div>

<div class="p-card">
  <div class="table-responsive">
    <table class="t-emp mb-0">
      <thead><tr><th>Título</th><th>Tipo</th><th class="text-center">Cand.</th><th class="text-center">Visitas</th><th>Data</th><th>Estado</th><th width="120">Ações</th></tr></thead>
      <tbody>
        <?php if(empty($vagas)): ?>
        <tr><td colspan="7" class="text-center py-5">
          <div style="font-size:2.5rem;margin-bottom:.5rem;">📋</div>
          <div class="fw-600">Nenhuma vaga publicada ainda</div>
          <a href="<?= url('empresa/nova_vaga.php') ?>" class="btn btn-sm btn-ace mt-2">Publicar Primeira Vaga</a>
        </td></tr>
        <?php else: foreach($vagas as $v):
          $cls=match($v['estado']){'publicada'=>'b-pub','pendente'=>'b-pend','encerrada'=>'b-enc',default=>'b-enc'};
          $lbl=match($v['estado']){'publicada'=>'Publicada','pendente'=>'Pendente rev.','encerrada'=>'Encerrada','rascunho'=>'Rascunho',default=>ucfirst($v['estado'])};
        ?>
        <tr>
          <td>
            <div class="fw-600" style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($v['titulo']) ?></div>
            <?php if($v['destaque']): ?><span style="font-size:.7rem;color:#d97706;">⭐ Destaque</span><?php endif; ?>
          </td>
          <td><span class="badge-contrato" style="font-size:.72rem;"><?= labelContrato($v['tipo_contrato']) ?></span></td>
          <td class="text-center"><span class="badge bg-primary"><?= $v['tc'] ?></span></td>
          <td class="text-center text-muted-sm"><?= $v['total_visualizacoes'] ?></td>
          <td class="text-muted-sm"><?= tempo($v['criado_em']) ?></td>
          <td><span class="b-status <?= $cls ?>"><?= $lbl ?></span></td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= url('empresa/nova_vaga.php?id='.$v['id']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2"><i data-feather="edit-2" style="width:12px;height:12px;"></i></a>
              <a href="<?= url('empresa/candidaturas.php?vaga='.$v['id']) ?>" class="btn btn-sm btn-outline-secondary py-0 px-2"><i data-feather="users" style="width:12px;height:12px;"></i></a>
              <?php if($v['estado']==='publicada'): ?>
              <form method="POST" class="d-inline"><input type="hidden" name="acao" value="encerrar"><input type="hidden" name="id" value="<?= $v['id'] ?>"><button class="btn btn-sm btn-outline-warning py-0 px-2"><i data-feather="square" style="width:12px;height:12px;"></i></button></form>
              <?php elseif($v['estado']==='rascunho'): ?>
              <form method="POST" class="d-inline" onsubmit="return confirm('Apagar?')"><input type="hidden" name="acao" value="apagar"><input type="hidden" name="id" value="<?= $v['id'] ?>"><button class="btn btn-sm btn-outline-danger py-0 px-2"><i data-feather="trash-2" style="width:12px;height:12px;"></i></button></form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer_painel.php'; ?>
