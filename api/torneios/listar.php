<?php
require_once '../config.php';

$conn = conectar();

$texto      = trim($_GET['texto']      ?? '');
$data_ini   = trim($_GET['data_ini']   ?? '');
$data_fim   = trim($_GET['data_fim']   ?? '');
$modalidade = trim($_GET['modalidade'] ?? '');
$status     = trim($_GET['status']     ?? '');

$sql    = 'SELECT * FROM torneio WHERE 1=1';
$tipos  = '';
$params = [];

if ($texto) {
    $sql .= ' AND nome LIKE ?';
    $tipos .= 's';
    $params[] = "%$texto%";
}
if ($data_ini) {
    $sql .= ' AND data_inicio >= ?';
    $tipos .= 's';
    $params[] = $data_ini;
}
if ($data_fim) {
    $sql .= ' AND data_inicio <= ?';
    $tipos .= 's';
    $params[] = $data_fim;
}
if ($modalidade) {
    $sql .= ' AND modalidade = ?';
    $tipos .= 's';
    $params[] = $modalidade;
}
if ($status) {
    $sql .= ' AND status = ?';
    $tipos .= 's';
    $params[] = $status;
}

$sql .= ' ORDER BY criado_em DESC';

$stmt = $conn->prepare($sql);
if ($tipos) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

$torneios = [];
while ($row = $resultado->fetch_assoc()) {
    $torneios[] = $row;
}

responder($torneios);
?>