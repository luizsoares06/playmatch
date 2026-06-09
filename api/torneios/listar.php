<?php
// Importa o arquivo config.php que está na pasta pai (../)
// Traz as funções: conectar(), responder() e corpo()
// Não verificamos o método aqui pois listagem usa GET
// GET é o método padrão para buscar/listar dados
require_once '../config.php';

// Abre a conexão com o banco de dados MySQL
$conn = conectar();

// $_GET contém os parâmetros passados na URL após o ?
// Exemplo de URL com todos os filtros:
// listar.php?texto=valorant&data_ini=2026-01-01&status=aberto
// trim() limpa espaços acidentais
// ?? '' retorna string vazia se o parâmetro não vier na URL
$texto      = trim($_GET['texto']      ?? '');
$data_ini   = trim($_GET['data_ini']   ?? '');
$data_fim   = trim($_GET['data_fim']   ?? '');
$modalidade = trim($_GET['modalidade'] ?? '');
$status     = trim($_GET['status']     ?? '');

// Início da query SQL que será montada dinamicamente
// WHERE 1=1 é um truque muito usado: 1=1 é sempre verdadeiro
// então não filtra nada por padrão
// Permite adicionar condições com AND sem verificar
// se já existe um WHERE antes — facilita o código
$sql = 'SELECT * FROM torneio WHERE 1=1';

// $tipos acumula a string de tipos para o bind_param
// Começa vazio e cresce conforme filtros são adicionados
// Exemplo final: 'sss' se houver 3 filtros ativos
$tipos = '';

// $params acumula os valores dos filtros ativos
// Começa vazio e recebe os valores conforme filtros são adicionados
// Exemplo final: ['%valorant%', '2026-01-01', 'aberto']
$params = [];

// FILTRO 1 — Busca por texto no nome do torneio
// Só adiciona o filtro se $texto não for vazio
if ($texto) {
    // LIKE faz busca parcial no banco de dados
    // % é um coringa que representa qualquer texto
    // %valorant% encontra qualquer nome que CONTENHA "valorant"
    // Exemplos: "Copa Valorant 2026", "1º Valorant Summer", etc.
    $sql .= ' AND nome LIKE ?';

    // .= concatena — adiciona 's' ao final de $tipos
    // Se $tipos era '' agora vira 's'
    $tipos .= 's';

    // Adiciona o valor com % ao array de parâmetros
    // As aspas duplas interpretam a variável $texto dentro da string
    $params[] = "%$texto%";
}

// FILTRO 2a — Data de início mínima
// Só adiciona se $data_ini não for vazio
if ($data_ini) {
    // >= filtra torneios que começam NA ou APÓS a data informada
    $sql .= ' AND data_inicio >= ?';
    $tipos .= 's';
    $params[] = $data_ini;
}

// FILTRO 2b — Data de início máxima
// Só adiciona se $data_fim não for vazio
if ($data_fim) {
    // <= filtra torneios que começam NA ou ANTES da data informada
    // Combinado com data_ini, cria um intervalo de datas
    $sql .= ' AND data_inicio <= ?';
    $tipos .= 's';
    $params[] = $data_fim;
}

// FILTRO 3a — Modalidade exata do torneio
// Só adiciona se $modalidade não for vazio
if ($modalidade) {
    // = faz comparação exata (diferente do LIKE que é parcial)
    // Só retorna torneios com aquela modalidade específica
    $sql .= ' AND modalidade = ?';
    $tipos .= 's';
    $params[] = $modalidade;
}

// FILTRO 3b — Status do torneio
// Só adiciona se $status não for vazio
if ($status) {
    // = comparação exata com o status
    // Valores possíveis: 'aberto', 'em-andamento', 'encerrado'
    $sql .= ' AND status = ?';
    $tipos .= 's';
    $params[] = $status;
}

// Ordena os resultados pelo mais recente primeiro
// ORDER BY criado_em = ordena pela data de criação
// DESC = decrescente (mais novo primeiro)
// ASC seria crescente (mais antigo primeiro)
$sql .= ' ORDER BY criado_em DESC';

// Prepara a query final que foi montada dinamicamente
// Pode ter de 0 a 5 condições AND dependendo dos filtros
$stmt = $conn->prepare($sql);

// Só executa o bind_param se houver algum filtro ativo
// Se $tipos for '' (vazio) não há parâmetros para vincular
if ($tipos) {
    // O ... é o operador spread (espalhamento)
    // Expande o array $params em argumentos individuais
    // Exemplo: se $params = ['%val%', '2026-01-01', 'aberto']
    // vira: bind_param('sss', '%val%', '2026-01-01', 'aberto')
    // Sem o spread teríamos que saber quantos filtros tem — inviável
    $stmt->bind_param($tipos, ...$params);
}

// Executa a consulta no banco com os filtros aplicados
$stmt->execute();

// Obtém o objeto com todos os resultados da consulta
$resultado = $stmt->get_result();

// Cria array vazio para acumular os torneios encontrados
$torneios = [];

// Loop: enquanto houver linhas para ler, continua executando
// fetch_assoc() retorna cada linha como array associativo
// Quando não há mais linhas, retorna false e o while para
while ($row = $resultado->fetch_assoc()) {
    // Adiciona cada torneio encontrado ao array $torneios
    // $torneios[] = $row é o mesmo que array_push($torneios, $row)
    $torneios[] = $row;
}

// Retorna o array completo de torneios como JSON
// Se nenhum torneio for encontrado, retorna array vazio []
// O frontend receberá algo como:
// [{"id_torneio":1,"nome":"Copa Valorant",...}, {...}, {...}]
responder($torneios);
?>