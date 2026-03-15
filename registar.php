<?php
require_once 'includes/config.php';
if (loggedIn()) { header("Location: " . url('index.php')); exit; }
$cfg  = allCfg();
$tipo = $_GET['tipo'] ?? 'candidato';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo   = $_POST['tipo']   ?? 'candidato';
    $nome   = trim($_POST['nome']   ?? '');
    $email  = trim($_POST['email']  ?? '');
    $senha  = $_POST['senha']  ?? '';
    $senha2 = $_POST['senha2'] ?? '';
    $aceita = !empty($_POST['aceita']);

    if (!$aceita)           $erro = 'Deves aceitar os termos de uso.';
    elseif (!$nome||!$email) $erro = 'Preenche todos os campos.';
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) $erro = 'Email inválido.';
    elseif (strlen($senha)<8)  $erro = 'A senha deve ter pelo menos 8 caracteres.';
    elseif ($senha!==$senha2)  $erro = 'As senhas não coincidem.';
    elseif (DB::val("SELECT COUNT(*) FROM utilizadores WHERE email=?",[$email])) $erro = 'Este email já está registado.';
    else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $uid  = DB::insert(
            "INSERT INTO utilizadores (nome,email,password,tipo,email_verificado) VALUES (?,?,?,?,1)",
            [$nome,$email,$hash,$tipo]
        );
        if ($tipo === 'candidato') {
            DB::insert("INSERT INTO candidatos (utilizador_id) VALUES (?)", [$uid]);
            session_regenerate_id(true);
            $_SESSION['uid']  = $uid;
            $_SESSION['tipo'] = 'candidato';
            $_SESSION['nome'] = $nome;
            redirect('candidato/index.php','Bem-vindo! Completa o teu perfil para te candidatares.');
        } else {
            $nome_emp   = trim($_POST['nome_empresa'] ?? $nome);
            $slug_emp   = slugUnico('empresas', $nome_emp);
            $estado_emp = ($cfg['aprovacao_empresa']??'1') === '1' ? 'pendente' : 'aprovada';
            DB::insert("INSERT INTO empresas (utilizador_id,nome,slug,estado) VALUES (?,?,?,?)",
                [$uid,$nome_emp,$slug_emp,$estado_emp]);
            session_regenerate_id(true);
            $_SESSION['uid']  = $uid;
            $_SESSION['tipo'] = 'empresa';
            $_SESSION['nome'] = $nome;
            $msg = $estado_emp==='pendente'
                ? 'Conta criada! Aguarda a aprovação do administrador para publicar vagas.'
                : 'Empresa registada! Podes começar a publicar vagas.';
            redirect('empresa/index.php', $msg);
        }
    }
}

$cor_pri = $cfg['cor_primaria']??'#0a2540';
$cor_ace = $cfg['cor_acento']??'#e63946';
$cor_sec = $cfg['cor_secundaria']??'#457b9d';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="theme-color" content="<?= h($cor_pri) ?>">
<title>Registar — <?= h($cfg['site_nome']??'Emprega') ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<style>
:root{ --pri:<?= h($cor_pri) ?>; --ace:<?= h($cor_ace) ?>; --sec:<?= h($cor_sec) ?>; }
.reg-page { min-height:100vh; display:flex; align-items:stretch; }
.reg-left  { background:linear-gradient(135deg,var(--pri) 0%,#1a3a5c 50%,var(--sec) 100%); flex:0 0 38%; display:flex; flex-direction:column; justify-content:center; padding:3rem 2.5rem; }
.reg-right { flex:1; display:flex; align-items:center; justify-content:center; padding:2rem 1.5rem; background:#f8f9fc; overflow-y:auto; }
.reg-box   { background:#fff; border-radius:16px; padding:2.25rem 2rem; width:100%; max-width:480px; box-shadow:0 4px 24px rgba(0,0,0,.08); }
.tipo-btn  { flex:1; padding:.65rem 1rem; border:2px solid #e4e9f0; border-radius:10px; background:#fff; font-weight:600; font-size:.9rem; cursor:pointer; transition:all .2s; text-align:center; }
.tipo-btn.active { border-color:var(--ace); background:#fff0f1; color:var(--ace); }
@media(max-width:991.98px){ .reg-left{ display:none; } }
</style>
</head>
<body>
<div class="reg-page">
  <!-- LADO ESQUERDO -->
  <div class="reg-left">
    <div>
      <a href="<?= url('index.php') ?>" style="font-family:'Clash Display','DM Sans',sans-serif;font-size:1.8rem;font-weight:700;color:#fff;text-decoration:none;display:block;margin-bottom:1.5rem;">
        <?= h($cfg['site_nome']??'Emprega') ?><span style="color:#e9c46a;">.</span>
      </a>
      <h2 style="color:#fff;font-size:1.6rem;margin-bottom:1rem;line-height:1.3;">
        <?= $tipo==='empresa' ? 'Publica as tuas vagas grátis' : 'Começa a tua jornada profissional' ?>
      </h2>
      <?php $beneficios = $tipo==='empresa'
        ? ['Publica vagas gratuitamente','Recebe candidaturas qualificadas','Gere candidatos facilmente','Encontra o talento certo']
        : ['Cria o teu perfil profissional','Candidata-te a centenas de vagas','Guarda vagas favoritas','Recebe alertas de novos empregos'];
      foreach ($beneficios as $b): ?>
      <div class="d-flex align-items-center gap-2 mb-2" style="color:rgba(255,255,255,.85);font-size:.9rem;">
        <div style="width:20px;height:20px;border-radius:50%;background:rgba(230,57,70,.8);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i data-feather="check" style="width:11px;height:11px;color:#fff;"></i>
        </div>
        <?= $b ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- LADO DIREITO -->
  <div class="reg-right">
    <div class="reg-box">
      <div class="text-center mb-4">
        <a href="<?= url('index.php') ?>" style="font-family:'Clash Display','DM Sans',sans-serif;font-size:1.5rem;font-weight:700;color:var(--pri);text-decoration:none;display:inline-block;margin-bottom:.5rem;">
          <?= h($cfg['site_nome']??'Emprega') ?><span style="color:var(--ace);">.</span>
        </a>
        <h4 class="fw-bold mb-1" style="color:var(--pri);">Criar Conta Grátis</h4>
        <p class="text-muted mb-0" style="font-size:.875rem;">
          Já tens conta? <a href="<?= url('login.php') ?>" style="color:var(--ace);font-weight:600;">Entrar</a>
        </p>
      </div>

      <?php if ($erro): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.84rem;">
        <i data-feather="alert-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        <?= h($erro) ?>
      </div>
      <?php endif; ?>

      <!-- Tipo de conta -->
      <div class="d-flex gap-2 mb-4">
        <button type="button" class="tipo-btn <?= $tipo==='candidato'?'active':'' ?>" onclick="setTipo('candidato')">
          <i data-feather="user" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"></i>Candidato
        </button>
        <button type="button" class="tipo-btn <?= $tipo==='empresa'?'active':'' ?>" onclick="setTipo('empresa')">
          <i data-feather="briefcase" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"></i>Empresa
        </button>
      </div>

      <form method="POST" novalidate>
        <input type="hidden" name="tipo" id="inputTipo" value="<?= h($tipo) ?>">

        <div class="mb-3" id="campoEmpresa" style="<?= $tipo==='candidato'?'display:none;':'' ?>">
          <label class="form-label">Nome da Empresa *</label>
          <input type="text" name="nome_empresa" class="form-control"
            placeholder="Nome oficial da empresa"
            value="<?= h($_POST['nome_empresa']??'') ?>">
        </div>

        <div class="mb-3">
          <label class="form-label" id="labelNome">
            <?= $tipo==='empresa' ? 'Nome do Responsável *' : 'Nome Completo *' ?>
          </label>
          <input type="text" name="nome" class="form-control" required
            value="<?= h($_POST['nome']??'') ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Email *</label>
          <input type="email" name="email" class="form-control" required
            placeholder="o.teu@email.com"
            value="<?= h($_POST['email']??'') ?>">
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Senha *</label>
            <input type="password" name="senha" class="form-control" required minlength="8" placeholder="Mín. 8 caracteres">
          </div>
          <div class="col-6">
            <label class="form-label">Confirmar *</label>
            <input type="password" name="senha2" class="form-control" required placeholder="Repetir senha">
          </div>
        </div>

        <div class="mb-3 d-flex align-items-start gap-2" style="font-size:.84rem;">
          <input type="checkbox" name="aceita" id="aceita" class="form-check-input mt-1" style="flex-shrink:0;" required>
          <label for="aceita" style="cursor:pointer;">
            Aceito os <a href="#" style="color:var(--ace);">Termos de Uso</a> e
            a <a href="#" style="color:var(--ace);">Política de Privacidade</a>
          </label>
        </div>

        <button type="submit" class="btn w-100 py-2 fw-bold" style="background:var(--ace);color:#fff;font-size:.95rem;">
          Criar Conta Grátis
        </button>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<script>
feather.replace({'stroke-width':1.75});
function setTipo(t) {
  document.getElementById('inputTipo').value = t;
  document.querySelectorAll('.tipo-btn').forEach((b,i)=>{
    b.classList.toggle('active',(i===0&&t==='candidato')||(i===1&&t==='empresa'));
  });
  document.getElementById('campoEmpresa').style.display = t==='empresa'?'':'none';
  document.getElementById('labelNome').textContent = t==='empresa'?'Nome do Responsável *':'Nome Completo *';
}
</script>
</body>
</html>
