<?php // php/conexao.php
// Variáveis de conexão
$servidor = 'localhost:3306'; // host:porta
$usuario = 'root';            // usuário do MySQL
$senha = '';                  // senha (vazia no XAMPP padrão)
$nome_banco = 'craqueon';     // nome do banco criado pelo script SQL

// Criar a conexão
$conexao = new mysqli($servidor, $usuario, $senha, $nome_banco);

// Verificar se a conexão falhou
if ($conexao->connect_error) {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'nok',
        'mensagem' => 'Falha na conexao: ' . $conexao->connect_error,
        'data' => []
    ]);
    exit; // para a execução aqui
}

// Definir charset da conexão
$conexao->set_charset('utf8mb4');
