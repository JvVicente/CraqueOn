<?php // php/conexao.php

/* ==========================================================
    CONEXAO COM O BANCO DE DADOS - CraqueOn
    Este e o UNICO arquivo do projeto que guarda os dados de
    acesso ao MySQL. Os demais arquivos PHP o incluem com
    include_once('conexao.php').
    O front-end (HTML/JS) nunca ve estas informacoes, porque
    o PHP e executado no servidor.
   ========================================================== */

// Variaveis de conexao (padrao do XAMPP)
$servidor   = 'localhost:3306';  // host:porta
$usuario    = 'root';            // usuario do MySQL
$senha      = '';                // senha (vazia no XAMPP padrao)
$nome_banco = 'craque_on';       // banco criado pelo script sql/craque_on.sql

// Cria a conexao
$conexao = new mysqli($servidor, $usuario, $senha, $nome_banco);

// Se a conexao falhar, devolve o JSON padrao e para a execucao
if ($conexao->connect_error) {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode([
        'status'   => 'nok',
        'mensagem' => 'Falha na conexao com o banco de dados.',
        'data'     => []
    ]);
    exit;
}

// Define o charset da conexao (acentos)
$conexao->set_charset('utf8mb4');


/* ----------------------------------------------------------
    Funcao usada por todos os arquivos para devolver a
    resposta no padrao do projeto e encerrar o script.
   ---------------------------------------------------------- */
function responder($status, $mensagem, $data = []) {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode([
        'status'   => $status,
        'mensagem' => $mensagem,
        'data'     => $data
    ]);
    exit;
}
