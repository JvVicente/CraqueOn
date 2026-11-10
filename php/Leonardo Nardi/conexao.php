<?php // php/conexao.php

/* ==========================================================
    CONEXAO COM O BANCO DE DADOS - CraqueOn
    Este e o UNICO arquivo do projeto que guarda os dados de
    acesso ao MySQL. Todos os outros arquivos PHP o incluem
    com include_once('conexao.php').
    O front-end (HTML/JS) nunca ve estas informacoes, porque
    o PHP e executado no servidor.
   ========================================================== */

// Faz o MySQLi avisar os erros devolvendo false, em vez de
// interromper o script. E assim que o if ($conexao->connect_error)
// logo abaixo consegue funcionar.
mysqli_report(MYSQLI_REPORT_OFF);

// Variaveis de conexao (padrao do XAMPP)
$servidor   = 'localhost:3306';  // host:porta
$usuario    = 'root';            // usuario do MySQL
$senha      = '';                // senha (vazia no XAMPP padrao)
$nome_banco = 'craque_on';       // banco criado pelo script sql/craque_on.sql

// Cria a conexao
$conexao = new mysqli($servidor, $usuario, $senha, $nome_banco);

// Verificar se a conexao falhou
if ($conexao->connect_error) {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode([
        'status'   => 'nok',
        'mensagem' => 'Falha na conexao com o banco de dados.',
        'data'     => []
    ]);
    exit;  // para a execucao aqui
}

// Definir charset da conexao (acentos)
$conexao->set_charset('utf8mb4');
