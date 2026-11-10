<?php // php/JoaoVicente/perfil_novo.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

// Receber os dados do FormData (vêm via $_POST)
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$estado = strtoupper(trim($_POST['estado'] ?? ''));

// Validação: não executa o INSERT se faltar algum campo
if (empty($nome) || empty($email) || empty($telefone) || empty($cidade) || empty($estado)) {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'Preencha todos os campos.';
    $conexao->close();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

// Preparar o INSERT com placeholders ? (evita SQL Injection)
$stmt = $conexao->prepare(
    'INSERT INTO usuario(nome, email, telefone, cidade, estado)
     VALUES (?, ?, ?, ?, ?)'
);

// Bind: 'sssss' = 5 strings (na ordem dos ?)
$stmt->bind_param('sssss', $nome, $email, $telefone, $cidade, $estado);
$stmt->execute();

// affected_rows > 0 significa que o INSERT funcionou
if ($stmt->affected_rows > 0) {
    $retorno['status'] = 'ok';
    $retorno['mensagem'] = 'Registro inserido com sucesso';
} else {
    $retorno['status'] = 'nok';
    if ($stmt->errno == 1062) {
        $retorno['mensagem'] = 'Ja existe um usuario com esse email.';
    } elseif ($stmt->errno == 3819 || $stmt->errno == 4025) {
        $retorno['mensagem'] = 'Dados invalidos: confira o email e o estado (2 letras, ex.: PR).';
    } else {
        $retorno['mensagem'] = 'Falha ao inserir o registro';
    }
}

$stmt->close();
$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
