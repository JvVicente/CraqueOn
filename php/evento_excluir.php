<?php // php/evento_excluir.php  ->  DELETE

include_once('conexao.php');

if (!isset($_GET['id'])) {
    responder('nok', 'E necessario informar o id do evento.');
}

$id = (int) $_GET['id'];

// As inscricoes (participacao), a fila de espera, as mensagens e as
// notificacoes da partida sao apagadas junto pelo ON DELETE CASCADE
// definido no banco.
$stmt = $conexao->prepare(
    'DELETE FROM partida WHERE id_partida = ? AND tipo = "ABERTA"'
);
$stmt->bind_param('i', $id);

try {
    $stmt->execute();
    $excluiu = $stmt->affected_rows > 0;
    $stmt->close();
    $conexao->close();

    if ($excluiu) {
        responder('ok', 'Evento excluido com sucesso.');
    }

    responder('nok', 'Evento nao encontrado para exclusao.');

} catch (mysqli_sql_exception $e) {
    $stmt->close();
    $conexao->close();
    responder('nok', 'Nao foi possivel excluir este evento.');
}
