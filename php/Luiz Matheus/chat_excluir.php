<?php // php/JoaoVicente/chat_excluir.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];
$idLogado = 1;

if (!isset($_POST['id'])) {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'ID e obrigatorio.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

$id = (int) $_POST['id'];

$stmt = $conexao->prepare(
    'DELETE FROM mensagem WHERE id_mensagem = ? AND id_remetente = ?'
);
$stmt->bind_param('ii', $id, $idLogado);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $retorno['status'] = 'ok';
    $retorno['mensagem'] = 'Mensagem excluida.';
} else {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'Nao foi possivel excluir a mensagem.';
}

$stmt->close();
$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
