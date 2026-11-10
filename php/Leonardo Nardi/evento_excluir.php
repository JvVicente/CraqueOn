<?php // php/evento_excluir.php  ->  DELETE

include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    /* As inscricoes (participacao), a fila de espera, as mensagens e
       as notificacoes da partida sao apagadas junto, pelo
       ON DELETE CASCADE definido no banco. */
    $stmt = $conexao->prepare(
        'DELETE FROM partida WHERE id_partida = ? AND tipo = "ABERTA"'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno['status']   = 'ok';
        $retorno['mensagem'] = 'Evento excluido com sucesso.';
    } else {
        $retorno['status']   = 'nok';
        $retorno['mensagem'] = 'Evento nao encontrado para exclusao.';
    }

    $stmt->close();
} else {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'E necessario informar o id do evento.';
}

$conexao->close();
header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
