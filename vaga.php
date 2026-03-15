<?php
require_once 'includes/config.php';
$cfg = allCfg();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: '.url('index.php')); exit; }

$vaga = DB::row(
    "SELECT v.*, e.nome as en, e.logo as el, e.sobre as es, e.website as ew,
            e.dimensao as edim, e.verificada as ev, e.id as eid,
            c.nome as cn, p.nome as pn
     FROM vagas v
     JOIN empresas e ON e.id=v.empresa_id
     LEFT JOIN categorias c ON c.id=v.categoria_id
     LEFT JOIN provincias p ON p.id=v.provincia_id
     WHERE v.id=? AND v.estado='publicada'", [$id]
);
if (!$vaga) {
    header('HTTP/1.0 404 Not Found');
    header('Location: '.url('index.php').'?msg='.urlencode('Vaga não encontrada.'));
    exit;
}

// Registar visualização
DB::exec("UPDATE vagas SET total_visualizacoes=total_visualizacoes+1 WHERE id=?", [$id]);

// Estado do candidato
$ja_candidatou = false;
$esta_guardada = false;
$cand = null;
if (loggedIn() && ($_SESSION['tipo'] ?? '') === 'candidato') {
    $cand = meCandidato();
    if ($cand) {
        $ja_candidatou = (bool)DB::val("SELECT COUNT(*) FROM candidaturas WHERE vaga_id=? AND candidato_id=?", [$id, $cand['id']]);
        $esta_guardada = (bool)DB::val("SELECT COUNT(*) FROM vagas_guardadas WHERE vaga_id=? AND candidato_id=?", [$id, $cand['id']]);
    }
}

// Processar candidatura
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidatar'])) {
    if (!loggedIn()) {
        header('Location: '.url('login.php').'?next='.urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    if (!$cand) {
        $erro = 'Perfil de candidato não encontrado.';
    } elseif ($ja_candidatou) {
        $erro = 'Já te candidataste a esta vaga.';
    } else {
        $carta  = trim($_POST['carta'] ?? '');
        $cv_nom = $cand['cv_ficheiro'];
        if (!empty($_FILES['cv']['name'])) {
            $res = uploadFicheiro($_FILES['cv'], 'cvs', ['pdf','doc','docx'], 5);
            if ($res['ok'])  $cv_nom = $res['nome'];
            else             $erro   = $res['msg'];
        }
        if (!$erro) {
            DB::insert(
                "INSERT INTO candidaturas (vaga_id, candidato_id, carta_apresentacao, cv_ficheiro)
                 VALUES (?,?,?,?)",
                [$id, $cand['id'], $carta, $cv_nom]
            );
            DB::exec("UPDATE vagas SET total_candidaturas=total_candidaturas+1 WHERE id=?", [$id]);
            // Notificar empresa
            $emp_uid = DB::val("SELECT utilizador_id FROM empresas WHERE id=?", [$vaga['eid']]);
            if ($emp_uid) {
                notificar($emp_uid, 'candidatura_recebida', 'Nova candidatura recebida',
                    ($cand['nome'] ?? '').' candidatou-se à vaga "'.$vaga['titulo'].'".',
                    url('empresa/candidaturas.php'));
            }
            header('Location: '.url('vaga.php?id='.$id).'&ok=1');
            exit;
        }
    }
}

// Vagas similares
$similares = DB::all(
    "SELECT v.*, e.nome as en, e.logo as el FROM vagas v
     JOIN empresas e ON e.id=v.empresa_id
     WHERE v.estado='publicada' AND v.id!=? AND v.categoria_id=?
     ORDER BY v.destaque DESC, v.data_publicacao DESC LIMIT 4",
    [$id, $vaga['categoria_id'] ?? 0]
);

$titulo     = h($vaga['titulo']).' — '.h($vaga['en']);
$descricao  = mb_substr(strip_tags($vaga['descricao']), 0, 160);
require_once 'includes/header_publico.php';
?>

<div class="container py-4">

  <?php if (!empty($_GET['ok'])): ?>
  <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
    <i data-feather="check-circle" style="width:16px;height:16px;"></i>
    <strong>Candidatura enviada com sucesso!</strong>&nbsp;Boa sorte.
    <a href="<?= url('candidato/candidaturas.php') ?>" class="ms-auto btn btn-sm btn-success">Ver candidaturas</a>
  </div>
  <?php endif; ?>

  <div class="row g-4">

    <!-- COLUNA PRINCIPAL -->
    <div class="col-lg-8">

      <!-- CABEÇALHO DA VAGA -->
      <div class="p-card mb-3">
        <div class="p-card-body">
          <div class="d-flex align-items-start gap-3 flex-wrap">
            <?php if ($vaga['el']): ?>
              <img src="<?= url('uploads/logos/'.h($vaga['el'])) ?>"
                style="width:72px;height:72px;border-radius:14px;object-fit:contain;background:#f8f9fc;padding:6px;border:1px solid #e4e9f0;flex-shrink:0;" alt="">
            <?php else: ?>
              <div style="width:72px;height:72px;border-radius:14px;background:linear-gradient(135deg,var(--pri),var(--sec));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.5rem;flex-shrink:0;">
                <?= mb_strtoupper(mb_substr($vaga['en'],0,1)) ?>
              </div>
            <?php endif; ?>

            <div class="flex-grow-1 min-w-0">
              <h1 style="font-size:1.4rem;font-weight:700;color:var(--pri);margin-bottom:.4rem;line-height:1.25;">
                <?= h($vaga['titulo']) ?>
              </h1>
              <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                <a href="<?= url('empresa.php?id='.$vaga['eid']) ?>" class="fw-600 text-decoration-none" style="color:var(--sec);font-size:.95rem;">
                  <?= h($vaga['en']) ?>
                </a>
                <?php if ($vaga['ev']): ?>
                <span style="font-size:.75rem;color:#2d6a4f;font-weight:600;display:flex;align-items:center;gap:.2rem;">
                  <i data-feather="check-circle" style="width:13px;height:13px;"></i>Verificada
                </span>
                <?php endif; ?>
              </div>
              <div class="d-flex flex-wrap gap-1 mb-2">
                <span class="badge-contrato"><?= labelContrato($vaga['tipo_contrato']) ?></span>
                <span class="badge-modalidade"><?= labelModalidade($vaga['modalidade']) ?></span>
                <span class="badge-experiencia"><?= labelExperiencia($vaga['nivel_experiencia']) ?></span>
                <?php if ($vaga['destaque']): ?><span class="badge-destaque">⭐ Destaque</span><?php endif; ?>
              </div>
              <div class="d-flex flex-wrap gap-3 align-items-center" style="font-size:.83rem;color:#6c757d;">
                <?php if ($vaga['pn']): ?>
                <span><i data-feather="map-pin" style="width:13px;height:13px;"></i> <?= h($vaga['pn']) ?></span>
                <?php endif; ?>
                <?php if ($vaga['cn']): ?>
                <span><i data-feather="tag" style="width:13px;height:13px;"></i> <?= h($vaga['cn']) ?></span>
                <?php endif; ?>
                <span><i data-feather="clock" style="width:13px;height:13px;"></i> <?= tempo($vaga['data_publicacao']) ?></span>
                <span><i data-feather="users" style="width:13px;height:13px;"></i> <?= $vaga['total_candidaturas'] ?> candidaturas</span>
                <?php if ($vaga['data_encerramento']): ?>
                <span style="<?= $vaga['data_encerramento'] < date('Y-m-d',strtotime('+3 days')) ? 'color:#dc2626;' : '' ?>">
                  <i data-feather="alert-circle" style="width:13px;height:13px;"></i>
                  Prazo: <?= date('d/m/Y', strtotime($vaga['data_encerramento'])) ?>
                </span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Salário -->
          <?php if ($vaga['salario_visivel']): ?>
          <div class="mt-3 p-3 rounded d-flex align-items-center gap-2" style="background:#f0fdf4;border:1px solid #86efac;">
            <i data-feather="dollar-sign" style="width:16px;height:16px;color:#16a34a;"></i>
            <span class="fw-600" style="color:#166534;">
              <?= formatSalario($vaga['salario_min'],$vaga['salario_max'],$vaga['moeda_salario'],1) ?>
              <?php if ($vaga['salario_negociavel']): ?><span style="font-weight:400;font-size:.82rem;color:#4b5563;"> (negociável)</span><?php endif; ?>
            </span>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- DESCRIÇÃO -->
      <div class="p-card mb-3">
        <div class="p-card-header"><i data-feather="file-text" style="width:15px;height:15px;" class="me-1"></i>Descrição da Vaga</div>
        <div class="p-card-body" style="font-size:.92rem;line-height:1.8;">
          <?= nl2br(h($vaga['descricao'])) ?>
        </div>
      </div>

      <?php if ($vaga['requisitos']): ?>
      <div class="p-card mb-3">
        <div class="p-card-header"><i data-feather="check-square" style="width:15px;height:15px;" class="me-1"></i>Requisitos</div>
        <div class="p-card-body">
          <ul style="margin:0;padding-left:1.25rem;line-height:2.1;font-size:.9rem;">
            <?php foreach (explode("\n", $vaga['requisitos']) as $r): if (trim($r)): ?>
            <li><?= h(trim($r)) ?></li>
            <?php endif; endforeach; ?>
          </ul>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($vaga['beneficios']): ?>
      <div class="p-card mb-3">
        <div class="p-card-header"><i data-feather="gift" style="width:15px;height:15px;" class="me-1"></i>Benefícios</div>
        <div class="p-card-body">
          <ul style="margin:0;padding-left:1.25rem;line-height:2.1;font-size:.9rem;">
            <?php foreach (explode("\n", $vaga['beneficios']) as $b): if (trim($b)): ?>
            <li><?= h(trim($b)) ?></li>
            <?php endif; endforeach; ?>
          </ul>
        </div>
      </div>
      <?php endif; ?>

      <!-- CANDIDATURA -->
      <div class="p-card" id="candidatar">
        <div class="p-card-header"><i data-feather="send" style="width:15px;height:15px;" class="me-1"></i>Candidatar-se a esta Vaga</div>
        <div class="p-card-body">

          <?php if ($erro): ?>
          <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem;"><?= h($erro) ?></div>
          <?php endif; ?>

          <?php if ($ja_candidatou): ?>
          <div class="alert alert-success d-flex align-items-center gap-2">
            <i data-feather="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
            Já enviaste a tua candidatura para esta vaga.
            <a href="<?= url('candidato/candidaturas.php') ?>" class="ms-auto btn btn-sm btn-success py-1">Ver candidaturas</a>
          </div>

          <?php elseif (!loggedIn()): ?>
          <div class="text-center py-3">
            <p style="color:#6c757d;margin-bottom:1rem;">Precisas de uma conta para te candidatares.</p>
            <a href="<?= url('login.php?next='.urlencode($_SERVER['REQUEST_URI'].'#candidatar')) ?>" class="btn btn-ace me-2">Entrar</a>
            <a href="<?= url('registar.php?tipo=candidato') ?>" class="btn btn-outline-secondary">Criar conta grátis</a>
          </div>

          <?php elseif (($_SESSION['tipo']??'') !== 'candidato'): ?>
          <div class="alert alert-warning">Apenas candidatos podem submeter candidaturas.</div>

          <?php else: ?>
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="candidatar" value="1">
            <div class="mb-3">
              <label class="form-label">Carta de Apresentação <span style="font-weight:400;color:#6c757d;">(opcional)</span></label>
              <textarea name="carta" class="form-control" rows="6"
                placeholder="Apresenta-te brevemente e explica porque és o candidato ideal para esta vaga..."></textarea>
            </div>
            <div class="mb-4">
              <label class="form-label">Currículo (CV)</label>
              <?php if ($cand['cv_ficheiro']): ?>
              <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" style="background:#f0f4ff;font-size:.84rem;">
                <i data-feather="file" style="width:14px;height:14px;color:var(--sec);"></i>
                <span>CV do teu perfil será usado automaticamente.</span>
                <a href="<?= url('candidato/perfil.php') ?>" style="color:var(--ace);font-size:.78rem;margin-left:auto;">Actualizar</a>
              </div>
              <?php endif; ?>
              <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx">
              <div class="text-muted-sm mt-1">PDF, DOC, DOCX · Máx. 5MB · Opcional se já tens CV no perfil</div>
            </div>
            <button type="submit" class="btn btn-ace fw-bold px-4 py-2">
              <i data-feather="send" style="width:15px;height:15px;" class="me-2"></i>Enviar Candidatura
            </button>
          </form>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <!-- COLUNA LATERAL -->
    <div class="col-lg-4">

      <!-- BOTÕES DE ACÇÃO -->
      <div class="p-card mb-3">
        <div class="p-card-body d-grid gap-2">
          <?php if (!$ja_candidatou && loggedIn() && ($_SESSION['tipo']??'') === 'candidato'): ?>
          <a href="#candidatar" class="btn btn-ace fw-bold">
            <i data-feather="send" style="width:15px;height:15px;" class="me-1"></i>Candidatar-se
          </a>
          <?php elseif (!loggedIn()): ?>
          <a href="<?= url('login.php?next='.urlencode($_SERVER['REQUEST_URI'].'#candidatar')) ?>" class="btn btn-ace fw-bold">
            Entrar para candidatar
          </a>
          <?php endif; ?>
          <?php if (loggedIn() && ($_SESSION['tipo']??'') === 'candidato'): ?>
          <button id="btnGuardar" class="btn btn-outline-secondary <?= $esta_guardada?'active':'' ?>"
            onclick="toggleGuardar(<?= $id ?>,this)">
            <i data-feather="heart" style="width:15px;height:15px;" class="me-1" <?= $esta_guardada?'style="fill:currentColor;"':'' ?>></i>
            <?= $esta_guardada ? 'Guardada' : 'Guardar Vaga' ?>
          </button>
          <?php endif; ?>
          <a href="<?= url('index.php') ?>" class="btn btn-outline-secondary">
            <i data-feather="arrow-left" style="width:14px;height:14px;" class="me-1"></i>Ver todas as vagas
          </a>
        </div>
      </div>

      <!-- SOBRE A EMPRESA -->
      <div class="p-card mb-3">
        <div class="p-card-header"><i data-feather="briefcase" style="width:15px;height:15px;" class="me-1"></i>Sobre a Empresa</div>
        <div class="p-card-body">
          <div class="d-flex align-items-center gap-3 mb-3">
            <?php if ($vaga['el']): ?>
              <img src="<?= url('uploads/logos/'.h($vaga['el'])) ?>"
                style="width:52px;height:52px;border-radius:10px;object-fit:contain;background:#f8f9fc;padding:4px;flex-shrink:0;" alt="">
            <?php else: ?>
              <div style="width:52px;height:52px;border-radius:10px;background:linear-gradient(135deg,var(--pri),var(--sec));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.1rem;flex-shrink:0;">
                <?= mb_strtoupper(mb_substr($vaga['en'],0,1)) ?>
              </div>
            <?php endif; ?>
            <div>
              <div class="fw-bold" style="color:var(--pri);"><?= h($vaga['en']) ?></div>
              <?php if ($vaga['ev']): ?><div style="font-size:.72rem;color:#2d6a4f;font-weight:600;">✓ Verificada</div><?php endif; ?>
            </div>
          </div>
          <?php if ($vaga['es']): ?>
          <p style="font-size:.86rem;color:#6c757d;line-height:1.65;margin-bottom:.75rem;">
            <?= h(mb_substr($vaga['es'],0,180)).(mb_strlen($vaga['es'])>180?'...':'') ?>
          </p>
          <?php endif; ?>
          <?php if ($vaga['edim']): ?>
          <div style="font-size:.8rem;color:#6c757d;" class="mb-1">
            <i data-feather="users" style="width:13px;height:13px;"></i> <?= ucfirst($vaga['edim']) ?>
          </div>
          <?php endif; ?>
          <?php if ($vaga['ew']): ?>
          <div style="font-size:.8rem;" class="mb-2">
            <i data-feather="globe" style="width:13px;height:13px;"></i>
            <a href="<?= h($vaga['ew']) ?>" target="_blank" rel="noopener" style="color:var(--sec);"><?= h(preg_replace('#^https?://#','',$vaga['ew'])) ?></a>
          </div>
          <?php endif; ?>
          <a href="<?= url('empresa.php?id='.$vaga['eid']) ?>" class="btn btn-sm btn-outline-secondary w-100 mt-2">Ver perfil da empresa</a>
        </div>
      </div>

      <!-- VAGAS SIMILARES -->
      <?php if (!empty($similares)): ?>
      <div class="p-card">
        <div class="p-card-header"><i data-feather="layers" style="width:15px;height:15px;" class="me-1"></i>Vagas Similares</div>
        <div class="p-card-body p-0">
          <?php foreach ($similares as $s): ?>
          <a href="<?= url('vaga.php?id='.$s['id']) ?>" class="d-flex align-items-center gap-2 px-3 py-2 border-bottom text-decoration-none" style="color:inherit;transition:background .15s;" onmouseover="this.style.background='#f8f9fc'" onmouseout="this.style.background=''">
            <?php if ($s['el']): ?>
              <img src="<?= url('uploads/logos/'.h($s['el'])) ?>" style="width:30px;height:30px;border-radius:6px;object-fit:contain;background:#f8f9fc;flex-shrink:0;" alt="">
            <?php else: ?>
              <div style="width:30px;height:30px;border-radius:6px;background:var(--sec);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.68rem;font-weight:700;flex-shrink:0;"><?= mb_strtoupper(mb_substr($s['en'],0,1)) ?></div>
            <?php endif; ?>
            <div class="min-w-0">
              <div style="font-size:.83rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($s['titulo']) ?></div>
              <div class="text-muted-sm"><?= h($s['en']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once 'includes/footer_publico.php'; ?>
<script>
function toggleGuardar(id, btn) {
  fetch('<?= url('ajax/guardar_vaga.php') ?>', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'vaga_id=' + id
  })
  .then(r => r.json())
  .then(d => {
    if (d.guardada) {
      btn.classList.add('active');
      btn.innerHTML = '<i data-feather="heart" style="width:15px;height:15px;fill:currentColor;margin-right:4px;"></i>Guardada';
    } else {
      btn.classList.remove('active');
      btn.innerHTML = '<i data-feather="heart" style="width:15px;height:15px;margin-right:4px;"></i>Guardar Vaga';
    }
    if (typeof feather !== 'undefined') feather.replace({'stroke-width':1.75});
  });
}
</script>
