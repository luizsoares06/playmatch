<?php
// Importa o arquivo config.php que está na pasta pai (../)
// require_once garante que o arquivo só é carregado uma vez,
// mesmo que seja chamado várias vezes no mesmo script.
// Traz as funções: conectar(), responder() e corpo()
require_once '../config.php';

// $_SERVER é uma variável global do PHP que contém informações
// sobre a requisição HTTP que chegou ao servidor.
// REQUEST_METHOD retorna o método usado: GET, POST, PUT, etc.
// O !== significa "diferente de" — verifica se NÃO é POST.
// Login deve sempre usar POST porque envia dados sensíveis (senha).
// Se fosse GET, a senha apareceria visível na URL — inseguro!
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Se não for POST, responde com erro 405 = Method Not Allowed
    // Isso encerra a execução do script imediatamente
    responder(['erro' => 'Método não permitido.'], 405);
}

// corpo() lê o JSON enviado pelo frontend no corpo da requisição
// Retorna um array PHP com os dados enviados pelo navegador
// Exemplo do que chega: {"email":"joao@email.com","senha":"123456"}
$d = corpo();

// Extrai o e-mail do array $d
// ?? '' significa: se 'email' não existir no array, usa string vazia
// trim() remove espaços em branco do início e fim da string
// Evita erros como " joao@email.com " (com espaço acidental)
$email = trim($d['email'] ?? '');

// Mesma lógica para a senha
$senha = trim($d['senha'] ?? '');

// Validação: verifica se os dois campos foram preenchidos
// O ! (não) inverte o valor — string vazia é falsa, então !'' é true
// O || significa OR — se QUALQUER campo estiver vazio, entra no if
// 400 = Bad Request (o cliente enviou uma requisição inválida)
if (!$email || !$senha) {
    responder(['erro' => 'Informe e-mail e senha.'], 400);
}

// Chama a função conectar() do config.php
// Abre a conexão com o banco de dados MySQL
// $conn é o objeto de conexão usado para fazer consultas
$conn = conectar();

// prepare() cria uma consulta SQL preparada (Prepared Statement)
// O ? é um placeholder — será substituído pelo valor real depois
// Isso previne SQL Injection: se alguém digitar código SQL no campo
// de e-mail, ele será tratado como texto comum, não como código
$stmt = $conn->prepare('SELECT * FROM usuario WHERE email = ?');

// bind_param() vincula o valor real ao placeholder ?
// Primeiro argumento 's' = tipo do dado (s = string/texto)
// Segundo argumento = variável com o valor a substituir
// Outros tipos: 'i' = integer (número), 'd' = double (decimal)
$stmt->bind_param('s', $email);

// execute() executa a consulta SQL no banco de dados
// Nesse momento o banco procura o usuário com aquele e-mail
$stmt->execute();

// get_result() obtém o resultado da consulta
// fetch_assoc() pega a primeira linha como array associativo
// Exemplo: ['id_usuario'=>1, 'nome'=>'João', 'email'=>'...']
// Se não encontrar nenhum usuário com esse e-mail, retorna null
$usuario = $stmt->get_result()->fetch_assoc();

// Dupla verificação de segurança:
// 1) !$usuario: verifica se o usuário foi encontrado no banco
// 2) !password_verify(): compara a senha digitada com o hash salvo
//    Recria o processo de criptografia e compara os resultados
//    Retorna false se a senha não corresponder ao hash
// A mensagem é genérica por segurança — não revela se foi
// o e-mail ou a senha que está errado (evita ataques)
if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
    // 401 = Unauthorized (credenciais inválidas)
    responder(['erro' => 'E-mail ou senha inválidos.'], 401);
}

// unset() remove o campo senha_hash do array antes de enviar
// Nunca devemos enviar dados sensíveis para o frontend
// Mesmo criptografado, é boa prática não expor o hash
unset($usuario['senha_hash']);

// Retorna sucesso com os dados do usuário (sem a senha)
// O frontend salvará esses dados no sessionStorage do navegador
// HTTP 200 é o código padrão de sucesso
responder(['sucesso' => true, 'usuario' => $usuario]);
?>