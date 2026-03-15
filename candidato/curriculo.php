<?php
require_once '../includes/config.php';
requireAuth('candidato');
$cfg=allCfg(); $cand=meCandidato();
if(!$cand) redirect('logout.php');
$titulo_pag='Currículo';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $s=$_POST['s']??'';
    if($s==='add_edu')   {DB::insert("INSERT INTO candidato_educacao(candidato_id,instituicao,curso,nivel,data_inicio,data_fim,em_curso,descricao) VALUES(?,?,?,?,?,?,?,?)",[$cand['id'],trim($_POST['inst']),trim($_POST['curso']),$_POST['nivel'],$_POST['di']?:null,$_POST['df']?:null,(int)($_POST['ec']??0),trim($_POST['desc']??'')]); redirect('candidato/curriculo.php','Educação adicionada!');}
    if($s==='del_edu')   {DB::exec("DELETE FROM candidato_educacao WHERE id=? AND candidato_id=?",[(int)$_POST['id'],$cand['id']]); redirect('candidato/curriculo.php','Removido.');}
    if($s==='add_exp')   {DB::insert("INSERT INTO candidato_experiencia(candidato_id,empresa,cargo,data_inicio,data_fim,atual,descricao) VALUES(?,?,?,?,?,?,?)",[$cand['id'],trim($_POST['emp']),trim($_POST['cargo']),$_POST['ei']?:null,$_POST['ef']?:null,(int)($_POST['atual']??0),trim($_POST['desc']??'')]); redirect('candidato/curriculo.php','Experiência adicionada!');}
    if($s==='del_exp')   {DB::exec("DELETE FROM candidato_experiencia WHERE id=? AND candidato_id=?",[(int)$_POST['id'],$cand['id']]); redirect('candidato/curriculo.php','Removido.');}
    if($s==='add_comp')  {DB::insert("INSERT INTO candidato_competencias(candidato_id,nome,nivel) VALUES(?,?,?)",[$cand['id'],trim($_POST['nome']),$_POST['nivel']]); redirect('candidato/curriculo.php','Competência adicionada!');}
    if($s==='del_comp')  {DB::exec("DELETE FROM candidato_competencias WHERE id=? AND candidato_id=?",[(int)$_POST['id'],$cand['id']]); redirect('candidato/curriculo.php','Removido.');}
}

$edu  =DB::all("SELECT * FROM candidato_educacao    WHERE candidato_id=? ORDER BY data_inicio DESC",[$cand['id']]);
$exp  =DB::all("SELECT * FROM candidato_experiencia WHERE candidato_id=? ORDER BY COALESCE(data_fim,'9999-99-99') DESC",[$cand['id']]);
$comps=DB::all("SELECT * FROM candidato_competencias WHERE candidato_id=? ORDER BY nome",[$cand['id']]);
$pct_nivel=['basico'=>25,'intermedio'=>50,'avancado'=>75,'especialista'=>100];
$lbl_nivel=['basico'=>'Básico','intermedio'=>'Intermédio','avancado'=>'Avançado','especialista'=>'Especialista'];

require_once '../includes/header_painel.php';
?>

<!-- EDUCAÇÃO -->
<div class="p-card mb-4">
  <div class="p-card-header d-flex justify-content-between align-items-center">
    <span><i data-feather="book-open" style="width:15px;height:15px;" class="me-1"></i>Formação Académica</span>
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#fEdu">+ Adicionar</button>
  </div>
  <div class="collapse" id="fEdu">
    <div class="p-3 border-bottom" style="background:#f8f9fc;">
      <form method="POST"><input type="hidden" name="s" value="add_edu">
        <div class="row g-2">
          <div class="col-md-5"><label class="form-label">Instituição *</label><input type="text" name="inst" class="form-control form-control-sm" required placeholder="Universidade..."></div>
          <div class="col-md-5"><label class="form-label">Curso *</label><input type="text" name="curso" class="form-control form-control-sm" required placeholder="Engenharia Informática..."></div>
          <div class="col-md-2"><label class="form-label">Nível</label>
            <select name="nivel" class="form-select form-select-sm">
              <?php foreach(['basico'=>'Básico','medio'=>'Médio','bacharelato'=>'Bacharelato','licenciatura'=>'Licenciatura','mestrado'=>'Mestrado','doutoramento'=>'Doutoramento','outro'=>'Outro'] as $k=>$l): ?>
              <option value="<?= $k ?>"><?= $l ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2"><label class="form-label">Início</label><input type="number" name="di" class="form-control form-control-sm" min="1970" max="<?= date('Y') ?>" placeholder="2020"></div>
          <div class="col-md-2"><label class="form-label">Fim</label><input type="number" name="df" class="form-control form-control-sm" min="1970" max="<?= date('Y')+5 ?>" placeholder="2024"></div>
          <div class="col-md-2 d-flex align-items-end"><div class="form-check mb-2"><input type="checkbox" name="ec" value="1" class="form-check-input" id="ec"><label for="ec" class="form-check-label" style="font-size:.85rem;">Em curso</label></div></div>
          <div class="col-md-6"><label class="form-label">Notas / Prémios</label><input type="text" name="desc" class="form-control form-control-sm" placeholder="Ex: Média 17, Prémio de mérito..."></div>
          <div class="col-12"><button type="submit" class="btn btn-sm btn-pri">Adicionar</button></div>
        </div>
      </form>
    </div>
  </div>
  <div class="p-card-body p-0">
    <?php if(empty($edu)): ?><div class="px-3 py-2 text-muted-sm">Nenhuma formação adicionada ainda.</div>
    <?php else: foreach($edu as $e): ?>
    <div class="d-flex justify-content-between align-items-start px-3 py-2 border-bottom">
      <div>
        <div class="fw-600"><?= h($e['curso']) ?></div>
        <div class="text-muted-sm"><?= h($e['instituicao']) ?> · <?= ucfirst(str_replace(['_','-'],' ',$e['nivel'])) ?></div>
        <div class="text-muted-sm"><?= $e['data_inicio']??'?' ?> – <?= $e['em_curso']?'Presente':($e['data_fim']??'?') ?></div>
      </div>
      <form method="POST" class="d-inline"><input type="hidden" name="s" value="del_edu"><input type="hidden" name="id" value="<?= $e['id'] ?>"><button class="btn btn-sm btn-outline-danger py-0 px-2"><i data-feather="trash-2" style="width:12px;height:12px;"></i></button></form>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- EXPERIÊNCIA -->
<div class="p-card mb-4">
  <div class="p-card-header d-flex justify-content-between align-items-center">
    <span><i data-feather="briefcase" style="width:15px;height:15px;" class="me-1"></i>Experiência Profissional</span>
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#fExp">+ Adicionar</button>
  </div>
  <div class="collapse" id="fExp">
    <div class="p-3 border-bottom" style="background:#f8f9fc;">
      <form method="POST"><input type="hidden" name="s" value="add_exp">
        <div class="row g-2">
          <div class="col-md-5"><label class="form-label">Empresa *</label><input type="text" name="emp" class="form-control form-control-sm" required placeholder="Nome da empresa"></div>
          <div class="col-md-5"><label class="form-label">Cargo *</label><input type="text" name="cargo" class="form-control form-control-sm" required placeholder="Desenvolvedor, Gestor..."></div>
          <div class="col-md-3"><label class="form-label">Início</label><input type="date" name="ei" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Fim</label><input type="date" name="ef" class="form-control form-control-sm"></div>
          <div class="col-md-3 d-flex align-items-end"><div class="form-check mb-2"><input type="checkbox" name="atual" value="1" class="form-check-input" id="ea"><label for="ea" class="form-check-label" style="font-size:.85rem;">Emprego actual</label></div></div>
          <div class="col-md-9"><label class="form-label">Descrição das funções</label><textarea name="desc" class="form-control form-control-sm" rows="2" placeholder="Responsabilidades, realizações..."></textarea></div>
          <div class="col-12"><button type="submit" class="btn btn-sm btn-pri">Adicionar</button></div>
        </div>
      </form>
    </div>
  </div>
  <div class="p-card-body p-0">
    <?php if(empty($exp)): ?><div class="px-3 py-2 text-muted-sm">Nenhuma experiência adicionada ainda.</div>
    <?php else: foreach($exp as $e): ?>
    <div class="d-flex justify-content-between align-items-start px-3 py-2 border-bottom">
      <div>
        <div class="fw-600"><?= h($e['cargo']) ?></div>
        <div class="text-muted-sm"><?= h($e['empresa']) ?></div>
        <div class="text-muted-sm"><?= ($e['data_inicio']?date('M/Y',strtotime($e['data_inicio'])):'?') ?> – <?= $e['atual']?'Presente':($e['data_fim']?date('M/Y',strtotime($e['data_fim'])):'?') ?></div>
        <?php if($e['descricao']): ?><div class="text-muted-sm mt-1" style="max-width:400px;"><?= h(mb_substr($e['descricao'],0,120)) ?><?= mb_strlen($e['descricao'])>120?'...':'' ?></div><?php endif; ?>
      </div>
      <form method="POST" class="d-inline"><input type="hidden" name="s" value="del_exp"><input type="hidden" name="id" value="<?= $e['id'] ?>"><button class="btn btn-sm btn-outline-danger py-0 px-2"><i data-feather="trash-2" style="width:12px;height:12px;"></i></button></form>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- COMPETÊNCIAS -->
<div class="p-card">
  <div class="p-card-header d-flex justify-content-between align-items-center">
    <span><i data-feather="zap" style="width:15px;height:15px;" class="me-1"></i>Competências</span>
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#fComp">+ Adicionar</button>
  </div>
  <div class="collapse" id="fComp">
    <div class="p-3 border-bottom" style="background:#f8f9fc;">
      <form method="POST" class="d-flex gap-2 align-items-end flex-wrap"><input type="hidden" name="s" value="add_comp">
        <div><label class="form-label">Competência</label><input type="text" name="nome" class="form-control form-control-sm" required placeholder="PHP, Excel, Inglês..." style="width:200px;"></div>
        <div><label class="form-label">Nível</label>
          <select name="nivel" class="form-select form-select-sm">
            <option value="basico">Básico</option><option value="intermedio">Intermédio</option>
            <option value="avancado">Avançado</option><option value="especialista">Especialista</option>
          </select>
        </div>
        <button type="submit" class="btn btn-sm btn-pri" style="margin-top:auto;">Adicionar</button>
      </form>
    </div>
  </div>
  <div class="p-card-body">
    <?php if(empty($comps)): ?><div class="text-muted-sm">Nenhuma competência adicionada ainda.</div>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach($comps as $c): ?>
      <div class="col-md-6">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="fw-600" style="font-size:.88rem;"><?= h($c['nome']) ?></span>
          <div class="d-flex align-items-center gap-2">
            <span class="text-muted-sm"><?= $lbl_nivel[$c['nivel']]??$c['nivel'] ?></span>
            <form method="POST" class="d-inline"><input type="hidden" name="s" value="del_comp"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn btn-sm py-0 px-1 border-0" style="color:#dc2626;"><i data-feather="x" style="width:12px;height:12px;"></i></button></form>
          </div>
        </div>
        <div style="height:5px;background:#f0f2f5;border-radius:3px;overflow:hidden;">
          <div style="height:100%;background:var(--ace);width:<?= $pct_nivel[$c['nivel']]??50 ?>%;border-radius:3px;"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/footer_painel.php'; ?>
