<?php // php/jogador_opcoes.php  ->  atletas que ainda podem ser inscritos

/* O jogador nao e digitado: ele e um atleta ja cadastrado
   (chave estrangeira id_atleta). Este arquivo devolve os
   atletas ativos que AINDA NAO estao inscritos no evento,
   para montar o <select> da tela.                          */

include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

if (!isset($_GET['id_partida'])) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Informe o id do evento.';
    $conexao->close();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

$id_partida = (int) $_GET['id_partida'];

$stmt = $conexao->prepare(
    'SELECT a.id_atleta, a.nome, a.posicao
       FROM atleta a
      WHERE a.status = "ATIVO"
        AND a.id_atleta NOT IN (
              SELECT p.id_atleta FROM participacao p WHERE p.id_partida = ?
            )
      ORDER BY a.nome'
);
$stmt->bind_param('i', $id_partida);
$stmt->execute();
$resultado = $stmt->get_result();
$tabela = [];

while ($linha = $resultado->fetch_assoc()) {
    $tabela[] = $linha;
}

$retorno['status']   = 'ok';
$retorno['mensagem'] = 'Consulta efetuada.';
$retorno['data']     = $tabela;

$stmt->close();
$conexao->close();
header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
