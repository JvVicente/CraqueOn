<?php // php/JoaoVicente/perfil_get.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

// Dois comportamentos num único arquivo:
if (isset($_GET['id'])) {
    // Buscar por ID — usado ao clicar em "Carregar"
    $stmt = $conexao->prepare(
        'SELECT id_usuario, nome, email, telefone, cidade, estado
         FROM usuario WHERE id_usuario = ?'
    );
    $stmt->bind_param('i', $_GET['id']); // 'i' = integer
} else {
    // Buscar todos — usado na listagem
    $stmt = $conexao->prepare(
        'SELECT id_usuario, nome, email, telefone, cidade, estado
         FROM usuario ORDER BY nome'
    );
}

$stmt->execute();
$resultado = $stmt->get_result();

$tabela = [];

if ($resultado->num_rows > 0) {
    while ($linha = $resultado->fetch_assoc()) {
        $tabela[] = $linha; // adiciona cada linha ao array
    }
    $retorno['status'] = 'ok';
    $retorno['mensagem'] = 'Consulta efetuada.';
    $retorno['data'] = $tabela;
} else {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'Nao ha registros';
}

$stmt->close();
$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
