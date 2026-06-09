<?php
// ============================================================
// Este é o arquivo central de configuração da API
// Todos os outros arquivos PHP importam este com require_once
// Ele define: headers HTTP, configurações do banco e funções
// ============================================================

// header() define cabeçalhos HTTP enviados ao navegador
// ANTES do conteúdo da resposta — deve ser chamado no início

// Define que o conteúdo retornado é JSON com encoding UTF-8
// Sem isso o navegador pode não interpretar a resposta como JSON
// charset=utf-8 garante suporte a acentos e caracteres especiais
header('Content-Type: application/json; charset=utf-8');

// CORS = Cross-Origin Resource Sharing
// Por padrão navegadores bloqueiam requisições entre domínios
// diferentes por segurança (ex: frontend em :5500 chamando API em :80)
// O * (asterisco) permite que QUALQUER origem acesse esta API
// Em produção substituir * pelo domínio específico do frontend
header('Access-Control-Allow-Origin: *');

// Lista quais métodos HTTP são permitidos nesta API
// GET    = buscar/listar dados
// POST   = criar dados
// PUT    = atualizar dados
// DELETE = remover dados
// OPTIONS = verificação prévia do navegador (preflight)
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Permite que o navegador envie o cabeçalho Content-Type
// nas requisições — necessário para enviar JSON no body
header('Access-Control-Allow-Headers: Content-Type');

// Preflight Request — verificação prévia do navegador
// Antes de toda requisição real (POST, PUT, DELETE...)
// o navegador envia uma requisição OPTIONS para "perguntar"
// se o servidor aceita aquela requisição
// Aqui respondemos 200 (OK) dizendo "pode prosseguir"
// exit() encerra o script — não precisa processar mais nada
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ativa a exibição de erros PHP diretamente na tela
// 1 = ativado | 0 = desativado
// IMPORTANTE: usar apenas durante desenvolvimento!
// Em produção (site no ar) SEMPRE desativar por segurança
// Erros expostos podem revelar a estrutura do código
ini_set('display_errors', 1);

// E_ALL = mostrar TODOS os tipos de erros e avisos do PHP
// Essencial durante desenvolvimento para pegar todos os problemas
// Como erros de digitação, variáveis não definidas, etc.
error_reporting(E_ALL);

// define() cria constantes globais — valores que não mudam
// Constantes NÃO usam $ na frente (diferente de variáveis)
// São acessíveis em qualquer lugar do código após serem definidas

// Endereço do servidor de banco de dados
// localhost = banco está no mesmo computador que o PHP (XAMPP)
define('DB_HOST', 'localhost');

// Usuário do banco de dados
// root = usuário administrador padrão do XAMPP
define('DB_USER', 'root');

// Senha do banco de dados
// XAMPP por padrão não tem senha — deixar vazio
// Em produção SEMPRE usar uma senha forte aqui!
define('DB_PASS', '');

// Nome do banco de dados que criamos no phpMyAdmin
define('DB_NAME', 'playmatch');

// ── FUNÇÃO conectar() ───────────────────────────────────────
// Cria e retorna uma conexão com o banco de dados MySQL
// Chamada em todos os arquivos PHP que precisam acessar o banco
function conectar() {

    // new mysqli() cria um novo objeto de conexão MySQL
    // Recebe os 4 parâmetros: host, usuário, senha, banco
    // Usa as constantes DB_HOST, DB_USER etc. definidas acima
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Define o charset da conexão como UTF-8 multibyte
    // utf8mb4 suporta todos os caracteres Unicode incluindo emojis
    // utf8 comum não suporta emojis (só 3 bytes por caractere)
    // Necessário para salvar e ler acentos corretamente
    $conn->set_charset('utf8mb4');

    // connect_error contém a mensagem se a conexão falhou
    // Se for null a conexão foi bem-sucedida
    // Se tiver conteúdo significa que algo deu errado
    if ($conn->connect_error) {

        // 500 = Internal Server Error (erro interno do servidor)
        http_response_code(500);

        // O ponto . é o operador de concatenação do PHP
        // Une a mensagem fixa com o erro específico do MySQL
        // Exemplo: "Falha na conexão: Access denied for user"
        echo json_encode(['erro' => 'Falha na conexão: ' . $conn->connect_error]);

        // Para a execução — não continua o script com conexão falha
        exit();
    }

    // Retorna o objeto de conexão para quem chamou a função
    // Será usado para prepare(), execute(), get_result() etc.
    return $conn;
}

// ── FUNÇÃO responder() ──────────────────────────────────────
// Envia uma resposta JSON padronizada e encerra o script
// $dados  = array PHP que será convertido para JSON
// $codigo = código HTTP (padrão 200 se não for informado)
function responder($dados, $codigo = 200) {

    // Define o código de status HTTP da resposta
    // 200 = OK (sucesso padrão)
    // 201 = Created (recurso criado)
    // 400 = Bad Request (dados inválidos)
    // 401 = Unauthorized (não autorizado)
    // 404 = Not Found (não encontrado)
    // 405 = Method Not Allowed (método não permitido)
    // 409 = Conflict (conflito, ex: e-mail duplicado)
    // 500 = Internal Server Error (erro no servidor)
    http_response_code($codigo);

    // json_encode() converte array PHP em string JSON
    // JSON_UNESCAPED_UNICODE: preserva acentos sem escapar
    //   sem essa flag: "Jo\u00e3o" | com ela: "João"
    // JSON_PRETTY_PRINT: formata com indentação legível
    //   sem ela: {"nome":"João"} | com ela: multilinha identado
    // O | combina as duas opções (operador bitwise OR)
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    // Encerra a execução do script após enviar a resposta
    // Garante que nenhum código adicional seja executado
    exit();
}

// ── FUNÇÃO corpo() ──────────────────────────────────────────
// Lê e retorna o corpo (body) da requisição HTTP como array PHP
// Usada para receber dados JSON enviados pelo frontend via POST
function corpo() {

    // file_get_contents() lê conteúdo de um arquivo ou stream
    // 'php://input' é um stream especial do PHP que contém
    // o body da requisição HTTP — o JSON enviado pelo frontend
    // json_decode() converte o JSON em dado PHP
    // O segundo argumento true = retorna array associativo
    // false retornaria um objeto (stdClass)
    // ?? [] retorna array vazio se o body for null ou inválido
    // Evita erros quando a requisição chega sem body
    return json_decode(file_get_contents('php://input'), true) ?? [];
}
?>