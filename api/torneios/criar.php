<?php
// Importa o arquivo config.php que está na pasta pai (../)
// Traz as funções: conectar(), responder() e corpo()
// require_once garante que o arquivo só é carregado uma vez
require_once '../config.php';

// Verifica se o método da requisição é POST
// Criação de dados sempre deve usar POST
// 405 = Method Not Allowed (método não permitido)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido.'], 405);
}

// corpo() lê o JSON enviado pelo frontend no corpo da requisição
// Exemplo do que chega:
// {"nome":"Copa Valorant","modalidade":"Valorant","formato":"mata-mata",...}
$d = corpo();

// Extrai e limpa cada campo do formulário
// trim() remove espaços acidentais do início e fim
// ?? '' retorna string vazia se o campo não existir no array
$nome       = trim($d['nome']        ?? '');
$modalidade = trim($d['modalidade']  ?? '');
$formato    = trim($d['formato']     ?? '');
$data_ini   = trim($d['data_inicio'] ?? '');

// data_fim não usa trim() porque pode ser null
// torneio sem data de fim definida é permitido no sistema
// null significa "indefinido" no banco de dados
$data_fim   = $d['data_fim']  ?? null;

// intval() converte o valor para número inteiro
// Se vier "16" (string), vira 16 (número inteiro)
// Se vier null ou vazio, vira 0
// ?? 0 retorna 0 se o campo não existir no array
$max_times  = intval($d['max_times'] ?? 0);

// Campos opcionais — podem ficar null no banco
// Não usamos trim() pois podem ser null
$descricao  = $d['descricao'] ?? null;
$premiacao  = $d['premiacao'] ?? null;

// intval() converte para inteiro
// ?: é o operador Elvis — se o valor for falsy (0, '', false, null)
// usa o valor à direita (null)
// Isso porque id_organizador = 0 não faz sentido no banco
// Um organizador precisa ter um ID válido ou ser null
$id_org = intval($d['id_organizador'] ?? 0) ?: null;

// Valida os campos obrigatórios antes de salvar no banco
// max_times < 2 valida a regra de negócio:
// um torneio precisa de no mínimo 2 times para funcionar
// 400 = Bad Request (requisição inválida do cliente)
if (!$nome || !$modalidade || !$formato || !$data_ini || $max_times < 2) {
    responder(['erro' => 'Preencha os campos obrigatórios.'], 400);
}

// Abre a conexão com o banco de dados MySQL
$conn = conectar();

// Prepara o INSERT com 9 colunas e 9 placeholders (?)
// A quebra de linha é só para facilitar a leitura do código
// Os ? serão substituídos pelos valores reais pelo bind_param
$stmt = $conn->prepare(
    'INSERT INTO torneio
     (nome, modalidade, formato, data_inicio, data_fim, max_times, descricao, premiacao, id_organizador)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

// Vincula os 9 valores aos 9 placeholders
// A string 'sssssissi' define o tipo de cada valor na ordem:
// s = nome         (string)
// s = modalidade   (string)
// s = formato      (string)
// s = data_ini     (string — datas ficam no formato YYYY-MM-DD)
// s = data_fim     (string ou null)
// i = max_times    (integer — número inteiro)
// s = descricao    (string ou null)
// s = premiacao    (string ou null)
// i = id_org       (integer ou null)
// ATENÇÃO: a ordem dos tipos deve ser EXATAMENTE igual
// à ordem das variáveis passadas logo abaixo
$stmt->bind_param('sssssissi',
    $nome, $modalidade, $formato,
    $data_ini, $data_fim, $max_times,
    $descricao, $premiacao, $id_org
);

// Executa o INSERT — salva o novo torneio no banco de dados
$stmt->execute();

// insert_id retorna o ID gerado automaticamente pelo banco
// para o novo registro (coluna AUTO_INCREMENT da tabela torneio)
// 201 = Created (recurso criado com sucesso)
responder(['sucesso' => true, 'id_torneio' => $conn->insert_id], 201);
?>