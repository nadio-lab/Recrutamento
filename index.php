<?php
require_once 'includes/config.php';
$cfg = allCfg();

// Redirecionar se já está logado
if (loggedIn()) {
    $dest = match($_SESSION['tipo']??'') {
        'admin'   => url('admin/index.php'),
        'empresa' => url('empresa/index.php'),
        default   => ''   // candidatos ficam na homepage
    };
    if ($dest) { header("Location: $dest"); exit; }
}

// ── PESQUISA E FILTROS ─────────────────────────────────────
$q        = trim($_GET['q']       ?? '');
$cat_id   = (int)($_GET['cat']    ?? 0);
$prov_id  = (int)($_GET['prov']   ?? 0);
$contrato = $_GET['contrato']     ?? '';
$modal    = $_GET['modal']        ?? '';
$pg       = max(1, (int)($_GET['pg'] ?? 1));
$por_pag  = (int)($cfg['vagas_por_pagina'] ?? 12);
$offset   = ($pg - 1) * $por_pag;

$where = ["v.estado='publicada'"];
$params = [];
if ($q)        { $where[] = "(v.titulo LIKE ? OR v.descricao LIKE ? OR e.nome LIKE ?)"; $b="%$q%"; $params=array_merge($params,[$b,$b,$b]); }
if ($cat_id)   { $where[] = "v.categoria_id=?";  $params[]=$cat_id; }
if ($prov_id)  { $where[] = "v.provincia_id=?";  $params[]=$prov_id; }
if ($contrato) { $where[] = "v.tipo_contrato=?";  $params[]=$contrato; }
if ($modal)    { $where[] = "v.modalidade=?";     $params[]=$modal; }
$w = implode(' AND ', $where);

$total_vagas = (int)(DB::val("SELECT COUNT(*) FROM vagas v JOIN empresas e ON e.id=v.empresa_id WHERE $w", $params) ?? 0);
$vagas = DB::all(
    "SELECT v.*, e.nome as emp_nome, e.logo as emp_logo, e.verificada as emp_verif, e.id as eid,
            c.nome as cat_nome, p.nome as prov_nome
     FROM vagas v JOIN empresas e ON e.id=v.empresa_id
     LEFT JOIN categorias c ON c.id=v.categoria_id
     LEFT JOIN provincias p ON p.id=v.provincia_id
     WHERE $w ORDER BY v.destaque DESC, v.data_publicacao DESC
     LIMIT $por_pag OFFSET $offset",
    $params
);

$categorias = DB::all("SELECT * FROM categorias WHERE ativo=1 ORDER BY nome");
$provincias = DB::all("SELECT id,nome FROM provincias ORDER BY nome");
$total_pag  = max(1, ceil($total_vagas / $por_pag));

// Stats hero
$total_pub   = DB::val("SELECT COUNT(*) FROM vagas WHERE estado='publicada'") ?? 0;
$total_empr  = DB::val("SELECT COUNT(*) FROM empresas WHERE estado='aprovada'") ?? 0;
$total_cands = DB::val("SELECT COUNT(*) FROM candidatos") ?? 0;

// Vagas guardadas
$guardadas = [];
if (loggedIn() && ($_SESSION['tipo']??'') === 'candidato') {
    $cand = meCandidato();
    if ($cand) {
        $rows = DB::all("SELECT vaga_id FROM vagas_guardadas WHERE candidato_id=?", [$cand['id']]);
        $guardadas = array_column($rows,'vaga_id');
    }
}

$titulo = 'Emprega — ' . ($cfg['site_slogan'] ?? 'Encontra o teu emprego');
require_once 'includes/header_publico.php';
?>

<?php if ($pg === 1 && !$q && !$cat_id && !$prov_id && !$contrato && !$modal): ?>
<!-- ── HERO ─────────────────────────────────────────── -->
<section class="hero">
  <div class="container position-relative">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <h1 class="hero-titulo">
          Encontra o emprego<br>dos teus <span>sonhos</span><br>em <?= h($cfg['site_pais']??'Angola') ?>
        </h1>
        <p class="hero-sub">Mais de <?= number_format($total_pub) ?> vagas publicadas por empresas líderes.</p>

        <form action="<?= url('index.php') ?>" method="GET" class="hero-search">
          <input type="text" name="q" placeholder="Cargo, empresa ou palavra-chave..." value="<?= h($q) ?>">
          <select name="prov">
            <option value="">Todas as províncias</option>
            <?php foreach ($provincias as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $prov_id==$p['id']?'selected':'' ?>><?= h($p['nome']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn-search">
            <i data-feather="search" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;"></i>Pesquisar
          </button>
        </form>

        <div class="hero-stats">
          <div class="hero-stat"><strong><?= number_format($total_pub) ?>+</strong><span>Vagas activas</span></div>
          <div class="hero-stat"><strong><?= number_format($total_empr) ?>+</strong><span>Empresas</span></div>
          <div class="hero-stat"><strong><?= number_format($total_cands) ?>+</strong><span>Candidatos</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CATEGORIAS ─────────────────────────────────────── -->
<section class="secao pb-3" style="padding-top:3rem;">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
      <div>
        <h2 class="secao-titulo">Explorar por Categoria</h2>
        <p class="secao-sub m-0">Encontra vagas na tua área</p>
      </div>
      <a href="<?= url('categorias.php') ?>" class="btn btn-sm btn-outline-secondary">Ver todas</a>
    </div>
    <div class="row g-3">
      <?php
      $cats_pop = DB::all(
          "SELECT c.*, COUNT(v.id) as total FROM categorias c
           LEFT JOIN vagas v ON v.categoria_id=c.id AND v.estado='publicada'
           WHERE c.ativo=1 GROUP BY c.id ORDER BY total DESC LIMIT 8"
      );
      foreach ($cats_pop as $cat):
      ?>
      <div class="col-6 col-sm-4 col-md-3 col-lg-2">
        <a href="<?= url('index.php?cat='.$cat['id']) ?>" class="cat-card text-decoration-none">
          <div class="cat-icon">
            <i data-feather="<?= h($cat['icone']??'briefcase') ?>" style="width:22px;height:22px;"></i>
          </div>
          <div class="cat-nome"><?= h($cat['nome']) ?></div>
          <div class="cat-vagas"><?= $cat['total'] ?> vaga<?= $cat['total']!=1?'s':'' ?></div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── VAGAS ─────────────────────────────────────────── -->
<section class="secao">
  <div class="container">
    <div class="row g-4">

      <!-- FILTROS LATERAL (desktop) -->
      <div class="col-lg-3 d-none d-lg-block">
        <div class="filtros-sidebar">
          <form method="GET" action="<?= url('index.php') ?>" id="fFiltros">
            <?php if ($q): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
            <h6 class="fw-bold mb-3" style="font-size:.85rem;color:var(--pri);">Filtrar Vagas</h6>

            <div class="filtro-grupo">
              <div class="filtro-titulo">Categoria</div>
              <?php foreach ($categorias as $c): ?>
              <label class="filtro-check">
                <input type="radio" name="cat" value="<?= $c['id'] ?>"
                  <?= $cat_id==$c['id']?'checked':'' ?>
                  onchange="document.getElementById('fFiltros').submit()">
                <?= h($c['nome']) ?>
              </label>
              <?php endforeach; ?>
              <?php if ($cat_id): ?>
              <a href="javascript:void(0)" onclick="clearFilter('cat')" class="text-danger" style="font-size:.78rem;">✕ Limpar categoria</a>
              <?php endif; ?>
            </div>

            <div class="filtro-grupo">
              <div class="filtro-titulo">Província</div>
              <select name="prov" class="form-select form-select-sm"
                onchange="document.getElementById('fFiltros').submit()">
                <option value="">Todas</option>
                <?php foreach ($provincias as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $prov_id==$p['id']?'selected':'' ?>><?= h($p['nome']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="filtro-grupo">
              <div class="filtro-titulo">Tipo de Contrato</div>
              <?php foreach (['efectivo'=>'Efectivo','contrato'=>'Contrato','part_time'=>'Part-time','freelance'=>'Freelance','estagio'=>'Estágio'] as $v=>$l): ?>
              <label class="filtro-check">
                <input type="radio" name="contrato" value="<?= $v ?>"
                  <?= $contrato===$v?'checked':'' ?>
                  onchange="document.getElementById('fFiltros').submit()">
                <?= $l ?>
              </label>
              <?php endforeach; ?>
            </div>

            <div class="filtro-grupo">
              <div class="filtro-titulo">Modalidade</div>
              <?php foreach (['presencial'=>'Presencial','remoto'=>'Remoto','hibrido'=>'Híbrido'] as $v=>$l): ?>
              <label class="filtro-check">
                <input type="radio" name="modal" value="<?= $v ?>"
                  <?= $modal===$v?'checked':'' ?>
                  onchange="document.getElementById('fFiltros').submit()">
                <?= $l ?>
              </label>
              <?php endforeach; ?>
            </div>

            <?php if ($q || $cat_id || $prov_id || $contrato || $modal): ?>
            <a href="<?= url('index.php') ?>" class="btn btn-sm btn-outline-danger w-100 mt-1">
              <i data-feather="x" style="width:13px;height:13px;" class="me-1"></i>Limpar filtros
            </a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- LISTA DE VAGAS -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <div>
            <h5 class="mb-0" style="font-size:1rem;">
              <?php if ($q): ?>Resultados para "<strong><?= h($q) ?></strong>"
              <?php elseif ($cat_id): ?><?= h($categorias[array_search($cat_id,array_column($categorias,'id'))]['nome'] ?? 'Categoria') ?>
              <?php else: ?>Vagas Recentes<?php endif; ?>
            </h5>
            <span class="text-muted-sm"><?= number_format($total_vagas) ?> vaga<?= $total_vagas!=1?'s':'' ?> encontrada<?= $total_vagas!=1?'s':'' ?></span>
          </div>
          <!-- Botão filtros mobile -->
          <button class="d-lg-none btn btn-sm btn-outline-secondary"
            data-bs-toggle="offcanvas" data-bs-target="#filtrosMobile">
            <i data-feather="filter" style="width:14px;height:14px;" class="me-1"></i>Filtros
          </button>
        </div>

        <?php if (empty($vagas)): ?>
        <div class="text-center py-5">
          <div style="font-size:3rem;margin-bottom:.75rem;">🔍</div>
          <h5>Nenhuma vaga encontrada</h5>
          <p class="text-muted">Tenta outros filtros ou palavras-chave.</p>
          <a href="<?= url('index.php') ?>" class="btn btn-sm" style="background:var(--ace);color:#fff;font-weight:600;">Ver todas as vagas</a>
        </div>
        <?php else: ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($vagas as $v):
            $esta_guardada = in_array($v['id'], $guardadas);
          ?>
          <div class="vaga-card <?= $v['destaque']?'destaque':'' ?>">
            <?php if ($v['destaque']): ?>
            <div style="position:absolute;top:.75rem;right:.75rem;">
              <span class="badge-destaque">⭐ Destaque</span>
            </div>
            <?php endif; ?>

            <div class="d-flex align-items-start gap-3">
              <!-- Logo empresa -->
              <?php if ($v['emp_logo']): ?>
                <img src="<?= url('uploads/logos/'.h($v['emp_logo'])) ?>"
                  class="vaga-empresa-logo" alt="<?= h($v['emp_nome']) ?>">
              <?php else: ?>
                <div class="vaga-empresa-logo-placeholder">
                  <?= mb_strtoupper(mb_substr($v['emp_nome'],0,1)) ?>
                </div>
              <?php endif; ?>

              <div class="flex-grow-1 min-w-0">
                <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                  <div>
                    <h3 class="vaga-titulo mb-1">
                      <a href="<?= url('vaga.php?id='.$v['id']) ?>"><?= h($v['titulo']) ?></a>
                    </h3>
                    <div class="vaga-empresa-nome">
                      <a href="<?= url('empresa.php?id='.$v['eid']) ?>" class="text-decoration-none" style="color:var(--sec);">
                        <?= h($v['emp_nome']) ?>
                      </a>
                      <?php if ($v['emp_verif']): ?>
                      <i data-feather="check-circle" style="width:13px;height:13px;color:#2d6a4f;" title="Verificada"></i>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="text-end d-none d-sm-block">
                    <div class="vaga-salario"><?= formatSalario($v['salario_min'],$v['salario_max'],$v['moeda_salario'],$v['salario_visivel']) ?></div>
                  </div>
                </div>

                <div class="d-flex flex-wrap gap-1 mt-2">
                  <span class="badge-contrato"><?= labelContrato($v['tipo_contrato']) ?></span>
                  <span class="badge-modalidade"><?= labelModalidade($v['modalidade']) ?></span>
                  <span class="badge-experiencia"><?= labelExperiencia($v['nivel_experiencia']) ?></span>
                </div>

                <div class="d-flex flex-wrap gap-3 mt-2 align-items-center">
                  <?php if ($v['prov_nome']): ?>
                  <span class="vaga-local d-flex align-items-center gap-1">
                    <i data-feather="map-pin" style="width:13px;height:13px;"></i><?= h($v['prov_nome']) ?>
                  </span>
                  <?php endif; ?>
                  <?php if ($v['cat_nome']): ?>
                  <span class="vaga-local d-flex align-items-center gap-1">
                    <i data-feather="tag" style="width:13px;height:13px;"></i><?= h($v['cat_nome']) ?>
                  </span>
                  <?php endif; ?>
                  <span class="vaga-tempo d-flex align-items-center gap-1">
                    <i data-feather="clock" style="width:12px;height:12px;"></i><?= tempo($v['data_publicacao']) ?>
                  </span>
                  <?php if ($v['data_encerramento']): ?>
                  <span class="vaga-tempo <?= $v['data_encerramento'] < date('Y-m-d',strtotime('+3 days'))?'text-danger':'' ?>">
                    Prazo: <?= date('d/m/Y',strtotime($v['data_encerramento'])) ?>
                  </span>
                  <?php endif; ?>
                </div>
                <!-- Salário mobile -->
                <div class="d-sm-none mt-2">
                  <div class="vaga-salario"><?= formatSalario($v['salario_min'],$v['salario_max'],$v['moeda_salario'],$v['salario_visivel']) ?></div>
                </div>
              </div>
            </div>

            <!-- Botões -->
            <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
              <div class="d-flex align-items-center gap-2 text-muted-sm">
                <span><i data-feather="users" style="width:12px;height:12px;"></i> <?= $v['total_candidaturas'] ?></span>
                <span><i data-feather="eye" style="width:12px;height:12px;"></i> <?= $v['total_visualizacoes'] ?></span>
              </div>
              <div class="d-flex gap-2 align-items-center">
                <?php if (loggedIn() && ($_SESSION['tipo']??'') === 'candidato'): ?>
                <button class="btn-guardar <?= $esta_guardada?'guardada':'' ?>"
                  onclick="guardarVaga(<?= $v['id'] ?>,this)"
                  title="<?= $esta_guardada?'Guardada':'Guardar' ?>">
                  <i data-feather="heart" style="width:15px;height:15px;<?= $esta_guardada?'fill:currentColor;':'' ?>"></i>
                </button>
                <?php endif; ?>
                <a href="<?= url('vaga.php?id='.$v['id']) ?>" class="btn btn-sm btn-outline-secondary">Ver detalhes</a>
                <a href="<?= url('vaga.php?id='.$v['id'].'#candidatar') ?>" class="btn-candidatar">Candidatar-se</a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- PAGINAÇÃO -->
        <?php if ($total_pag > 1): ?>
        <nav class="mt-4" aria-label="Paginação">
          <ul class="pagination justify-content-center">
            <?php if ($pg > 1): ?>
            <li class="page-item">
              <a class="page-link" href="<?= url('index.php?'.http_build_query(array_merge($_GET,['pg'=>$pg-1]))) ?>">‹ Anterior</a>
            </li>
            <?php endif; ?>
            <?php for ($i=max(1,$pg-2); $i<=min($total_pag,$pg+2); $i++): ?>
            <li class="page-item <?= $i===$pg?'active':'' ?>">
              <a class="page-link" href="<?= url('index.php?'.http_build_query(array_merge($_GET,['pg'=>$i]))) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <?php if ($pg < $total_pag): ?>
            <li class="page-item">
              <a class="page-link" href="<?= url('index.php?'.http_build_query(array_merge($_GET,['pg'=>$pg+1]))) ?>">Próxima ›</a>
            </li>
            <?php endif; ?>
          </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- OFFCANVAS FILTROS MOBILE -->
<div class="offcanvas offcanvas-start" id="filtrosMobile" tabindex="-1">
  <div class="offcanvas-header">
    <h6 class="offcanvas-title fw-bold">Filtrar Vagas</h6>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <form method="GET" action="<?= url('index.php') ?>">
      <?php if ($q): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
      <div class="mb-3">
        <label class="form-label">Categoria</label>
        <select name="cat" class="form-select">
          <option value="">Todas</option>
          <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $cat_id==$c['id']?'selected':'' ?>><?= h($c['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Província</label>
        <select name="prov" class="form-select">
          <option value="">Todas</option>
          <?php foreach ($provincias as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $prov_id==$p['id']?'selected':'' ?>><?= h($p['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Tipo de Contrato</label>
        <select name="contrato" class="form-select">
          <option value="">Todos</option>
          <?php foreach (['efectivo'=>'Efectivo','contrato'=>'Contrato','part_time'=>'Part-time','freelance'=>'Freelance','estagio'=>'Estágio'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= $contrato===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-4">
        <label class="form-label">Modalidade</label>
        <select name="modal" class="form-select">
          <option value="">Todas</option>
          <option value="presencial" <?= $modal==='presencial'?'selected':'' ?>>Presencial</option>
          <option value="remoto"     <?= $modal==='remoto'    ?'selected':'' ?>>Remoto</option>
          <option value="hibrido"    <?= $modal==='hibrido'   ?'selected':'' ?>>Híbrido</option>
        </select>
      </div>
      <button type="submit" class="btn w-100 mb-2 fw-bold" style="background:var(--ace);color:#fff;">Aplicar Filtros</button>
      <a href="<?= url('index.php') ?>" class="btn btn-outline-secondary w-100">Limpar</a>
    </form>
  </div>
</div>

<?php require_once 'includes/footer_publico.php'; ?>
<script>
function clearFilter(name) {
  const url = new URL(window.location.href);
  url.searchParams.delete(name);
  window.location.href = url.toString();
}
function guardarVaga(id, btn) {
  fetch('<?= url('ajax/guardar_vaga.php') ?>', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'vaga_id='+id
  })
  .then(r=>r.json()).then(d=>{
    const ico = btn.querySelector('svg, i');
    if (d.guardada) {
      btn.classList.add('guardada');
      if(ico) ico.style.fill='currentColor';
      btn.title='Guardada';
    } else {
      btn.classList.remove('guardada');
      if(ico) ico.style.fill='none';
      btn.title='Guardar';
    }
  });
}
</script>
