<?php // php/perfil_alterar.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

// ID vem na URL: perfil_alterar.php?id=42
if (isset($_GET['id'])) {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $idade = (int) ($_POST['idade'] ?? 0);
    $esporte = $_POST['esporte'] ?? '';

    // Validação: não executa o UPDATE se faltar algum campo
    if (empty($nome) || empty($email) || empty($telefone) || empty($idade) || empty($esporte)) {
        $retorno['status'] = 'nok';
        $retorno['mensagem'] = 'Preencha todos os campos.';
        $conexao->close();
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($retorno);
        exit;
    }

    $stmt = $conexao->prepare(
        'UPDATE perfil
         SET nome = ?, email = ?, telefone = ?, idade = ?, esporte = ?
         WHERE id = ?'
    );

    // 'sssisi' = 3 strings + 1 integer (idade) + 1 string + 1 integer (id)
    $stmt->bind_param('sssisi', $nome, $email, $telefone, $idade, $esporte, $_GET['id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno['status'] = 'ok';
        $retorno['mensagem'] = 'Registro alterado com sucesso.';
    } else {
        $retorno['status'] = 'nok';
        $retorno['mensagem'] = 'Nenhuma alteracao realizada (dados iguais ou registro inexistente).';
    }

    $stmt->close();
} else {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'ID nao informado.';
}

$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
