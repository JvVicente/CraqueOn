<?php // php/JoaoVicente/conexao.php
// Desliga as excecoes automaticas do MySQLi (PHP 8.1+) para o PHP sempre devolver JSON
mysqli_report(MYSQLI_REPORT_OFF);

// Variáveis de conexão
$servidor = 'localhost:3306'; // host:porta
$usuario = 'root';            // usuário do MySQL
$senha = '';                  // senha (vazia no XAMPP padrão)
$nome_banco = 'craque_on';    // banco do projeto (craque_on.sql)

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
