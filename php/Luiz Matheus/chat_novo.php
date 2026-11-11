<?php // php/JoaoVicente/chat_novo.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];
$idLogado = 1;

$contato = isset($_POST['contato_id']) ? (int) $_POST['contato_id'] : 0;
$texto = isset($_POST['texto']) ? trim($_POST['texto']) : '';

if ($contato <= 0 || $texto === '') {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'Contato e texto sao obrigatorios.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

if ($contato === $idLogado) {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'Nao e possivel enviar mensagem para si mesmo.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

$stmt = $conexao->prepare(
    'INSERT INTO mensagem (id_remetente, id_destinatario, texto) VALUES (?, ?, ?)'
);
$stmt->bind_param('iis', $idLogado, $contato, $texto);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $retorno['status'] = 'ok';
    $retorno['mensagem'] = 'Mensagem enviada.';
    $retorno['data'] = ['id_mensagem' => $stmt->insert_id];
} else {
    $retorno['status'] = 'nok';
    if ($stmt->errno == 3819 || $stmt->errno == 4025) {
        $retorno['mensagem'] = 'Texto invalido.';
    } else {
        $retorno['mensagem'] = 'Falha ao enviar mensagem.';
    }
}

$stmt->close();
$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
