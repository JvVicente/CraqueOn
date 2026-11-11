<?php // php/JoaoVicente/chat_contatos.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];
$idLogado = 1;

$stmt = $conexao->prepare(
    "SELECT u.id_usuario AS id,
            u.nome AS name,
            CASE
                WHEN l.ultimo_acesso >= NOW() - INTERVAL 5 MINUTE THEN 'Online'
                ELSE 'Online recentemente'
            END AS status
     FROM usuario u
     LEFT JOIN login l ON l.id_usuario = u.id_usuario
     WHERE u.id_usuario != ?
     ORDER BY u.nome"
);

$stmt->bind_param('i', $idLogado);
$stmt->execute();
$resultado = $stmt->get_result();

$contatos = [];
while ($linha = $resultado->fetch_assoc()) {
    $contatos[] = [
        'id' => (int) $linha['id'],
        'name' => $linha['name'],
        'status' => $linha['status'] ?? 'Online recentemente'
    ];
}

$retorno['status'] = 'ok';
$retorno['mensagem'] = count($contatos) > 0 ? 'Contatos listados.' : 'Nenhum contato encontrado.';
$retorno['data'] = $contatos;

$stmt->close();
$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
