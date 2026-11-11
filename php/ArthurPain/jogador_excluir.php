<?php // php/jogador_excluir.php  ->  DELETE

include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Os pagamentos ligados a inscricao saem junto, pelo ON DELETE CASCADE
    $stmt = $conexao->prepare('DELETE FROM participacao WHERE id_participacao = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno['status']   = 'ok';
        $retorno['mensagem'] = 'Inscricao excluida com sucesso.';
    } else {
        $retorno['status']   = 'nok';
        $retorno['mensagem'] = 'Inscricao nao encontrada para exclusao.';
    }

    $stmt->close();
} else {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'E necessario informar o id da inscricao.';
}

$conexao->close();
header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
