<?php // php/jogador_novo.php  ->  CREATE (INSERT)

include_once('conexao.php');

// Dados vindos do FormData (chegam em $_POST)
$id_partida = (int) ($_POST['id_partida'] ?? 0);
$id_atleta  = (int) ($_POST['id_atleta']  ?? 0);
$situacao   = trim($_POST['situacao'] ?? 'PENDENTE_PAGAMENTO');

if ($id_partida < 1) {
    responder('nok', 'Evento nao informado.');
}

if ($id_atleta < 1) {
    responder('nok', 'Selecione o atleta.');
}

$situacoes = ['PENDENTE_PAGAMENTO', 'CONFIRMADA', 'CANCELADA', 'PRESENTE', 'FALTOU'];

if (!in_array($situacao, $situacoes)) {
    responder('nok', 'Situacao invalida.');
}

$stmt = $conexao->prepare(
    'INSERT INTO participacao (id_partida, id_atleta, situacao)
     VALUES (?, ?, ?)'
);

// iis = 2 integers + 1 string
$stmt->bind_param('iis', $id_partida, $id_atleta, $situacao);

/* O banco tem um TRIGGER (trg_participacao_bi) que recusa a
   inscricao quando a partida nao e ABERTA, nao esta AGENDADA
   ou ja esta lotada. Ele avisa com SQLSTATE 45000 e uma
   mensagem pronta para o usuario, que repassamos aqui.      */
try {
    $stmt->execute();
    $gravou = $stmt->affected_rows > 0;
    $stmt->close();
    $conexao->close();

    if ($gravou) {
        responder('ok', 'Jogador inscrito com sucesso.');
    }

    responder('nok', 'Falha ao inscrever o jogador.');

} catch (mysqli_sql_exception $e) {
    $stmt->close();
    $conexao->close();

    if ($e->getSqlState() == '45000') {
        // Mensagem escrita no proprio trigger do banco
        responder('nok', $e->getMessage());
    }

    if ($e->getCode() == 1062) {
        responder('nok', 'Este atleta ja esta inscrito neste evento.');
    }

    responder('nok', 'Nao foi possivel inscrever o jogador.');
}
