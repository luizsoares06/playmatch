<?php
// Importa o arquivo config.php que está na pasta pai (../)
// Traz as funções: conectar(), responder() e corpo()
// require_once garante que o arquivo só é carregado uma vez
require_once '../config.php';

// Verifica se o método da requisição é POST
// Cadastro envia dados sensíveis (senha), então deve ser POST
// Se fosse GET, os dados apareceriam na URL — inseguro!
// 405 = Method Not Allowed (método não permitido)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido.'], 405);
}

// corpo() lê o JSON enviado pelo frontend no corpo da requisição
// Retorna um array PHP com todos os dados enviados
// Exemplo do que chega:
// {"nome":"João","email":"joao@email.com","senha":"123","perfil":"organizador"}
$d = corpo();

// Extrai cada campo do array $d
// ?? '' retorna string vazia se o campo não existir no array
// trim() remove espaços acidentais do início e fim
$nome   = trim($d['nome']   ?? '');
$email  = trim($d['email']  ?? '');
$senha  = trim($d['senha']  ?? '');
$perfil = trim($d['perfil'] ?? '');

// Valida se TODOS os campos obrigatórios foram preenchidos
// O || significa OR — se QUALQUER campo estiver vazio, entra no if
// !$nome = true se $nome for vazio, false se tiver conteúdo
// 400 = Bad Request (requisição inválida do cliente)
if (!$nome || !$email || !$senha || !$perfil) {
    responder(['erro' => 'Preencha todos os campos.'], 400);
}

// Abre a conexão com o banco de dados MySQL
// Usa as configurações definidas no config.php (host, user, pass, db)
$conn = conectar();

// Verifica se o e-mail já está cadastrado no banco
// SELECT id_usuario (só o ID) é mais eficiente que SELECT *
// porque não precisamos de todos os dados, só saber se existe
// O ? é o placeholder que será substituído pelo $email
$stmt = $conn->prepare('SELECT id_usuario FROM usuario WHERE email = ?');

// Vincula o $email ao placeholder ?
// 's' = string (tipo do dado)
$stmt->bind_param('s', $email);

// Executa a consulta no banco
$stmt->execute();

// num_rows conta quantas linhas o SELECT retornou
// Se for maior que 0, significa que o e-mail já existe no banco
if ($stmt->get_result()->num_rows > 0) {
    // 409 = Conflict (conflito — o recurso já existe)
    responder(['erro' => 'E-mail já cadastrado.'], 409);
}

// password_hash() criptografa a senha usando o algoritmo bcrypt
// PASSWORD_BCRYPT é uma constante do PHP para esse algoritmo
// O resultado é uma string como: $2y$10$abc123...xyz (60 caracteres)
// IMPORTANTE: cada execução gera um hash DIFERENTE para a mesma senha
// por causa do "salt" aleatório interno — impossibilita ataques
// de dicionário. NUNCA armazenamos a senha original no banco!
$hash = password_hash($senha, PASSWORD_BCRYPT);

// Prepara o INSERT para criar o novo usuário no banco
// Os 4 pontos de interrogação são os 4 valores que serão inseridos
// A ordem deve corresponder exatamente às colunas declaradas
$stmt = $conn->prepare(
    'INSERT INTO usuario (nome, email, senha_hash, perfil) VALUES (?, ?, ?, ?)'
);

// Vincula os 4 valores aos 4 placeholders
// 'ssss' = quatro strings (nome, email, hash, perfil)
// A ordem dos tipos deve corresponder à ordem das variáveis
// Salvamos $hash (criptografado), nunca $senha (original)
$stmt->bind_param('ssss', $nome, $email, $hash, $perfil);

// Executa o INSERT — insere o novo usuário no banco de dados
$stmt->execute();

// insert_id retorna o ID gerado automaticamente pelo banco
// para o novo registro (coluna AUTO_INCREMENT)
// 201 = Created (recurso criado com sucesso)
// É o código HTTP correto para criação, diferente do 200 (OK)
responder(['sucesso' => true, 'id' => $conn->insert_id], 201);
?>