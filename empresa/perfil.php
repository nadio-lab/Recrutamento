<?php
require_once '../includes/config.php';
requireAuth('empresa');
$cfg=allCfg(); $emp=meEmpresa();
if(!$emp) redirect('logout.php');
$titulo_pag='Perfil da Empresa';
$provs=DB::all("SELECT * FROM provincias ORDER BY nome");
$erro='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $nome   =trim($_POST['nome']??'');
    $logo_n =$emp['logo'];
    if(!empty($_FILES['logo']['name'])){
        $res=uploadFicheiro($_FILES['logo'],'logos',['jpg','jpeg','png','webp','svg'],2);
        if($res['ok']){
            if($emp['logo']&&file_exists(UPLOAD_DIR.'logos/'.$emp['logo'])) @unlink(UPLOAD_DIR.'logos/'.$emp['logo']);
            $logo_n=$res['nome'];
        } else $erro=$res['msg'];
    }
    if(!$erro&&$nome){
        DB::exec("UPDATE empresas SET nome=?,nif=?,website=?,telefone=?,email_contato=?,provincia_id=?,morada=?,setor=?,dimensao=?,ano_fundacao=?,sobre=?,logo=? WHERE id=?",
            [$nome,trim($_POST['nif']??''),trim($_POST['web']??''),trim($_POST['tel']??''),trim($_POST['email_c']??''),
             $_POST['prov']?:null,trim($_POST['morada']??''),trim($_POST['setor']??''),$_POST['dim']??'media',
             $_POST['ano']?:null,trim($_POST['sobre']??''),$logo_n,$emp['id']]);
        redirect('empresa/perfil.php','Perfil actualizado!');
    } else if(!$nome) $erro='O nome da empresa é obrigatório.';
    $emp=meEmpresa();
}

require_once '../includes/header_painel.php';
?>

<?php if($erro): ?><div class="alert alert-danger"><?= h($erro) ?></div><?php endif; ?>

<div class="d-flex justify-content-end mb-3">
  <a href="<?= url('empresa.php?id='.$emp['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Ver Perfil Público</a>
</div>

<form method="POST" enctype="multipart/form-data">
<div class="row g-3">
  <div class="col-lg-3">
    <div class="p-card text-center">
      <div class="p-card-body">
        <?php if($emp['logo']): ?>
          <img src="<?= url('uploads/logos/'.h($emp['logo'])) ?>" style="width:90px;height:90px;border-radius:14px;object-fit:contain;background:#f8f9fc;padding:6px;margin:0 auto .75rem;display:block;">
        <?php else: ?>
          <div style="width:90px;height:90px;border-radius:14px;background:linear-gradient(135deg,var(--pri),var(--sec));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:2rem;margin:0 auto .75rem;"><?= mb_strtoupper(mb_substr($emp['nome'],0,1)) ?></div>
        <?php endif; ?>
        <label class="form-label">Logo da Empresa</label>
        <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
        <div class="text-muted-sm mt-1">JPG, PNG, SVG · Máx. 2MB</div>
        <div class="mt-2">
          <span class="b-status <?= match($emp['estado']){'aprovada'=>'b-aprov','pendente'=>'b-pend',default=>'b-susp'} ?>"><?= ucfirst($emp['estado']) ?></span>
          <?php if($emp['verificada']): ?><div style="font-size:.72rem;color:#2d6a4f;margin-top:.3rem;">✓ Verificada</div><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-9">
    <div class="p-card">
      <div class="p-card-header">Dados da Empresa</div>
      <div class="p-card-body">
        <div class="row g-3">
          <div class="col-md-8"><label class="form-label">Nome da Empresa *</label><input type="text" name="nome" class="form-control" required value="<?= h($emp['nome']) ?>"></div>
          <div class="col-md-4"><label class="form-label">NIF</label><input type="text" name="nif" class="form-control" value="<?= h($emp['nif']??'') ?>"></div>
          <div class="col-md-6"><label class="form-label">Website</label><input type="url" name="web" class="form-control" placeholder="https://..." value="<?= h($emp['website']??'') ?>"></div>
          <div class="col-md-6"><label class="form-label">Email de Contacto</label><input type="email" name="email_c" class="form-control" value="<?= h($emp['email_contato']??'') ?>"></div>
          <div class="col-md-6"><label class="form-label">Telefone</label><input type="text" name="tel" class="form-control" value="<?= h($emp['telefone']??'') ?>"></div>
          <div class="col-md-6">
            <label class="form-label">Província</label>
            <select name="prov" class="form-select">
              <option value="">-- Selecionar --</option>
              <?php foreach($provs as $p): ?><option value="<?= $p['id'] ?>" <?= ($emp['provincia_id']??'')==$p['id']?'selected':'' ?>><?= h($p['nome']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-5"><label class="form-label">Sector</label><input type="text" name="setor" class="form-control" placeholder="Tecnologia, Banca..." value="<?= h($emp['setor']??'') ?>"></div>
          <div class="col-md-4">
            <label class="form-label">Dimensão</label>
            <select name="dim" class="form-select">
              <?php foreach(['startup'=>'Startup','pequena'=>'Pequena','media'=>'Média','grande'=>'Grande','multinacional'=>'Multinacional'] as $k=>$l): ?>
              <option value="<?= $k ?>" <?= ($emp['dimensao']??'media')===$k?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3"><label class="form-label">Ano Fundação</label><input type="number" name="ano" class="form-control" min="1900" max="<?= date('Y') ?>" value="<?= h($emp['ano_fundacao']??'') ?>"></div>
          <div class="col-12"><label class="form-label">Morada</label><input type="text" name="morada" class="form-control" value="<?= h($emp['morada']??'') ?>"></div>
          <div class="col-12"><label class="form-label">Sobre a Empresa</label><textarea name="sobre" class="form-control" rows="5" placeholder="Missão, visão, cultura..."><?= h($emp['sobre']??'') ?></textarea></div>
          <div class="col-12 d-flex justify-content-end gap-2">
            <a href="<?= url('empresa/index.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-ace fw-bold"><i data-feather="save" style="width:14px;height:14px;" class="me-1"></i>Guardar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</form>

<?php require_once '../includes/footer_painel.php'; ?>
