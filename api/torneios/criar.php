<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido.'], 405);
}

$d = corpo();

$nome       = trim($d['nome']            ?? '');
$modalidade = trim($d['modalidade']      ?? '');
$formato    = trim($d['formato']         ?? '');
$data_ini   = trim($d['data_inicio']     ?? '');
$data_fim   = $d['data_fim']             ?? null;
$max_times  = intval($d['max_times']     ?? 0);
$descricao  = $d['descricao']            ?? null;
$premiacao  = $d['premiacao']            ?? null;
$id_org     = intval($d['id_organizador']?? 0) ?: null;

if (!$nome || !$modalidade || !$formato || !$data_ini || $max_times < 2) {
    responder(['erro' => 'Preencha os campos obrigatórios.'], 400);
}

$conn = conectar();
$stmt = $conn->prepare(
    'INSERT INTO torneio
     (nome, modalidade, formato, data_inicio, data_fim, max_times, descricao, premiacao, id_organizador)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('sssssissi',
    $nome, $modalidade, $formato,
    $data_ini, $data_fim, $max_times,
    $descricao, $premiacao, $id_org
);
$stmt->execute();

responder(['sucesso' => true, 'id_torneio' => $conn->insert_id], 201);
?>