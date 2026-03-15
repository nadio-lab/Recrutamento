 <?php
// Header reutilizável para páginas públicas
// Variáveis esperadas: $cfg (array), $titulo (string), $descricao (string)
$cfg      = $cfg ?? allCfg();
$titulo   = $titulo ?? ($cfg['site_nome'] ?? 'Emprega');
$descricao= $descricao ?? ($cfg['site_descricao'] ?? '');
$nome_site= $cfg['site_nome'] ?? 'Emprega';
$cor_pri  = $cfg['cor_primaria']   ?? '#0a2540';
$cor_ace  = $cfg['cor_acento']     ?? '#e63946';
$cor_sec  = $cfg['cor_secundaria'] ?? '#457b9d';

// Calcular profundidade para assets
$script     = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$depth      = substr_count(str_replace('\\','/',dirname($script)), '/');
$base_depth = substr_count(rtrim(str_replace('\\','/',parse_url(BASE_URL,PHP_URL_PATH)),'/'), '/');
$levels     = max(0, $depth - $base_depth);
$rel        = str_repeat('../', $levels); // '' para raiz, '../' para subpastas
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover">
<meta name="description" content="<?= h($descricao) ?>">
<meta name="theme-color" content="<?= h($cor_pri) ?>">
<title><?= h($titulo) ?> — <?= h($nome_site) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= $rel ?>assets/css/style.css">
<!-- Favicon Dinâmico -->
<?php if(!empty($cfg['site_logo'])): ?>
  <link rel="icon" type="image/png" href="<?= url('uploads/site/'.$cfg['site_logo']) ?>">
<?php endif; ?>


<style>
:root {
  --pri: <?= h($cor_pri) ?>;
  --ace: <?= h($cor_ace) ?>;
  --sec: <?= h($cor_sec) ?>;
}
/* Ajuste para o logo não quebrar o layout */
.nav-brand img {
    max-height: 40px;
    width: auto;
    object-fit: contain;
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-emprega">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
      
      <!-- LOGO OU NOME DO SITE -->
      <a href="<?= url('index.php') ?>" class="nav-brand">
        <?php if(!empty($cfg['site_logo'])): ?>
          <img src="<?= url('uploads/site/'.$cfg['site_logo']) ?>" alt="<?= h($nome_site) ?>">
        <?php else: ?>
          <?= h($nome_site) ?><span>.</span>
        <?php endif; ?>
      </a>

      <div class="d-none d-lg-flex align-items-center gap-1">
        <a href="<?= url('index.php') ?>"       class="nav-link-emp">Vagas</a>
        <a href="<?= url('empresas.php') ?>"    class="nav-link-emp">Empresas</a>
        <a href="<?= url('categorias.php') ?>"  class="nav-link-emp">Categorias</a>
      </div>

      <div class="d-flex align-items-center gap-2">
        <?php if (loggedIn()):
          $tipo_u = $_SESSION['tipo'] ?? '';
          $dash = match($tipo_u) {
              'admin'    => url('admin/index.php'),
              'empresa'  => url('empresa/index.php'),
              default    => url('candidato/index.php')
          };
          $notifs = totalNotifs();
        ?>
          <a href="<?= $dash ?>" class="btn btn-sm btn-outline-secondary d-none d-sm-inline-flex align-items-center gap-1">
            <i data-feather="grid" style="width:14px;height:14px;"></i>
            Painel <?php if ($notifs > 0): ?><span class="badge bg-danger ms-1"><?= $notifs ?></span><?php endif; ?>
          </a>
          <a href="<?= url('logout.php') ?>" class="btn-nav-pub" style="background:var(--pri);">Sair</a>
        <?php else: ?>
          <a href="<?= url('login.php') ?>"    class="btn btn-sm btn-outline-secondary d-none d-sm-inline-block">Entrar</a>
          <a href="<?= url('registar.php') ?>" class="btn-nav-pub">Registar</a>
        <?php endif; ?>
        <button class="d-lg-none btn btn-sm btn-outline-secondary p-1" id="menuMob" aria-label="Menu">
          <i data-feather="menu" style="width:18px;height:18px;"></i>
        </button>
      </div>
    </div>

    <!-- Menu mobile dropdown -->
    <div id="menuMobLinks" class="d-none py-2 border-top mt-2">
      <a href="<?= url('index.php') ?>"      class="nav-link-emp d-block py-2">Vagas</a>
      <a href="<?= url('empresas.php') ?>"   class="nav-link-emp d-block py-2">Empresas</a>
      <a href="<?= url('categorias.php') ?>" class="nav-link-emp d-block py-2">Categorias</a>
      <?php if (!loggedIn()): ?>
      <a href="<?= url('login.php') ?>"    class="nav-link-emp d-block py-2">Entrar</a>
      <a href="<?= url('registar.php') ?>" class="nav-link-emp d-block py-2" style="color:var(--ace);font-weight:700;">Registar Grátis</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
