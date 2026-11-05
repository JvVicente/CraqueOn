<?php // php/jogador_excluir.php  ->  DELETE

include_once('conexao.php');

if (!isset($_GET['id'])) {
    responder('nok', 'E necessario informar o id da inscricao.');
}

$id = (int) $_GET['id'];

// Os pagamentos ligados a inscricao saem junto pelo ON DELETE CASCADE.
$stmt = $conexao->prepare('DELETE FROM participacao WHERE id_participacao = ?');
$stmt->bind_param('i', $id);

try {
    $stmt->execute();
    $excluiu = $stmt->affected_rows > 0;
    $stmt->close();
    $conexao->close();

    if ($excluiu) {
        responder('ok', 'Inscricao excluida com sucesso.');
    }

    responder('nok', 'Inscricao nao encontrada para exclusao.');

} catch (mysqli_sql_exception $e) {
    $stmt->close();
    $conexao->close();
    responder('nok', 'Nao foi possivel excluir a inscricao.');
}
