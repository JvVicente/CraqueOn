<?php // php/evento_novo.php  ->  CREATE (INSERT)

include_once('conexao.php');

// Dados vindos do FormData enviado pelo fetch (chegam em $_POST)
$titulo         = trim($_POST['titulo'] ?? '');
$id_modalidade  = (int)   ($_POST['id_modalidade']   ?? 0);
$id_organizador = (int)   ($_POST['id_organizador']  ?? 0);
$id_local       = (int)   ($_POST['id_local']        ?? 0);
$data_evento    = trim($_POST['data_evento'] ?? '');
$horario        = trim($_POST['horario'] ?? '');
$vagas_total    = (int)   ($_POST['vagas_total']     ?? 0);
$valor          = (float) ($_POST['valor_inscricao'] ?? 0);
$status         = trim($_POST['status'] ?? 'AGENDADA');

// Validacao no back-end (nunca confiar apenas no required do HTML)
if ($titulo == '' || $data_evento == '' || $horario == '') {
    responder('nok', 'Preencha o titulo, a data e o horario do evento.');
}

if ($id_modalidade < 1 || $id_organizador < 1 || $id_local < 1) {
    responder('nok', 'Selecione a modalidade, o local e o organizador.');
}

// A tabela partida exige vagas_total maior que zero quando o tipo e ABERTA
if ($vagas_total < 1) {
    responder('nok', 'A quantidade de vagas deve ser maior que zero.');
}

if ($valor < 0) {
    responder('nok', 'O valor da inscricao nao pode ser negativo.');
}

$situacoes = ['AGENDADA', 'EM_ANDAMENTO', 'FINALIZADA', 'CANCELADA'];

if (!in_array($status, $situacoes)) {
    responder('nok', 'Status invalido.');
}

// O banco guarda data e hora juntas na coluna data_hora (DATETIME)
$data_hora = $data_evento . ' ' . substr($horario, 0, 5) . ':00';

// INSERT com placeholders ? (prepared statement -> evita SQL Injection)
// tipo = ABERTA: evento com vagas, sem equipe mandante/visitante
$stmt = $conexao->prepare(
    'INSERT INTO partida
        (tipo, id_modalidade, id_organizador, id_local,
        titulo, data_hora, vagas_total, valor_inscricao, status)
    VALUES ("ABERTA", ?, ?, ?, ?, ?, ?, ?, ?)'
);

// iiissids = 3 integers + 2 strings + 1 integer + 1 double + 1 string
$stmt->bind_param(
    'iiissids',
    $id_modalidade, $id_organizador, $id_local,
    $titulo, $data_hora, $vagas_total, $valor, $status
);

// O try/catch transforma os erros do banco (chave estrangeira
// inexistente, CHECK da tabela partida) na resposta JSON padrao,
// em vez de derrubar o script com um erro em HTML.
try {
    $stmt->execute();
    $gravou = $stmt->affected_rows > 0;
    $stmt->close();
    $conexao->close();

    if ($gravou) {
        responder('ok', 'Evento cadastrado com sucesso.');
    }

    responder('nok', 'Falha ao cadastrar o evento.');

} catch (mysqli_sql_exception $e) {
    $stmt->close();
    $conexao->close();
    responder('nok', 'Nao foi possivel cadastrar: verifique modalidade, local e organizador.');
}
