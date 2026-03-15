<?php
require_once '../includes/config.php';
requireAuth('candidato');
$cfg=allCfg(); $cand=meCandidato();
if(!$cand) redirect('logout.php');
$titulo_pag='Dashboard';

$stats=[
    'cands'   =>DB::val("SELECT COUNT(*) FROM candidaturas WHERE candidato_id=?",[$cand['id']])??0,
    'guarda'  =>DB::val("SELECT COUNT(*) FROM vagas_guardadas WHERE candidato_id=?",[$cand['id']])??0,
    'entrev'  =>DB::val("SELECT COUNT(*) FROM candidaturas WHERE candidato_id=? AND estado='entrevista'",[$cand['id']])??0,
    'ofertas' =>DB::val("SELECT COUNT(*) FROM candidaturas WHERE candidato_id=? AND estado IN('oferta','aceite')",[$cand['id']])??0,
];

// Completude do perfil (%)
$pts=0;
if($cand['foto']) $pts+=10;
if($cand['titulo_profissional']) $pts+=15;
if($cand['sobre']) $pts+=15;
if($cand['cv_ficheiro']) $pts+=25;
if($cand['telefone']) $pts+=5;
if(DB::val("SELECT COUNT(*) FROM candidato_educacao    WHERE candidato_id=?",[$cand['id']])>0) $pts+=15;
if(DB::val("SELECT COUNT(*) FROM candidato_experiencia WHERE candidato_id=?",[$cand['id']])>0) $pts+=10;
if(DB::val("SELECT COUNT(*) FROM candidato_competencias WHERE candidato_id=?",[$cand['id']])>0) $pts+=5;
$pts=min(100,$pts);

$cands_rec=DB::all("SELECT c.*,v.titulo,v.tipo_contrato,v.modalidade,e.nome as en,e.logo as el FROM candidaturas c JOIN vagas v ON v.id=c.vaga_id JOIN empresas e ON e.id=v.empresa_id WHERE c.candidato_id=? ORDER BY c.data_candidatura DESC LIMIT 8",[$cand['id']]);
$notifs=DB::all("SELECT * FROM notificacoes WHERE utilizador_id=? ORDER BY criado_em DESC LIMIT 5",[$cand['utilizador_id']]);
DB::exec("UPDATE notificacoes SET lida=1 WHERE utilizador_id=? AND lida=0",[$cand['utilizador_id']]);

// Vagas recomendadas
$cat_ids=DB::all("SELECT DISTINCT v.categoria_id FROM candidaturas c JOIN vagas v ON v.id=c.vaga_id WHERE c.candidato_id=? AND v.categoria_id IS NOT NULL LIMIT 3",[$cand['id']]);
$cat_in=implode(',',array_filter(array_column($cat_ids,'categoria_id')))?:'0';
$recom=DB::all("SELECT v.*,e.nome as en,e.logo as el FROM vagas v JOIN empresas e ON e.id=v.empresa_id WHERE v.estado='publicada' AND v.id NOT IN(SELECT vaga_id FROM candidaturas WHERE candidato_id=?) AND v.categoria_id IN($cat_in) ORDER BY v.destaque DESC,v.data_publicacao DESC LIMIT 4",[$cand['id']]);
if(empty($recom)) $recom=DB::all("SELECT v.*,e.nome as en,e.logo as el FROM vagas v JOIN empresas e ON e.id=v.empresa_id WHERE v.estado='publicada' AND v.id NOT IN(SELECT vaga_id FROM candidaturas WHERE candidato_id=?) ORDER BY v.destaque DESC,v.data_publicacao DESC LIMIT 4",[$cand['id']]);

require_once '../includes/header_painel.php';
?>

<?php if($pts<80): ?>
<div class="p-card mb-4" style="border-left:4px solid var(--ace);">
  <div class="p-card-body">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
      <div>
        <div class="fw-bold" style="color:var(--pri);">Completa o teu perfil</div>
        <div class="text-muted-sm">Perfis completos têm 3× mais hipóteses de serem contactados.</div>
      </div>
      <span style="font-size:1.2rem;font-weight:700;color:var(--ace);"><?= $pts ?>%</span>
    </div>
    <div style="height:8px;background:#f0f2f5;border-radius:4px;overflow:hidden;">
      <div style="height:100%;background:linear-gradient(90deg,var(--sec),var(--ace));width:<?= $pts ?>%;border-radius:4px;transition:width .5s;"></div>
    </div>
    <div class="d-flex gap-2 mt-3 flex-wrap">
      <?php if(!$cand['foto']): ?><a href="<?= url('candidato/perfil.php') ?>" class="btn btn-sm btn-outline-secondary">+ Foto</a><?php endif; ?>
      <?php if(!$cand['cv_ficheiro']): ?><a href="<?= url('candidato/perfil.php') ?>" class="btn btn-sm btn-outline-secondary">+ CV</a><?php endif; ?>
      <?php if(!$cand['sobre']): ?><a href="<?= url('candidato/perfil.php') ?>" class="btn btn-sm btn-outline-secondary">+ Sobre mim</a><?php endif; ?>
      <?php if(!DB::val("SELECT COUNT(*) FROM candidato_educacao WHERE candidato_id=?",[$cand['id']])): ?><a href="<?= url('candidato/curriculo.php') ?>" class="btn btn-sm btn-outline-secondary">+ Educação</a><?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3"><div class="stat-box s-blue"><div class="stat-icon s-blue"><i data-feather="file-text"></i></div><div class="stat-val"><?= $stats['cands'] ?></div><div class="stat-lbl">Candidaturas</div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-box s-amber"><div class="stat-icon s-amber"><i data-feather="heart"></i></div><div class="stat-val"><?= $stats['guarda'] ?></div><div class="stat-lbl">Guardadas</div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-box s-green"><div class="stat-icon s-green"><i data-feather="calendar"></i></div><div class="stat-val"><?= $stats['entrev'] ?></div><div class="stat-lbl">Entrevistas</div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-box s-red"><div class="stat-icon s-red"><i data-feather="star"></i></div><div class="stat-val"><?= $stats['ofertas'] ?></div><div class="stat-lbl">Ofertas</div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="p-card h-100">
      <div class="p-card-header d-flex justify-content-between">
        <span><i data-feather="file-text" style="width:15px;height:15px;" class="me-1"></i>Minhas Candidaturas</span>
        <a href="<?= url('candidato/candidaturas.php') ?>" class="btn btn-sm btn-outline-secondary py-1">Ver todas</a>
      </div>
      <div class="p-card-body p-0">
        <?php if(empty($cands_rec)): ?>
        <div class="text-center py-4" style="font-size:.875rem;color:#6c757d;">
          Ainda sem candidaturas.<br>
          <a href="<?= url('index.php') ?>" style="color:var(--ace);">Pesquisar vagas →</a>
        </div>
        <?php else: foreach($cands_rec as $c): [$lbl,$cls]=estadoCandidaturaLabel($c['estado']); ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <?php if($c['el']): ?>
            <img src="<?= url('uploads/logos/'.h($c['el'])) ?>" style="width:34px;height:34px;border-radius:8px;object-fit:contain;background:#f8f9fc;flex-shrink:0;">
          <?php else: ?>
            <div style="width:34px;height:34px;border-radius:8px;background:var(--pri);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0;"><?= mb_strtoupper(mb_substr($c['en'],0,1)) ?></div>
          <?php endif; ?>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-600" style="font-size:.84rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($c['titulo']) ?></div>
            <div class="text-muted-sm"><?= h($c['en']) ?> · <?= tempo($c['data_candidatura']) ?></div>
          </div>
          <span class="badge bg-<?= $cls ?> flex-shrink-0"><?= $lbl ?></span>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <?php if(!empty($notifs)): ?>
    <div class="p-card mb-3">
      <div class="p-card-header"><i data-feather="bell" style="width:15px;height:15px;" class="me-1"></i>Notificações</div>
      <div class="p-card-body p-0">
        <?php foreach($notifs as $n): ?>
        <div class="px-3 py-2 border-bottom">
          <div class="fw-600" style="font-size:.83rem;"><?= h($n['titulo']) ?></div>
          <div class="text-muted-sm"><?= h($n['mensagem']??'') ?></div>
          <div class="text-muted-sm"><?= tempo($n['criado_em']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($recom)): ?>
    <div class="p-card">
      <div class="p-card-header"><i data-feather="zap" style="width:15px;height:15px;" class="me-1"></i>Recomendadas para Ti</div>
      <div class="p-card-body p-0">
        <?php foreach($recom as $r): ?>
        <a href="<?= url('vaga.php?id='.$r['id']) ?>" class="d-flex align-items-center gap-2 px-3 py-2 border-bottom text-decoration-none" style="color:inherit;">
          <?php if($r['el']): ?>
            <img src="<?= url('uploads/logos/'.h($r['el'])) ?>" style="width:30px;height:30px;border-radius:6px;object-fit:contain;background:#f8f9fc;flex-shrink:0;">
          <?php else: ?>
            <div style="width:30px;height:30px;border-radius:6px;background:var(--sec);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.68rem;font-weight:700;flex-shrink:0;"><?= mb_strtoupper(mb_substr($r['en'],0,1)) ?></div>
          <?php endif; ?>
          <div class="min-w-0 flex-grow-1">
            <div style="font-size:.83rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($r['titulo']) ?></div>
            <div class="text-muted-sm"><?= h($r['en']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/footer_painel.php'; ?>
