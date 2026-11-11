<?php // php/JoaoVicente/chat_mensagens.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];
$idLogado = 1;

if (!isset($_GET['contato_id']) || !is_numeric($_GET['contato_id'])) {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'Informe o contato_id.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

$contato = (int) $_GET['contato_id'];

if ($contato === $idLogado) {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'Nao e possivel carregar conversa consigo mesmo.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

$stmt = $conexao->prepare(
    "SELECT m.id_mensagem AS id,
            CASE
                WHEN m.id_remetente = ? THEN 'eu'
                ELSE u.nome
            END AS remetente,
            m.texto,
            DATE_FORMAT(m.enviada_em, '%H:%i') AS horario,
            (m.id_remetente = ? AND TIMESTAMPDIFF(MINUTE, m.enviada_em, NOW()) <= 15) AS pode_editar
     FROM mensagem m
     JOIN usuario u ON u.id_usuario = m.id_remetente
     WHERE m.id_partida IS NULL
       AND (
           (m.id_remetente = ? AND m.id_destinatario = ?)
           OR (m.id_remetente = ? AND m.id_destinatario = ?)
       )
     ORDER BY m.enviada_em"
);

$stmt->bind_param('iiiiii', $idLogado, $idLogado, $idLogado, $contato, $contato, $idLogado);
$stmt->execute();
$resultado = $stmt->get_result();

$mensagens = [];
while ($linha = $resultado->fetch_assoc()) {
    $mensagens[] = [
        'id' => (int) $linha['id'],
        'remetente' => $linha['remetente'],
        'texto' => $linha['texto'],
        'horario' => $linha['horario'],
        'pode_editar' => (bool) $linha['pode_editar']
    ];
}

$retorno['status'] = 'ok';
$retorno['mensagem'] = 'Mensagens carregadas.';
$retorno['data'] = $mensagens;

$stmt->close();
$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
