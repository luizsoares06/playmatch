<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido.'], 405);
}

$d     = corpo();
$email = trim($d['email'] ?? '');
$senha = trim($d['senha'] ?? '');

if (!$email || !$senha) {
    responder(['erro' => 'Informe e-mail e senha.'], 400);
}

$conn = conectar();
$stmt = $conn->prepare('SELECT * FROM usuario WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
    responder(['erro' => 'E-mail ou senha inválidos.'], 401);
}

unset($usuario['senha_hash']);
responder(['sucesso' => true, 'usuario' => $usuario]);
?>