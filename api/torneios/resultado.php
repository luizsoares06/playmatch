<?php
// Importa o arquivo config.php que está na pasta pai (../)
// Traz as funções: conectar(), responder() e corpo()
// require_once garante que o arquivo só é carregado uma vez
require_once '../config.php';

// Verifica se o método da requisição é POST
// Registrar resultado modifica dados no banco, então usa POST
// 405 = Method Not Allowed (método não permitido)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido.'], 405);
}

// corpo() lê o JSON enviado pelo frontend no corpo da requisição
// Exemplo do que chega:
// {"id_partida": 3, "placar_a": 13, "placar_b": 7}
$d = corpo();

// intval() converte para número inteiro
// ?? 0 retorna 0 se o campo não existir no array
// ID 0 não existe no banco (AUTO_INCREMENT começa em 1)
// então 0 indica que o campo não foi enviado
$id_partida = intval($d['id_partida'] ?? 0);

// Para os placares usamos ?? null em vez de ?? 0
// MOTIVO IMPORTANTE: placar 0 (zero) é um valor válido!
// Se usássemos ?? 0 não saberíamos se o usuário mandou
// o placar 0 ou se o campo simplesmente não foi enviado
// null indica com certeza que o campo está ausente
$placar_a = $d['placar_a'] ?? null;
$placar_b = $d['placar_b'] ?? null;

// Valida os três campos obrigatórios
// !$id_partida: id 0 é inválido — false no PHP
// === null: comparação ESTRITA — verifica especificamente null
// Não usamos !$placar_a porque 0 seria falsy e rejeitaria
// um placar zero válido (ex: time perdeu de 13 a 0)
if (!$id_partida || $placar_a === null || $placar_b === null) {
    // 400 = Bad Request (dados inválidos ou ausentes)
    responder(['erro' => 'Informe id_partida, placar_a e placar_b.'], 400);
}

// Abre a conexão com o banco de dados MySQL
$conn = conectar();

// UPDATE modifica registros que já existem no banco
// SET define quais colunas serão atualizadas:
// placar_a=? recebe o placar do time A (dinâmico)
// placar_b=? recebe o placar do time B (dinâmico)
// status='finalizada' é valor fixo — sempre marca como finalizada
// quando um resultado é registrado
// WHERE id_partida=? garante que só atualiza a partida correta
// SEM o WHERE, atualizaria TODAS as partidas do banco!
$stmt = $conn->prepare(
    "UPDATE partida SET placar_a=?, placar_b=?, status='finalizada' WHERE id_partida=?"
);

// Vincula os 3 valores aos 3 placeholders
// 'iii' = três integers (números inteiros)
// intval() converte $placar_a e $placar_b para inteiro aqui também
// garantindo que não há strings ou decimais nos placares
// A ordem é: placar_a, placar_b, id_partida
// (mesma ordem dos ? no SQL acima)
$stmt->bind_param('iii', intval($placar_a), intval($placar_b), $id_partida);

// Executa o UPDATE no banco de dados
// Modifica os placares e o status da partida
$stmt->execute();

// Retorna sucesso — HTTP 200 padrão
// O frontend receberá: {"sucesso": true}
responder(['sucesso' => true]);
?>