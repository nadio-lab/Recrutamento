<?php
// ============================================================
// EMPREGA — config.php  (caminhos automáticos)
// ============================================================

// ── BASE DE DADOS ── altere apenas estas 4 linhas ──────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'emprega_db');
// ──────────────────────────────────────────────────────────

define('VERSION', '1.0.0');

// ── DETECÇÃO AUTOMÁTICA DO CAMINHO BASE ───────────────────
// Funciona em qualquer servidor: localhost, 000webhost, etc.
// Calcula o caminho correto a partir da localização deste ficheiro

if (!defined('BASE_PATH')) {
    // __DIR__ = .../recrutamento/includes
    // Sobe um nível para chegar à raiz do projecto
    define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

if (!defined('BASE_URL')) {
    // Detecta automaticamente: http://localhost/recrutamento/ OU https://meusite.ao/
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Caminho do script em relação à raiz do servidor
    // Exemplo: /recrutamento/admin/index.php → /recrutamento/
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

    // Subir directórios até encontrar a raiz do projecto (onde está index.php)
    // Ficheiros estão em: /recrutamento/, /recrutamento/admin/, /recrutamento/empresa/, etc.
    // Precisamos sempre apontar para /recrutamento/
    $raiz = realpath(BASE_PATH);
    $doc_root = realpath($_SERVER['DOCUMENT_ROOT'] ?? '/');
    $rel_path = str_replace('\\', '/', str_replace($doc_root, '', $raiz));
    if ($rel_path && $rel_path[0] !== '/') $rel_path = '/' . $rel_path;
    if ($rel_path && substr($rel_path, -1) !== '/') $rel_path .= '/';
    if (empty($rel_path) || $rel_path === '//') $rel_path = '/';

    define('BASE_URL', $protocol . '://' . $host . $rel_path);
}

define('UPLOAD_DIR', BASE_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Fuso horário
date_default_timezone_set('Africa/Luanda');

// Sessão
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7,   // 7 dias
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── DB ──────────────────────────────────────────────────────
class DB {
    private static ?PDO $c = null;
    public static function conn(): PDO {
        if (!self::$c) {
            try {
                self::$c = new PDO(
                    "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
                    DB_USER, DB_PASS,
                    [PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                     PDO::ATTR_EMULATE_PREPARES   => false]
                );
            } catch (PDOException $e) {
                die('
<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Erro de Ligação</title>
<style>body{font-family:sans-serif;padding:3rem;color:#c00;background:#fff8f8;}
h2{color:#c00;}code{background:#fee;padding:.2rem .4rem;border-radius:4px;}</style>
</head><body>
<h2>⚠ Erro de Ligação à Base de Dados</h2>
<p>Não foi possível ligar ao MySQL. Verifique as configurações em <code>includes/config.php</code>:</p>
<ul>
  <li>DB_HOST: <code>' . DB_HOST . '</code></li>
  <li>DB_NAME: <code>' . DB_NAME . '</code></li>
  <li>DB_USER: <code>' . DB_USER . '</code></li>
</ul>
<p>Mensagem do servidor: <code>' . htmlspecialchars($e->getMessage()) . '</code></p>
<p>Certifique-se de que correu o ficheiro <code>database.sql</code> para criar a base de dados.</p>
</body></html>');
            }
        }
        return self::$c;
    }
    public static function q(string $sql, array $p=[]): PDOStatement { $s=self::conn()->prepare($sql); $s->execute($p); return $s; }
    public static function row(string $sql, array $p=[]): ?array     { return self::q($sql,$p)->fetch()?:null; }
    public static function all(string $sql, array $p=[]): array      { return self::q($sql,$p)->fetchAll(); }
    public static function insert(string $sql, array $p=[]): int     { self::q($sql,$p); return (int)self::conn()->lastInsertId(); }
    public static function exec(string $sql, array $p=[]): int       { return self::q($sql,$p)->rowCount(); }
    public static function val(string $sql, array $p=[]): mixed      { $r=self::q($sql,$p)->fetch(PDO::FETCH_NUM); return $r?$r[0]:null; }
}

// ── CONFIG DO SITE ──────────────────────────────────────────
function cfg(string $k, string $d=''): string {
    try { $r=DB::row("SELECT valor FROM configuracoes WHERE chave=?",[$k]); return $r?$r['valor']:$d; }
    catch(Exception $e){ return $d; }
}
function allCfg(): array {
    try { return array_column(DB::all("SELECT chave,valor FROM configuracoes"),'valor','chave'); }
    catch(Exception $e){ return []; }
}

// ── HELPERS ─────────────────────────────────────────────────
function h(mixed $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function url(string $caminho = ''): string {
    // Gera URLs absolutas a partir da raiz do site
    // url('login.php')           → http://localhost/recrutamento/login.php
    // url('admin/index.php')     → http://localhost/recrutamento/admin/index.php
    return BASE_URL . ltrim($caminho, '/');
}

function urlAsset(string $ficheiro): string {
    return BASE_URL . 'assets/' . ltrim($ficheiro, '/');
}

function urlUpload(string $pasta, string $ficheiro): string {
    return BASE_URL . 'uploads/' . ltrim($pasta, '/') . '/' . $ficheiro;
}

function slug(string $s): string {
    $map = ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i',
            'ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c','ñ'=>'n',
            'Á'=>'a','À'=>'a','Ã'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ç'=>'c'];
    $s = strtr(strtolower(trim($s)), $map);
    $s = preg_replace('/[^a-z0-9\s-]/', '', $s);
    $s = preg_replace('/[\s-]+/', '-', $s);
    return trim($s, '-');
}

function slugUnico(string $tabela, string $titulo, int $excluir=0): string {
    $base = slug($titulo); $s = $base; $i = 1;
    while (DB::val("SELECT COUNT(*) FROM `{$tabela}` WHERE slug=? AND id!=?",[$s,$excluir])) {
        $s = $base . '-' . $i++;
    }
    return $s;
}

function redirect(string $caminho, string $msg='', string $tipo='ok'): void {
    // Aceita caminho relativo ('login.php') ou URL absoluta
    if ($msg) $_SESSION['flash'] = ['msg'=>$msg,'tipo'=>$tipo];
    // Se começa com http é URL absoluta, senão é relativa à raiz do site
    $destino = (strpos($caminho,'http')===0) ? $caminho : url($caminho);
    header("Location: $destino");
    exit;
}

function flash(): string {
    if (empty($_SESSION['flash'])) return '';
    $f = $_SESSION['flash']; unset($_SESSION['flash']);
    $msg = h($f['msg']);
    $cls = $f['tipo']==='ok'?'success':($f['tipo']==='erro'?'danger':'warning');
    $ico = $f['tipo']==='ok'?'check-circle':($f['tipo']==='erro'?'x-circle':'alert-triangle');
    return "<div class='alert alert-{$cls} d-flex align-items-center gap-2 alert-dismissible fade show mb-3' role='alert'>
        <i data-feather='{$ico}' style='width:16px;height:16px;flex-shrink:0;'></i>
        <span>{$msg}</span>
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
}

function tempo(string $data): string {
    if (!$data) return '—';
    $diff = time() - strtotime($data);
    if ($diff < 60)     return 'agora mesmo';
    if ($diff < 3600)   return floor($diff/60).'min atrás';
    if ($diff < 86400)  return floor($diff/3600).'h atrás';
    if ($diff < 604800) return floor($diff/86400).'d atrás';
    return date('d/m/Y', strtotime($data));
}

function formatSalario(?float $min, ?float $max, string $moeda='AOA', int $visivel=1): string {
    if (!$visivel) return '<span class="text-muted" style="font-size:.82rem;">Salário não divulgado</span>';
    if (!$min && !$max) return '<span class="text-muted" style="font-size:.82rem;">A combinar</span>';
    $fmt = fn($v) => number_format($v, 0, ',', '.') . ' ' . $moeda;
    if ($min && $max) return $fmt($min) . ' — ' . $fmt($max);
    if ($min)         return 'Desde ' . $fmt($min);
    return 'Até ' . $fmt($max);
}

function uploadFicheiro(array $file, string $pasta, array $tipos, int $maxMB=5): array {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK)
        return ['ok'=>false,'msg'=>'Erro no upload (código: '.($file['error']??'?').')'];
    if ($file['size'] > $maxMB*1024*1024)
        return ['ok'=>false,'msg'=>"Ficheiro demasiado grande. Máximo {$maxMB}MB."];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $tipos))
        return ['ok'=>false,'msg'=>'Tipo de ficheiro não permitido. Aceites: '.implode(', ',$tipos)];
    $dir = UPLOAD_DIR . $pasta . DIRECTORY_SEPARATOR;
    if (!is_dir($dir) && !mkdir($dir, 0755, true))
        return ['ok'=>false,'msg'=>'Erro ao criar directório de upload.'];
    $nome = uniqid('', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir.$nome))
        return ['ok'=>false,'msg'=>'Erro ao guardar o ficheiro no servidor.'];
    return ['ok'=>true,'nome'=>$nome,'ext'=>$ext];
}

function notificar(int $uid, string $tipo, string $titulo, string $msg='', string $link=''): void {
    if (!$uid) return;
    try {
        DB::insert("INSERT INTO notificacoes (utilizador_id,tipo,titulo,mensagem,link) VALUES (?,?,?,?,?)",
            [$uid,$tipo,$titulo,$msg,$link]);
    } catch(Exception $e) {}
}

// ── AUTH ────────────────────────────────────────────────────
function loggedIn(): bool { return !empty($_SESSION['uid']); }

function requireAuth(string $tipo='', string $back=''): void {
    if (!loggedIn()) {
        $next = $back ?: ($_SERVER['REQUEST_URI'] ?? '');
        redirect('login.php' . ($next ? '?next='.urlencode($next) : ''));
    }
    if ($tipo && ($_SESSION['tipo']??'') !== $tipo) {
        redirect('index.php', 'Acesso não autorizado.', 'erro');
    }
}

function me(): ?array {
    if (!loggedIn()) return null;
    return DB::row("SELECT * FROM utilizadores WHERE id=?", [$_SESSION['uid']]);
}

function meEmpresa(): ?array {
    if (!loggedIn()) return null;
    return DB::row(
        "SELECT e.*, u.email, u.nome as nome_user FROM empresas e
         JOIN utilizadores u ON u.id=e.utilizador_id
         WHERE e.utilizador_id=?", [$_SESSION['uid']]
    );
}

function meCandidato(): ?array {
    if (!loggedIn()) return null;
    return DB::row(
        "SELECT c.*, u.nome, u.email FROM candidatos c
         JOIN utilizadores u ON u.id=c.utilizador_id
         WHERE c.utilizador_id=?", [$_SESSION['uid']]
    );
}

function totalNotifs(): int {
    if (!loggedIn()) return 0;
    return (int)(DB::val("SELECT COUNT(*) FROM notificacoes WHERE utilizador_id=? AND lida=0",
        [$_SESSION['uid']]) ?? 0);
}

// ── ETIQUETAS ───────────────────────────────────────────────
function labelContrato(string $v): string {
    return match($v) {
        'efectivo'  => 'Efectivo',   'contrato'   => 'Contrato a prazo',
        'part_time' => 'Part-time',  'freelance'  => 'Freelance',
        'estagio'   => 'Estágio',    'voluntario' => 'Voluntariado',
        default     => ucfirst($v)
    };
}
function labelModalidade(string $v): string {
    return match($v) { 'presencial'=>'Presencial','remoto'=>'Remoto','hibrido'=>'Híbrido', default=>$v };
}
function labelExperiencia(string $v): string {
    return match($v) {
        'sem_experiencia'=>'Sem experiência','junior'=>'Júnior (1–2 anos)',
        'medio'=>'Médio (3–5 anos)','senior'=>'Sénior (5+ anos)','diretor'=>'Diretor / Gestor',
        default=>$v
    };
}
function estadoCandidaturaLabel(string $e): array {
    return match($e) {
        'enviada'    => ['Enviada',     'secondary'],
        'vista'      => ['Vista',       'info'],
        'em_analise' => ['Em análise',  'warning'],
        'entrevista' => ['Entrevista',  'primary'],
        'oferta'     => ['Oferta',      'success'],
        'aceite'     => ['Aceite',      'success'],
        'rejeitada'  => ['Rejeitada',   'danger'],
        'retirada'   => ['Retirada',    'secondary'],
        default      => [$e,            'secondary'],
    };
}

// ── HELPER DE ASSETS (caminhos para CSS/JS/IMG) ─────────────
// Calcula o caminho relativo dos assets a partir de qualquer subdirectório
function assetPath(string $ficheiro): string {
    // Calcula quantos níveis subir com base no SCRIPT_NAME actual
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $depth  = substr_count(str_replace('\\','/',dirname($script)), '/');
    $base_depth = substr_count(rtrim(str_replace('\\','/',parse_url(BASE_URL,PHP_URL_PATH)),'/'), '/');
    $levels = max(0, $depth - $base_depth);
    $prefix = str_repeat('../', $levels);
    return $prefix . 'assets/' . ltrim($ficheiro, '/');
}

function uploadPath(string $pasta, string $nome): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $depth  = substr_count(str_replace('\\','/',dirname($script)), '/');
    $base_depth = substr_count(rtrim(str_replace('\\','/',parse_url(BASE_URL,PHP_URL_PATH)),'/'), '/');
    $levels = max(0, $depth - $base_depth);
    $prefix = str_repeat('../', $levels);
    return $prefix . 'uploads/' . $pasta . '/' . $nome;
}
