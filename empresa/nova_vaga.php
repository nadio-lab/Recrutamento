<?php
require_once '../includes/config.php';
requireAuth('empresa');
$cfg = allCfg();
$emp = meEmpresa();
if (!$emp || $emp['estado'] !== 'aprovada') {
    redirect('empresa/index.php', 'Empresa precisa de aprovação para publicar vagas.', 'aviso');
}

$cats   = DB::all("SELECT * FROM categorias WHERE ativo=1 ORDER BY nome");
$provs  = DB::all("SELECT * FROM provincias ORDER BY nome");
$id_edit= (int)($_GET['id'] ?? 0);
$vaga   = null;
if ($id_edit) {
    $vaga = DB::row("SELECT * FROM vagas WHERE id=? AND empresa_id=?", [$id_edit, $emp['id']]);
    if (!$vaga) redirect('empresa/vagas.php', 'Vaga não encontrada.', 'erro');
}
$titulo_pag = $vaga ? 'Editar Vaga' : 'Publicar Nova Vaga';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = trim($_POST['titulo'] ?? '');
    $cat       = (int)($_POST['cat'] ?? 0);
    $prov      = (int)($_POST['prov'] ?? 0);
    $contrato  = $_POST['contrato']  ?? 'efectivo';
    $modal     = $_POST['modal']     ?? 'presencial';
    $exp       = $_POST['exp']       ?? 'medio';
    $escol     = $_POST['escol']     ?? 'licenciatura';
    $descricao = trim($_POST['descricao'] ?? '');
    $requisitos= trim($_POST['requisitos'] ?? '');
    $beneficios= trim($_POST['beneficios'] ?? '');
    $sal_min   = $_POST['sal_min'] !== '' ? (float)$_POST['sal_min'] : null;
    $sal_max   = $_POST['sal_max'] !== '' ? (float)$_POST['sal_max'] : null;
    $sal_vis   = (int)($_POST['sal_vis'] ?? 1);
    $sal_neg   = (int)($_POST['sal_neg'] ?? 0);
    $moeda     = $_POST['moeda'] ?? 'AOA';
    $enc       = $_POST['enc'] !== '' ? $_POST['enc'] : null;
    $n_vagas   = max(1,(int)($_POST['n_vagas'] ?? 1));
    $pub_acao  = $_POST['pub_acao'] ?? 'publicar';
    $estado    = $pub_acao === 'rascunho' ? 'rascunho' : 'pendente';

    if (!$titulo)    $erro = 'O título é obrigatório.';
    elseif (!$descricao) $erro = 'A descrição é obrigatória.';
    else {
        if ($vaga) {
            DB::exec("UPDATE vagas SET titulo=?,categoria_id=?,provincia_id=?,tipo_contrato=?,modalidade=?,
                nivel_experiencia=?,nivel_escolaridade=?,descricao=?,requisitos=?,beneficios=?,
                salario_min=?,salario_max=?,moeda_salario=?,salario_visivel=?,salario_negociavel=?,
                data_encerramento=?,vagas_disponiveis=?,estado=? WHERE id=?",
                [$titulo,$cat?:null,$prov?:null,$contrato,$modal,$exp,$escol,$descricao,$requisitos,
                 $beneficios,$sal_min,$sal_max,$moeda,$sal_vis,$sal_neg,$enc,$n_vagas,$estado,$vaga['id']]);
            redirect('empresa/vagas.php', 'Vaga actualizada!');
        } else {
            $slug = slugUnico('vagas', $titulo);
            DB::insert("INSERT INTO vagas (empresa_id,titulo,slug,categoria_id,provincia_id,tipo_contrato,modalidade,
                nivel_experiencia,nivel_escolaridade,descricao,requisitos,beneficios,salario_min,salario_max,
                moeda_salario,salario_visivel,salario_negociavel,data_encerramento,vagas_disponiveis,estado)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$emp['id'],$titulo,$slug,$cat?:null,$prov?:null,$contrato,$modal,$exp,$escol,$descricao,
                 $requisitos,$beneficios,$sal_min,$sal_max,$moeda,$sal_vis,$sal_neg,$enc,$n_vagas,$estado]);
            DB::exec("UPDATE empresas SET total_vagas_publicadas=total_vagas_publicadas+1 WHERE id=?",[$emp['id']]);
            $msg = $estado==='rascunho' ? 'Vaga guardada como rascunho.' : 'Vaga submetida para aprovação!';
            redirect('empresa/vagas.php', $msg);
        }
    }
}
$v = $vaga ?? [];

require_once '../includes/header_painel.php';
?>

<?php if ($erro): ?><div class="alert alert-danger"><?= h($erro) ?></div><?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div></div>
  <a href="<?= url('empresa/vagas.php') ?>" class="btn btn-sm btn-outline-secondary">← Voltar às vagas</a>
</div>

<form method="POST">
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="p-card mb-3">
        <div class="p-card-header">Informação da Vaga</div>
        <div class="p-card-body">
          <div class="mb-3">
            <label class="form-label">Título da Vaga *</label>
            <input type="text" name="titulo" class="form-control" required placeholder="Ex: Engenheiro de Software Sénior" value="<?= h($v['titulo'] ?? '') ?>">
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Categoria</label>
              <select name="cat" class="form-select">
                <option value="">-- Selecionar --</option>
                <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($v['categoria_id']??'')==$c['id']?'selected':'' ?>><?= h($c['nome']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Província</label>
              <select name="prov" class="form-select">
                <option value="">-- Selecionar --</option>
                <?php foreach ($provs as $p): ?><option value="<?= $p['id'] ?>" <?= ($v['provincia_id']??'')==$p['id']?'selected':'' ?>><?= h($p['nome']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Tipo de Contrato</label>
              <select name="contrato" class="form-select">
                <?php foreach(['efectivo'=>'Efectivo','contrato'=>'Contrato a prazo','part_time'=>'Part-time','freelance'=>'Freelance','estagio'=>'Estágio','voluntario'=>'Voluntariado'] as $k=>$l): ?>
                <option value="<?= $k ?>" <?= ($v['tipo_contrato']??'efectivo')===$k?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Modalidade</label>
              <select name="modal" class="form-select">
                <option value="presencial" <?= ($v['modalidade']??'presencial')==='presencial'?'selected':'' ?>>Presencial</option>
                <option value="remoto"     <?= ($v['modalidade']??'')==='remoto'    ?'selected':'' ?>>Remoto</option>
                <option value="hibrido"    <?= ($v['modalidade']??'')==='hibrido'   ?'selected':'' ?>>Híbrido</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Nº de Vagas</label>
              <input type="number" name="n_vagas" class="form-control" min="1" value="<?= h($v['vagas_disponiveis'] ?? 1) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Experiência Necessária</label>
              <select name="exp" class="form-select">
                <?php foreach(['sem_experiencia'=>'Sem experiência','junior'=>'Júnior (1–2 anos)','medio'=>'Médio (3–5 anos)','senior'=>'Sénior (5+ anos)','diretor'=>'Director / Gestor'] as $k=>$l): ?>
                <option value="<?= $k ?>" <?= ($v['nivel_experiencia']??'medio')===$k?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Escolaridade Mínima</label>
              <select name="escol" class="form-select">
                <?php foreach(['basico'=>'Básico','medio'=>'Médio','bacharelato'=>'Bacharelato','licenciatura'=>'Licenciatura','mestrado'=>'Mestrado','doutoramento'=>'Doutoramento','indiferente'=>'Indiferente'] as $k=>$l): ?>
                <option value="<?= $k ?>" <?= ($v['nivel_escolaridade']??'licenciatura')===$k?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="p-card mb-3">
        <div class="p-card-header">Requisitos (um por linha)</div>
        <div class="p-card-body">
          <textarea name="requisitos" class="form-control" rows="5" placeholder="Licenciatura em Informática&#10;3 anos de experiência em PHP&#10;Bom domínio do inglês"><?= h($v['requisitos'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="p-card mb-3">
        <div class="p-card-header">Descrição Completa *</div>
        <div class="p-card-body">
          <textarea name="descricao" class="form-control" rows="10" required placeholder="Descreve as funções, responsabilidades e o contexto da vaga..."><?= h($v['descricao'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="p-card mb-3">
        <div class="p-card-header">Benefícios (um por linha)</div>
        <div class="p-card-body">
          <textarea name="beneficios" class="form-control" rows="4" placeholder="Seguro de saúde&#10;Viatura de serviço&#10;Formação contínua"><?= h($v['beneficios'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="p-card mb-3">
        <div class="p-card-header">Salário</div>
        <div class="p-card-body">
          <div class="mb-2">
            <label class="form-label">Moeda</label>
            <select name="moeda" class="form-select form-select-sm">
              <option value="AOA" <?= ($v['moeda_salario']??'AOA')==='AOA'?'selected':'' ?>>AOA (Kwanza)</option>
              <option value="USD" <?= ($v['moeda_salario']??'')==='USD'?'selected':'' ?>>USD (Dólar)</option>
              <option value="EUR" <?= ($v['moeda_salario']??'')==='EUR'?'selected':'' ?>>EUR (Euro)</option>
            </select>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-6"><label class="form-label">Mínimo</label><input type="number" name="sal_min" class="form-control" step="1000" placeholder="0" value="<?= h($v['salario_min'] ?? '') ?>"></div>
            <div class="col-6"><label class="form-label">Máximo</label><input type="number" name="sal_max" class="form-control" step="1000" placeholder="0" value="<?= h($v['salario_max'] ?? '') ?>"></div>
          </div>
          <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="sal_vis" value="1" id="sv" <?= ($v['salario_visivel']??1)?'checked':'' ?>><label class="form-check-label" for="sv" style="font-size:.85rem;">Mostrar salário</label></div>
          <div class="form-check"><input class="form-check-input" type="checkbox" name="sal_neg" value="1" id="sn" <?= ($v['salario_negociavel']??0)?'checked':'' ?>><label class="form-check-label" for="sn" style="font-size:.85rem;">Negociável</label></div>
        </div>
      </div>

      <div class="p-card mb-3">
        <div class="p-card-header">Prazo</div>
        <div class="p-card-body">
          <label class="form-label">Data Limite de Candidatura</label>
          <input type="date" name="enc" class="form-control" value="<?= h($v['data_encerramento'] ?? '') ?>" min="<?= date('Y-m-d') ?>">
          <div class="text-muted-sm mt-1">Deixe em branco para prazo indefinido.</div>
        </div>
      </div>

      <div class="p-card">
        <div class="p-card-header">Publicar</div>
        <div class="p-card-body">
          <p class="text-muted-sm mb-3">A vaga será analisada pelo administrador antes de ficar visível.</p>
          <div class="d-grid gap-2">
            <button type="submit" name="pub_acao" value="publicar" class="btn btn-ace fw-bold">
              <i data-feather="send" style="width:14px;height:14px;" class="me-1"></i>
              <?= $vaga ? 'Actualizar Vaga' : 'Submeter para Publicação' ?>
            </button>
            <button type="submit" name="pub_acao" value="rascunho" class="btn btn-outline-secondary">
              <i data-feather="save" style="width:14px;height:14px;" class="me-1"></i>Guardar Rascunho
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<?php require_once '../includes/footer_painel.php'; ?>
