<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
if (!loggedIn() || ($_SESSION['tipo']??'') !== 'candidato') { echo json_encode(['erro'=>'Não autenticado']); exit; }
$cand=meCandidato(); $vid=(int)($_POST['vaga_id']??0);
if (!$cand || !$vid) { echo json_encode(['erro'=>'Inválido']); exit; }
try {
    $existe=DB::val("SELECT COUNT(*) FROM vagas_guardadas WHERE candidato_id=? AND vaga_id=?",[$cand['id'],$vid]);
    if ($existe) { DB::exec("DELETE FROM vagas_guardadas WHERE candidato_id=? AND vaga_id=?",[$cand['id'],$vid]); echo json_encode(['guardada'=>false]); }
    else { DB::exec("INSERT IGNORE INTO vagas_guardadas (candidato_id,vaga_id) VALUES (?,?)",[$cand['id'],$vid]); echo json_encode(['guardada'=>true]); }
} catch(Exception $e) { echo json_encode(['erro'=>'Erro interno']); }
