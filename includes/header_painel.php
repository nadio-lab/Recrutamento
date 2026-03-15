 <?php
// includes/header_painel.php
$cfg       = $cfg ?? allCfg();
$titulo_pag= $titulo_pag ?? 'Painel';
$nome_site = $cfg['site_nome'] ?? 'Emprega';
$cor_pri   = $cfg['cor_primaria']   ?? '#0a2540';
$cor_ace   = $cfg['cor_acento']     ?? '#e63946';
$cor_sec   = $cfg['cor_secundaria'] ?? '#457b9d';

$script     = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$depth      = substr_count(str_replace('\\','/',dirname($script)), '/');
$base_depth = substr_count(rtrim(str_replace('\\','/',parse_url(BASE_URL,PHP_URL_PATH)),'/'), '/');
$levels     = max(0, $depth - $base_depth);
$rel        = str_repeat('../', $levels);

$tipo_u   = $_SESSION['tipo']  ?? '';
$nome_u   = $_SESSION['nome']  ?? '';
$notifs   = totalNotifs();

// Configuração de Menus (Mantida igual ao seu original)
$nav_admin = [
    ['home', url('admin/index.php'), 'Dashboard'],
    ['briefcase', url('admin/vagas.php'), 'Vagas'],
    ['building-2', url('admin/empresas.php'), 'Empresas'],
    ['users', url('admin/candidatos.php'), 'Candidatos'],
    ['file-text', url('admin/candidaturas.php'), 'Candidaturas'],
    ['grid', url('admin/categorias.php'), 'Categorias'],
    ['bar-chart-2', url('admin/relatorios.php'), 'Relatórios'],
    ['settings', url('admin/configuracoes.php'), 'Configurações'],
];
$nav_empresa = [
    ['home', url('empresa/index.php'), 'Dashboard'],
    ['briefcase', url('empresa/vagas.php'), 'Minhas Vagas'],
    ['plus-circle', url('empresa/nova_vaga.php'), 'Publicar Vaga'],
    ['file-text', url('empresa/candidaturas.php'), 'Candidaturas'],
    ['edit', url('empresa/perfil.php'), 'Perfil da Empresa'],
];
$nav_candidato = [
    ['home', url('candidato/index.php'), 'Dashboard'],
    ['search', url('index.php'), 'Pesquisar Vagas'],
    ['file-text', url('candidato/candidaturas.php'), 'Minhas Candidaturas'],
    ['heart', url('candidato/guardadas.php'), 'Vagas Guardadas'],
    ['user', url('candidato/perfil.php'), 'Meu Perfil'],
    ['file', url('candidato/curriculo.php'), 'Currículo'],
    ['bell', url('candidato/notificacoes.php'), 'Notificações'],
];

$nav_links = match($tipo_u) {
    'admin'     => $nav_admin,
    'empresa'   => $nav_empresa,
    default     => $nav_candidato
};

$script_atual = basename($_SERVER['SCRIPT_NAME'] ?? '');
$dir_atual    = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover">
<meta name="theme-color" content="<?= h($cor_pri) ?>">
<title><?= h($titulo_pag) ?> — <?= h($nome_site) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= $rel ?>assets/css/style.css">
<!-- Favicon Dinâmico -->
<?php if(!empty($cfg['site_logo'])): ?>
  <link rel="icon" type="image/png" href="<?= url('uploads/site/'.$cfg['site_logo']) ?>">
<?php endif; ?>

<style>
:root { --pri: <?= h($cor_pri) ?>; --ace: <?= h($cor_ace) ?>; --sec: <?= h($cor_sec) ?>; }
.painel-brand img { max-height: 35px; width: auto; object-fit: contain; }
</style>
</head>
<body>

<div class="painel-overlay" id="pOverlay"></div>

<aside class="painel-sidebar" id="pSidebar">
  <div class="painel-brand">
    <a href="<?= url('index.php') ?>" class="nav-brand">
      <?php if(!empty($cfg['site_logo'])): ?>
        <img src="<?= url('uploads/site/'.$cfg['site_logo']) ?>" alt="<?= h($nome_site) ?>">
      <?php else: ?>
        <span style="font-size:1.2rem;color:#fff;"><?= h($nome_site) ?><span style="color:var(--ace);">.</span></span>
      <?php endif; ?>
    </a>
    <button class="painel-close" id="pClose" aria-label="Fechar menu">
      <i data-feather="x"></i>
    </button>
  </div>

  <div style="padding:.75rem 1.1rem .5rem;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:.6rem;min-width:0;">
    <?php
    $foto_u = '';
    // Filtro para buscar foto conforme o tipo de utilizador
        $foto_u = '';
    $user_id_sessao = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;

    if ($tipo_u === 'admin' && $user_id_sessao) { 
        $ad = DB::row("SELECT foto FROM utilizadores WHERE id=?", [$user_id_sessao]); 
        $foto_u = $ad['foto'] ?? ''; 
    }
    elseif ($tipo_u === 'candidato') { 
        $c = meCandidato(); 
        $foto_u = $c['foto'] ?? ''; 
    }
    elseif ($tipo_u === 'empresa') { 
        $e = meEmpresa();   
        $foto_u = $e['logo'] ?? ''; 
    }

    ?>
          <?php if ($foto_u): 
      $pasta = match($tipo_u) { 'empresa' => 'logos', 'admin' => 'perfil', default => 'fotos' };
    ?>
      <img src="<?= url('uploads/'.$pasta.'/'.$foto_u) ?>"
        style="width:34px;height:34px;border-radius:<?= ($tipo_u==='empresa'?'8px':'50%') ?>;object-fit:cover;background:#fff;flex-shrink:0;">
    <?php else: ?>
      <div style="width:34px;height:34px;border-radius:50%;background:var(--sec);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.8rem;flex-shrink:0;">
        <?= mb_strtoupper(mb_substr($nome_u,0,1)) ?>
      </div>
    <?php endif; ?>

    <div style="min-width:0;">
      <div style="font-size:.78rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($nome_u) ?></div>
      <div style="font-size:.68rem;color:rgba(255,255,255,.5);"><?= ucfirst($tipo_u) ?></div>
    </div>
  </div>

  <!-- Navegação -->
  <nav class="painel-nav">
    <?php foreach ($nav_links as [$ico,$href,$label]):
      $is_active = (rtrim($href,'/') === rtrim(url($dir_atual.'/'.$script_atual),'/'))
                || (rtrim($href,'/') === rtrim(url($script_atual),'/'));
    ?>
    <a class="nav-item <?= $is_active?'active':'' ?>" href="<?= h($href) ?>">
      <i data-feather="<?= $ico ?>"></i>
      <span><?= $label ?></span>
      <?php if ($label === 'Notificações' && $notifs > 0): ?>
        <span class="badge bg-danger ms-auto" style="font-size:.62rem;"><?= $notifs ?></span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="painel-footer">
    <a class="nav-item" href="<?= url('logout.php') ?>" style="color:#f87171!important;">
      <i data-feather="log-out"></i><span>Sair</span>
    </a>
  </div>
</aside>

<!-- MAIN CONTENT -->
<div class="painel-main">
  <header class="painel-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="painel-toggle" id="pToggle" aria-label="Abrir menu">
        <i data-feather="menu"></i>
      </button>
      <h1 class="painel-page-title"><?= h($titulo_pag) ?></h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <?php if ($notifs > 0): ?>
        <span class="badge bg-danger"><?= $notifs ?> notif.</span>
      <?php endif; ?>
      <a href="<?= url('index.php') ?>" class="btn btn-sm btn-outline-secondary d-none d-sm-inline-block">
        Ver Site
      </a>
    </div>
  </header>
  <div class="painel-content">
    <?= flash() ?>
