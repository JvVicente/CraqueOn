<?php // php/JoaoVicente/perfil_alterar.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

// ID vem na URL: perfil_alterar.php?id=42
if (isset($_GET['id'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = strtoupper(trim($_POST['estado'] ?? ''));

    // Validação: não executa o UPDATE se faltar algum campo
    if (empty($nome) || empty($email) || empty($telefone) || empty($cidade) || empty($estado)) {
        $retorno['status'] = 'nok';
        $retorno['mensagem'] = 'Preencha todos os campos.';
        $conexao->close();
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($retorno);
        exit;
    }

    $stmt = $conexao->prepare(
        'UPDATE usuario
         SET nome = ?, email = ?, telefone = ?, cidade = ?, estado = ?
         WHERE id_usuario = ?'
    );

    // 'sssssi' = 5 strings + 1 integer (id)
    $stmt->bind_param('sssssi', $nome, $email, $telefone, $cidade, $estado, $_GET['id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno['status'] = 'ok';
        $retorno['mensagem'] = 'Registro alterado com sucesso.';
    } else {
        $retorno['status'] = 'nok';
        if ($stmt->errno == 1062) {
            $retorno['mensagem'] = 'Ja existe um usuario com esse email.';
        } elseif ($stmt->errno == 3819 || $stmt->errno == 4025) {
            $retorno['mensagem'] = 'Dados invalidos: confira o email e o estado (2 letras, ex.: PR).';
        } else {
            $retorno['mensagem'] = 'Nenhuma alteracao realizada (dados iguais ou registro inexistente).';
        }
    }

    $stmt->close();
} else {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'ID nao informado.';
}

$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
