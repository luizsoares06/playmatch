<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'playmatch');

function conectar() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['erro' => 'Falha na conexão: ' . $conn->connect_error]);
        exit();
    }
    return $conn;
}

function responder($dados, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

function corpo() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}
?>