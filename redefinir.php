<?php
require_once 'includes/config.php';

$cfg = allCfg();
$erro = '';
$sucesso = '';
$token = $_GET['token'] ?? $_POST['token'] ?? '';

// 1. Validar se o token existe e é válido
if (!$token) {
    die("Acesso inválido. O link de recuperação está incompleto.");
}

// Procura o utilizador com este token e verifica se ainda não expirou
$user = DB::row("SELECT id FROM utilizadores WHERE reset_token = ? AND reset_expira > NOW()", [$token]);

if (!$user) {
    $erro = "O link de recuperação é inválido ou já expirou. Por favor, peça um novo.";
}

// 2. Processar a nova senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $nova_senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';

    if (strlen($nova_senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } elseif ($nova_senha !== $confirma_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        // Encriptar a nova senha
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        // Atualizar a senha e LIMPAR o token para não ser usado outra vez
        DB::exec("UPDATE utilizadores SET password = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?", [$hash, $user['id']]);
        
        $sucesso = "Senha atualizada com sucesso! Já podes entrar.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Nova Senha — <?= h($cfg['site_nome']??'Emprega') ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net">
<link rel="stylesheet" href="assets/css/style.css">
<?php if(!empty($cfg['site_logo'])): ?>
  <link rel="icon" type="image/png" href="<?= url('uploads/site/'.$cfg['site_logo']) ?>">
<?php endif; ?>

<style>
/* Reset e Base */
:root {
    --pri: <?= h($cfg['cor_primaria'] ?? '#0a2540') ?>;
    --ace: <?= h($cfg['cor_acento'] ?? '#e63946') ?>;
    --sec: <?= h($cfg['cor_secundaria'] ?? '#457b9d') ?>;
}

body { background-color: #f8fafc; font-family: 'Inter', system-ui, -apple-system, sans-serif; }

/* Contentor Principal */
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--pri) 0%, #1e3a8a 100%);
    padding: 20px;
}

/* Cartão de Login */
.login-card {
    background: #ffffff;
    border-radius: 1.25rem;
    padding: 2.5rem;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Tipografia */
h5 { font-size: 1.5rem; letter-spacing: -0.025em; margin-bottom: 0.5rem; }
.text-muted { color: #64748b !important; }

/* Formulário e Inputs */
.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.5rem;
}

.form-control {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.2s;
    background-color: #fcfdfe;
}

.form-control:focus {
    border-color: var(--sec);
    box-shadow: 0 0 0 4px rgba(69, 123, 157, 0.1);
    outline: 0;
    background-color: #fff;
}

/* Botões */
.btn-ace {
    background-color: var(--ace);
    color: #fff;
    border: none;
    border-radius: 0.75rem;
    padding: 0.8rem;
    font-size: 1rem;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-ace:hover {
    background-color: #d62828;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);
    color: #fff;
}

/* Alertas */
.alert {
    border: none;
    border-radius: 0.75rem;
    font-weight: 500;
    padding: 0.75rem 1rem;
}

.alert-danger { background-color: #fef2f2; color: #991b1b; }
.alert-success { background-color: #f0fdf4; color: #166534; }

/* Link de Cancelar */
.back-link {
    color: #94a3b8;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.back-link:hover { color: var(--pri); }
</style>

<div class="login-page">
  <div class="login-card">
    <div class="text-center mb-4">
      <h5 class="fw-bold" style="color:var(--pri);">Definir Nova Senha</h5>
      <p class="text-muted">Escolha uma senha segura e fácil de lembrar.</p>
    </div>

    <?php if ($erro): ?>
      <div class="alert alert-danger mb-3"><?= h($erro) ?></div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
      <div class="alert alert-success mb-4"><?= h($sucesso) ?></div>
      <a href="login.php" class="btn btn-ace w-100 fw-bold shadow-sm">Ir para o Login</a>
    <?php elseif ($user): ?>
      <form method="POST">
        <input type="hidden" name="token" value="<?= h($token) ?>">
        <br>
        <div class="mb-3">
          <label class="form-label">Nova Senha</label>
          <input type="password" name="senha" class="form-control" required autofocus placeholder="Mínimo 6 caracteres">
        </div>
<br>
        <div class="mb-4">
          <label class="form-label">Confirmar Nova Senha</label>
          <input type="password" name="confirma_senha" class="form-control" required placeholder="Repita a senha">
        </div>
<br>
        <button type="submit" class="btn btn-ace w-100 fw-bold shadow-sm">Atualizar Senha</button>
      </form>
    <?php endif; ?>
<br>
    <div class="mt-4 pt-3 border-top text-center">
      <a href="login.php" class="back-link small">← Cancelar e voltar</a>
    </div>
  </div>
</div>
</body>
</html>
