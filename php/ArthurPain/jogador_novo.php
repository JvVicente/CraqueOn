<?php // php/jogador_novo.php  ->  CREATE (INSERT)

include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

// Receber os dados do FormData (vem via $_POST)
$id_partida = (int) ($_POST['id_partida'] ?? 0);
$id_atleta  = (int) ($_POST['id_atleta']  ?? 0);
$situacao   = trim($_POST['situacao'] ?? '');

if ($id_partida < 1) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Evento nao informado.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

if ($id_atleta < 1) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Selecione o atleta.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

if ($situacao != 'PENDENTE_PAGAMENTO' && $situacao != 'CONFIRMADA'
    && $situacao != 'CANCELADA' && $situacao != 'PRESENTE'
    && $situacao != 'FALTOU') {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Situacao invalida.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

/* O banco tem um TRIGGER que recusa a inscricao quando a partida
   nao e ABERTA, nao esta AGENDADA ou ja esta lotada. As tres
   conferencias abaixo fazem a mesma checagem antes, para o usuario
   receber uma mensagem clara em vez de so "falha ao inscrever". */

// 1. A partida existe? De que tipo e com que status?
$stmt = $conexao->prepare(
    'SELECT tipo, status, vagas_total FROM partida WHERE id_partida = ?'
);
$stmt->bind_param('i', $id_partida);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Evento nao encontrado.';
    $stmt->close();
    $conexao->close();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

$partida = $resultado->fetch_assoc();
$stmt->close();

if ($partida['tipo'] != 'ABERTA') {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Inscricao individual so e permitida em eventos abertos.';
    $conexao->close();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

if ($partida['status'] != 'AGENDADA') {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Somente eventos agendados aceitam novas inscricoes.';
    $conexao->close();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

// 2. Quantas vagas ja estao ocupadas?
$stmt = $conexao->prepare(
    'SELECT COUNT(*) AS ocupadas
       FROM participacao
      WHERE id_partida = ?
        AND situacao IN ("PENDENTE_PAGAMENTO","CONFIRMADA","PRESENTE")'
);
$stmt->bind_param('i', $id_partida);
$stmt->execute();
$resultado = $stmt->get_result();
$linha     = $resultado->fetch_assoc();
$ocupadas  = (int) $linha['ocupadas'];
$stmt->close();

if ($ocupadas >= (int) $partida['vagas_total']) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Evento lotado: todas as ' . $partida['vagas_total'] . ' vagas estao ocupadas.';
    $conexao->close();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

// 3. Esse atleta ja esta inscrito? (a tabela tem UNIQUE do par)
$stmt = $conexao->prepare(
    'SELECT id_participacao FROM participacao WHERE id_partida = ? AND id_atleta = ?'
);
$stmt->bind_param('ii', $id_partida, $id_atleta);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Este atleta ja esta inscrito neste evento.';
    $stmt->close();
    $conexao->close();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}
$stmt->close();

// Passou nas conferencias: pode inserir
$stmt = $conexao->prepare(
    'INSERT INTO participacao (id_partida, id_atleta, situacao) VALUES (?, ?, ?)'
);

// Bind: 'iis' = 2 integers + 1 string
$stmt->bind_param('iis', $id_partida, $id_atleta, $situacao);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $retorno['status']   = 'ok';
    $retorno['mensagem'] = 'Jogador inscrito com sucesso.';
} else {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Falha ao inscrever o jogador.';
}

$stmt->close();
$conexao->close();
header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
