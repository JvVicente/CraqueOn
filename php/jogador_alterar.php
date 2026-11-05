<?php // php/jogador_alterar.php  ->  UPDATE

/* O id da inscricao vem pela URL ($_GET['id'])
   Os dados novos vem pelo body   ($_POST, via FormData) */

include_once('conexao.php');

if (!isset($_GET['id'])) {
    responder('nok', 'Id da inscricao nao informado.');
}

$id        = (int) $_GET['id'];
$id_atleta = (int) ($_POST['id_atleta'] ?? 0);
$situacao  = trim($_POST['situacao'] ?? '');

if ($id_atleta < 1) {
    responder('nok', 'Selecione o atleta.');
}

$situacoes = ['PENDENTE_PAGAMENTO', 'CONFIRMADA', 'CANCELADA', 'PRESENTE', 'FALTOU'];

if (!in_array($situacao, $situacoes)) {
    responder('nok', 'Situacao invalida.');
}

$stmt = $conexao->prepare(
    'UPDATE participacao
        SET id_atleta = ?,
            situacao  = ?
      WHERE id_participacao = ?'
);

// isi = integer + string + o id
$stmt->bind_param('isi', $id_atleta, $situacao, $id);

// Nao usamos affected_rows: salvar sem mudar nada devolve 0 linhas
// afetadas mesmo tendo executado o UPDATE corretamente.
try {
    $stmt->execute();
    $stmt->close();
    $conexao->close();
    responder('ok', 'Inscricao alterada com sucesso.');

} catch (mysqli_sql_exception $e) {
    $stmt->close();
    $conexao->close();

    if ($e->getCode() == 1062) {
        responder('nok', 'Este atleta ja esta inscrito neste evento.');
    }

    responder('nok', 'Nao foi possivel alterar a inscricao.');
}
