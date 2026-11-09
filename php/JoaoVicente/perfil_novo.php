<?php // php/perfil_novo.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

// Receber os dados do FormData (vêm via $_POST)
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$idade = (int) ($_POST['idade'] ?? 0); // (int) garante que é número
$esporte = $_POST['esporte'] ?? '';

// Validação: não executa o INSERT se faltar algum campo
if (empty($nome) || empty($email) || empty($telefone) || empty($idade) || empty($esporte)) {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'Preencha todos os campos.';
    $conexao->close();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

// Preparar o INSERT com placeholders ? (evita SQL Injection)
$stmt = $conexao->prepare(
    'INSERT INTO perfil(nome, email, telefone, idade, esporte)
     VALUES (?, ?, ?, ?, ?)'
);

// Bind: 'sssis' = 3 strings + 1 integer + 1 string (na ordem dos ?)
$stmt->bind_param('sssis', $nome, $email, $telefone, $idade, $esporte);
$stmt->execute();

// affected_rows > 0 significa que o INSERT funcionou
if ($stmt->affected_rows > 0) {
    $retorno['status'] = 'ok';
    $retorno['mensagem'] = 'Registro inserido com sucesso';
} else {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'Falha ao inserir o registro';
}

$stmt->close();
$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
