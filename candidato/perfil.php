<?php
require_once '../includes/config.php';
requireAuth('candidato');
$cfg=allCfg(); $cand=meCandidato();
if(!$cand) redirect('logout.php');
$titulo_pag='Meu Perfil';
$provs=DB::all("SELECT * FROM provincias ORDER BY nome");
$erro='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $sec=$_POST['sec']??'';
    if($sec==='perfil'){
        $foto_n=$cand['foto'];
        if(!empty($_FILES['foto']['name'])){
            $r=uploadFicheiro($_FILES['foto'],'fotos',['jpg','jpeg','png','webp'],2);
            if($r['ok']){if($cand['foto']&&file_exists(UPLOAD_DIR.'fotos/'.$cand['foto'])) @unlink(UPLOAD_DIR.'fotos/'.$cand['foto']); $foto_n=$r['nome'];}
            else $erro=$r['msg'];
        }
        $cv_n=$cand['cv_ficheiro'];
        if(!empty($_FILES['cv']['name'])){
            $r=uploadFicheiro($_FILES['cv'],'cvs',['pdf','doc','docx'],5);
            if($r['ok']){if($cand['cv_ficheiro']&&file_exists(UPLOAD_DIR.'cvs/'.$cand['cv_ficheiro'])) @unlink(UPLOAD_DIR.'cvs/'.$cand['cv_ficheiro']); $cv_n=$r['nome'];}
            else $erro=$r['msg'];
        }
        if(!$erro){
            DB::exec("UPDATE utilizadores SET nome=? WHERE id=?",[trim($_POST['nome']),$cand['utilizador_id']]);
            DB::exec("UPDATE candidatos SET titulo_profissional=?,sobre=?,telefone=?,data_nascimento=?,genero=?,provincia_id=?,morada=?,linkedin=?,portfolio=?,disponibilidade=?,salario_pretendido=?,moeda_salario=?,foto=?,cv_ficheiro=? WHERE id=?",
                [trim($_POST['titulo']??''),trim($_POST['sobre']??''),trim($_POST['tel']??''),$_POST['dnasc']?:null,$_POST['genero']??'M',$_POST['prov']?:null,trim($_POST['morada']??''),trim($_POST['linkedin']??''),trim($_POST['portf']??''),$_POST['disp']??'imediata',$_POST['sal']!==''?(float)$_POST['sal']:null,$_POST['moeda']??'AOA',$foto_n,$cv_n,$cand['id']]);
            redirect('candidato/perfil.php','Perfil actualizado!');
        }
    }
    if($sec==='senha'){
        $user=DB::row("SELECT * FROM utilizadores WHERE id=?",[$cand['utilizador_id']]);
        if(!password_verify($_POST['atual']??'',$user['password'])) $erro='Senha actual incorrecta.';
        elseif(strlen($_POST['nova']??'')<8) $erro='Nova senha deve ter pelo menos 8 caracteres.';
        elseif($_POST['nova']!=$_POST['conf']) $erro='As senhas não coincidem.';
        else{DB::exec("UPDATE utilizadores SET password=? WHERE id=?",[password_hash($_POST['nova'],PASSWORD_DEFAULT),$cand['utilizador_id']]); redirect('candidato/perfil.php','Senha alterada!');}
    }
    $cand=meCandidato();
}

require_once '../includes/header_painel.php';
?>

<?php if($erro): ?><div class="alert alert-danger"><?= h($erro) ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="sec" value="perfil">
<div class="row g-3">
  <div class="col-lg-3">
    <div class="p-card mb-3 text-center">
      <div class="p-card-body">
        <?php if($cand['foto']): ?>
          <img src="<?= url('uploads/fotos/'.h($cand['foto'])) ?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;margin:0 auto .75rem;display:block;">
        <?php else: ?>
          <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--pri),var(--sec));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:2rem;margin:0 auto .75rem;"><?= mb_strtoupper(mb_substr($cand['nome'],0,1)) ?>
            
          </div>
        <?php endif; ?>
        <label class="form-label">Foto de Perfil</label>
        <input type="file" name="foto" class="form-control form-control-sm mb-3" accept="image/*">
        <div class="divider"></div>
        <label class="form-label">Currículo (CV)</label>
        <?php if($cand['cv_ficheiro']): ?>
        <div class="d-flex align-items-center justify-content-center gap-1 mb-2" style="font-size:.82rem;color:#2d6a4f;background:#f0fdf4;padding:.4rem;border-radius:6px;">
          <i data-feather="check-circle" style="width:13px;height:13px;"></i>CV carregado
          <a href="<?= url('uploads/cvs/'.h($cand['cv_ficheiro'])) ?>" target="_blank" style="color:var(--sec);font-size:.75rem;">Ver</a>
        </div>
        <?php endif; ?>
        <input type="file" name="cv" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
        <div class="text-muted-sm mt-1">PDF, DOC · Máx. 5MB</div>
             
      </div>
    </div>
    <div class="p-card">
      <div class="p-card-header">Alterar Senha</div>
      <div class="p-card-body">
        <form method="POST">
          <input type="hidden" name="sec" value="senha">
          <div class="mb-2"><label class="form-label">Senha Actual</label><input type="password" name="atual" class="form-control form-control-sm" required></div>
          <div class="mb-2"><label class="form-label">Nova Senha</label><input type="password" name="nova" class="form-control form-control-sm" required minlength="8"></div>
          <div class="mb-3"><label class="form-label">Confirmar</label><input type="password" name="conf" class="form-control form-control-sm" required></div>
          <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Alterar</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-9">
    <div class="p-card">
      <div class="p-card-header">Dados Pessoais e Profissionais</div>
      <div class="p-card-body">
        <div class="row g-3">
          <div class="col-md-8"><label class="form-label">Nome Completo</label><input type="text" name="nome" class="form-control" value="<?= h($cand['nome']) ?>"></div>
          <div class="col-md-4"><label class="form-label">Género</label>
            <select name="genero" class="form-select">
              <option value="M" <?= ($cand['genero']??'M')==='M'?'selected':'' ?>>Masculino</option>
              <option value="F" <?= ($cand['genero']??'')==='F'?'selected':'' ?>>Feminino</option>
              <option value="outro" <?= ($cand['genero']??'')==='outro'?'selected':'' ?>>Outro</option>
            </select>
          </div>
          <div class="col-12"><label class="form-label">Título Profissional</label><input type="text" name="titulo" class="form-control" placeholder="Ex: Engenheiro de Software · Designer UX" value="<?= h($cand['titulo_profissional']??'') ?>"></div>
          <div class="col-12"><label class="form-label">Sobre Mim</label><textarea name="sobre" class="form-control" rows="4" placeholder="Apresenta-te de forma profissional..."><?= h($cand['sobre']??'') ?></textarea></div>
          <div class="col-md-4"><label class="form-label">Telefone</label><input type="text" name="tel" class="form-control" value="<?= h($cand['telefone']??'') ?>"></div>
          <div class="col-md-4"><label class="form-label">Data de Nascimento</label><input type="date" name="dnasc" class="form-control" value="<?= h($cand['data_nascimento']??'') ?>"></div>
          <div class="col-md-4">
            <label class="form-label">Província</label>
            <select name="prov" class="form-select">
              <option value="">--</option>
              <?php foreach($provs as $p): ?><option value="<?= $p['id'] ?>" <?= ($cand['provincia_id']??'')==$p['id']?'selected':'' ?>><?= h($p['nome']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-12"><label class="form-label">Morada</label><input type="text" name="morada" class="form-control" value="<?= h($cand['morada']??'') ?>"></div>
          <div class="col-md-6"><label class="form-label">LinkedIn</label><input type="url" name="linkedin" class="form-control" placeholder="https://linkedin.com/in/..." value="<?= h($cand['linkedin']??'') ?>"></div>
          <div class="col-md-6"><label class="form-label">Portfólio / Website</label><input type="url" name="portf" class="form-control" placeholder="https://..." value="<?= h($cand['portfolio']??'') ?>"></div>
          <div class="col-md-4">
            <label class="form-label">Disponibilidade</label>
            <select name="disp" class="form-select">
              <option value="imediata"  <?= ($cand['disponibilidade']??'imediata')==='imediata'?'selected':'' ?>>Imediata</option>
              <option value="1_mes"     <?= ($cand['disponibilidade']??'')==='1_mes'?'selected':'' ?>>1 Mês</option>
              <option value="3_meses"   <?= ($cand['disponibilidade']??'')==='3_meses'?'selected':'' ?>>3 Meses</option>
              <option value="empregado" <?= ($cand['disponibilidade']??'')==='empregado'?'selected':'' ?>>Empregado (aberto a ofertas)</option>
            </select>
          </div>
          <div class="col-md-4"><label class="form-label">Pretensão Salarial</label><input type="number" name="sal" class="form-control" step="1000" value="<?= h($cand['salario_pretendido']??'') ?>"></div>
          <div class="col-md-4">
            <label class="form-label">Moeda</label>
            <select name="moeda" class="form-select">
              <option value="AOA" <?= ($cand['moeda_salario']??'AOA')==='AOA'?'selected':'' ?>>AOA (Kwanza)</option>
              <option value="USD" <?= ($cand['moeda_salario']??'')==='USD'?'selected':'' ?>>USD</option>
              <option value="EUR" <?= ($cand['moeda_salario']??'')==='EUR'?'selected':'' ?>>EUR</option>
            </select>
          </div>
          <div class="col-12 d-flex justify-content-end gap-2">
            <a href="<?= url('candidato/index.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-ace fw-bold"><i data-feather="save" style="width:14px;height:14px;" class="me-1"></i>Guardar Perfil</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</form>

<?php require_once '../includes/footer_painel.php'; ?>
