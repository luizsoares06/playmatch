<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido.'], 405);
}

$d          = corpo();
$id_partida = intval($d['id_partida'] ?? 0);
$placar_a   = $d['placar_a'] ?? null;
$placar_b   = $d['placar_b'] ?? null;

if (!$id_partida || $placar_a === null || $placar_b === null) {
    responder(['erro' => 'Informe id_partida, placar_a e placar_b.'], 400);
}

$conn = conectar();
$stmt = $conn->prepare(
    "UPDATE partida SET placar_a=?, placar_b=?, status='finalizada' WHERE id_partida=?"
);
$stmt->bind_param('iii', intval($placar_a), intval($placar_b), $id_partida);
$stmt->execute();

responder(['sucesso' => true]);
?>