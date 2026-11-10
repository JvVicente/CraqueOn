<?php // php/evento_novo.php  ->  CREATE (INSERT)

include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

// Receber os dados do FormData (vem via $_POST)
$titulo         = trim($_POST['titulo'] ?? '');
$id_modalidade  = (int) ($_POST['id_modalidade']  ?? 0);
$id_organizador = (int) ($_POST['id_organizador'] ?? 0);
$id_local       = (int) ($_POST['id_local']       ?? 0);
$data_evento    = trim($_POST['data_evento'] ?? '');
$horario        = trim($_POST['horario'] ?? '');
$vagas_total    = (int) ($_POST['vagas_total'] ?? 0);
$valor          = (float) ($_POST['valor_inscricao'] ?? 0);
$status         = trim($_POST['status'] ?? '');

// Validar antes de gravar (o required do HTML roda no navegador,
// e o navegador e do usuario: a conferencia que vale e esta)
if (empty($titulo) || empty($data_evento) || empty($horario)) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Preencha o titulo, a data e o horario do evento.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

if ($id_modalidade < 1 || $id_organizador < 1 || $id_local < 1) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Selecione a modalidade, o local e o organizador.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

// A tabela partida exige vagas maior que zero quando o tipo e ABERTA
if ($vagas_total < 1) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'A quantidade de vagas deve ser maior que zero.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

if ($valor < 0) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'O valor da inscricao nao pode ser negativo.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

if ($status != 'AGENDADA' && $status != 'EM_ANDAMENTO'
    && $status != 'FINALIZADA' && $status != 'CANCELADA') {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Status invalido.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

// O banco guarda data e hora juntas, na coluna data_hora (DATETIME)
$data_hora = $data_evento . ' ' . $horario;

// Preparar o INSERT com placeholders ? (evita SQL Injection)
// tipo = ABERTA: evento com vagas, sem equipe mandante/visitante
$stmt = $conexao->prepare(
    'INSERT INTO partida
        (tipo, id_modalidade, id_organizador, id_local,
         titulo, data_hora, vagas_total, valor_inscricao, status)
     VALUES ("ABERTA", ?, ?, ?, ?, ?, ?, ?, ?)'
);

// Bind: 'iiissids' = 3 integers + 2 strings + 1 integer + 1 double + 1 string
$stmt->bind_param(
    'iiissids',
    $id_modalidade, $id_organizador, $id_local,
    $titulo, $data_hora, $vagas_total, $valor, $status
);
$stmt->execute();

// affected_rows > 0 significa que o INSERT funcionou
if ($stmt->affected_rows > 0) {
    $retorno['status']   = 'ok';
    $retorno['mensagem'] = 'Evento cadastrado com sucesso.';
} else {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Falha ao cadastrar o evento.';
}

$stmt->close();
$conexao->close();
header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
