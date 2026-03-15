<?php
require_once 'includes/config.php';
$cfg = allCfg();

if (loggedIn()) {
    $dest = match($_SESSION['tipo']??'') {
        'admin'   => url('admin/index.php'),
        'empresa' => url('empresa/index.php'),
        default   => url('candidato/index.php')
    };
    header("Location: $dest"); exit;
}

$erro = '';
$next = $_GET['next'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!$email || !$senha) {
        $erro = 'Preenche email e senha.';
    } else {
        $user = DB::row("SELECT * FROM utilizadores WHERE email=? AND ativo=1", [$email]);
        if ($user && password_verify($senha, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['uid']  = $user['id'];
            $_SESSION['tipo'] = $user['tipo'];
            $_SESSION['nome'] = $user['nome'];
            DB::exec("UPDATE utilizadores SET ultimo_login=NOW() WHERE id=?", [$user['id']]);
            $dest = match($user['tipo']) {
                'admin'   => url('admin/index.php'),
                'empresa' => url('empresa/index.php'),
                default   => url('candidato/index.php')
            };
            header("Location: " . ($next ?: $dest));
            exit;
        }
        $erro = 'Email ou senha incorretos. Verifica os teus dados.';
    }
}

$cor_pri = $cfg['cor_primaria']   ?? '#0a2540';
$cor_ace = $cfg['cor_acento']     ?? '#e63946';
$cor_sec = $cfg['cor_secundaria'] ?? '#457b9d';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="theme-color" content="<?= h($cor_pri) ?>">
<title>Entrar — <?= h($cfg['site_nome']??'Emprega') ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<?php if(!empty($cfg['site_logo'])): ?>
  <link rel="icon" type="image/png" href="<?= url('uploads/site/'.$cfg['site_logo']) ?>">
<?php endif; ?>

<style>
:root { --pri: <?= h($cor_pri) ?>; --ace: <?= h($cor_ace) ?>; --sec: <?= h($cor_sec) ?>; }
.login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--pri) 0%, #1a3a5c 60%, var(--sec) 100%);
  display: flex; align-items: center; justify-content: center; padding: 1.5rem;
}
.login-card {
  background: #fff; border-radius: 16px;
  padding: 2.5rem 2.25rem; width: 100%; max-width: 430px;
  box-shadow: 0 20px 60px rgba(0,0,0,.25);
}
</style>
</head>
<body>
<div class="login-page">
  <div class="login-card">
    <div class="text-center mb-4">
      <a href="<?= url('index.php') ?>" style="font-family:'Clash Display','DM Sans',sans-serif;font-size:1.8rem;font-weight:700;color:var(--pri);text-decoration:none;display:inline-block;margin-bottom:.5rem;">
        <?= h($cfg['site_nome']??'Emprega') ?><span style="color:var(--ace);">.</span>
      </a>
      <h5 class="fw-bold mb-1" style="color:var(--pri);">Bem-vindo de volta!</h5>
      <p class="text-muted mb-0" style="font-size:.875rem;">
        Não tens conta? <a href="<?= url('registar.php') ?>" style="color:var(--ace);font-weight:600;">Registar grátis</a>
      </p>
    </div>

    <?php if ($erro): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.84rem;">
      <i data-feather="alert-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
      <?= h($erro) ?>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <?php if ($next): ?><input type="hidden" name="next" value="<?= h($next) ?>"><?php endif; ?>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required autofocus
          placeholder="o.teu@email.com"
          value="<?= h($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-4">
        <div class="d-flex justify-content-between mb-1">
          <label class="form-label mb-0">Senha</label>
          <a href="<?= url('recuperar.php') ?>" style="font-size:.8rem;color:var(--sec);">Esqueci a senha</a>
        </div>
        <input type="password" name="senha" class="form-control" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn w-100 py-2 fw-bold" style="background:var(--ace);color:#fff;font-size:.95rem;">
        Entrar
      </button>
    </form>

    <!-- Separador -->
    <div class="d-flex align-items-center gap-2 my-3">
      <div class="flex-grow-1" style="height:1px;background:#e4e9f0;"></div>
      <span style="font-size:.78rem;color:#94a3b8;">ou</span>
      <div class="flex-grow-1" style="height:1px;background:#e4e9f0;"></div>
    </div>

    <div class="d-grid gap-2">
      <a href="<?= url('registar.php?tipo=candidato') ?>" class="btn btn-outline-secondary btn-sm">
        <i data-feather="user" style="width:14px;height:14px;" class="me-1"></i>Registar como Candidato
      </a>
      <a href="<?= url('registar.php?tipo=empresa') ?>" class="btn btn-outline-secondary btn-sm">
        <i data-feather="briefcase" style="width:14px;height:14px;" class="me-1"></i>Registar como Empresa
      </a>
    </div>

    <div class="mt-4 text-center" style="font-size:.77rem;color:#94a3b8;">
      <a href="<?= url('index.php') ?>" style="color:#94a3b8;">← Voltar ao site</a>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<script>feather.replace({'stroke-width':1.75});</script>
</body>
</html>
