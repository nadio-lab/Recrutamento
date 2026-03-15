<?php
require_once 'includes/config.php';

if (loggedIn()) {
    $dest = match($_SESSION['tipo']??'') {'admin'=>'admin/index.php','empresa'=>'empresa/index.php',default=>'candidato/index.php'};
    redirect(BASE_URL.$dest);
}

$cfg = allCfg();
$erro = '';
$sucesso = '';
$link_teste = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (!$email) {
        $erro = 'Por favor, introduz o teu email.';
    } else {
        $user = DB::row("SELECT id, nome FROM utilizadores WHERE email=? AND ativo=1", [$email]);
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            DB::exec("UPDATE utilizadores SET reset_token=?, reset_expira=? WHERE id=?", [$token, $expira, $user['id']]);
            $url_recuperacao = BASE_URL . "redefinir.php?token=" . $token;

            $sucesso = 'As instruções foram enviadas para o teu e-mail.';
            $link_teste = $url_recuperacao; 
        } else {
            // Por segurança, mantemos a mesma mensagem
            $sucesso = 'Se o e-mail estiver registado, receberás instruções em breve.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Recuperar Senha — <?= h($cfg['site_nome']??'Portal') ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="stylesheet">
    <?php if(!empty($cfg['site_logo'])): ?>
  <link rel="icon" type="image/png" href="<?= url('uploads/site/'.$cfg['site_logo']) ?>">
<?php endif; ?>

    <style>
        :root {
            --pri: <?= h($cfg['cor_primaria']??'#0a2540') ?>;
            --ace: <?= h($cfg['cor_acento']??'#e63946') ?>;
            --sec: <?= h($cfg['cor_secundaria']??'#457b9d') ?>;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f7fa;
            margin: 0;
        }
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--pri) 0%, #1a3a5c 100%);
            padding: 20px;
        }
        .auth-card {
            background: #fff;
            width: 100%;
            max-width: 400px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            padding: 40px 30px;
        }
        .brand-logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--pri);
            text-decoration: none;
            letter-spacing: -1px;
        }
        .brand-logo span { color: var(--ace); }
        
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #edf2f7;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--sec);
            box-shadow: none;
            background: #fcfdfe;
        }
        .btn-recuperar {
            background: var(--ace);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 700;
            transition: transform 0.2s;
        }
        .btn-recuperar:hover {
            background: #d62828;
            color: #fff;
            transform: translateY(-2px);
        }
        .alert {
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
        }
        .back-link {
            color: #718096;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--pri); }
        
        /* Estilo do Link de Teste */
        .test-link-box {
            background: #fff5f5;
            border: 2px dashed #feb2b2;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <a href="index.php" class="brand-logo">
                <?= h($cfg['site_nome']??'Emprega') ?><span>.</span>
            </a>
            <p class="text-muted mt-2">Recuperação de acesso</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <svg xmlns="http://www.w3.org" width="18" height="18" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                </svg>
                <?= h($erro) ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org" width="18" height="18" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                <?= h($sucesso) ?>
            </div>
            
            <?php if ($link_teste): ?>
                <div class="test-link-box small">
                    <div class="fw-bold text-danger mb-1">Link de Recuperação (Modo Teste):</div>
                    <a href="<?= $link_teste ?>" class="text-break"><?= $link_teste ?></a>
                </div>
            <?php endif; ?>

            <div class="mt-4 text-center">
                <a href="login.php" class="btn btn-dark w-100 py-2 border-0" style="border-radius:10px;">Ir para Login</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">Email de Registo</label>
                    <input type="email" name="email" class="form-control" 
                           required autofocus placeholder="Introduz o teu email">
                    <div class="form-text mt-2" style="font-size: 0.75rem;">
                        Enviaremos um link seguro para redefinires a tua senha.
                    </div>
                </div>
                <button type="submit" class="btn btn-recuperar w-100 shadow-sm">
                    Enviar Instruções
                </button>
            </form>
            
            <div class="mt-4 pt-3 border-top text-center">
                <a href="login.php" class="back-link">
                    &larr; Voltar ao Login
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net"></script>
</body>
</html>
