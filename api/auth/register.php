<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido.'], 405);
}

$d      = corpo();
$nome   = trim($d['nome']   ?? '');
$email  = trim($d['email']  ?? '');
$senha  = trim($d['senha']  ?? '');
$perfil = trim($d['perfil'] ?? '');

if (!$nome || !$email || !$senha || !$perfil) {
    responder(['erro' => 'Preencha todos os campos.'], 400);
}

$conn = conectar();

$stmt = $conn->prepare('SELECT id_usuario FROM usuario WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    responder(['erro' => 'E-mail já cadastrado.'], 409);
}

$hash = password_hash($senha, PASSWORD_BCRYPT);
$stmt = $conn->prepare(
    'INSERT INTO usuario (nome, email, senha_hash, perfil) VALUES (?, ?, ?, ?)'
);
$stmt->bind_param('ssss', $nome, $email, $hash, $perfil);
$stmt->execute();

responder(['sucesso' => true, 'id' => $conn->insert_id], 201);
?>