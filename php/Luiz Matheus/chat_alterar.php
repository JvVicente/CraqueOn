<?php // php/JoaoVicente/chat_alterar.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];
$idLogado = 1;

if (!isset($_POST['id']) || !isset($_POST['texto'])) {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'ID e texto sao obrigatorios.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

$id = (int) $_POST['id'];
$texto = trim($_POST['texto']);

if ($texto === '') {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'Texto nao pode ficar vazio.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

$stmt = $conexao->prepare(
    'UPDATE mensagem
     SET texto = ?, editada_em = NOW()
     WHERE id_mensagem = ?
       AND id_remetente = ?
       AND TIMESTAMPDIFF(MINUTE, enviada_em, NOW()) <= 15'
);
$stmt->bind_param('sii', $texto, $id, $idLogado);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $retorno['status'] = 'ok';
    $retorno['mensagem'] = 'Mensagem editada.';
} else {
    $retorno['status'] = 'nok';
    if ($stmt->errno == 3819 || $stmt->errno == 4025) {
        $retorno['mensagem'] = 'Texto invalido.';
    } else {
        $retorno['mensagem'] = 'Nao foi possivel editar. Mensagem inexistente, nao e sua ou passou de 15 minutos.';
    }
}

$stmt->close();
$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
