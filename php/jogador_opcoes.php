<?php // php/jogador_opcoes.php  ->  atletas que ainda podem ser inscritos

/* O jogador nao e digitado: ele e um atleta ja cadastrado
   (chave estrangeira id_atleta). Este arquivo devolve os
   atletas ativos que AINDA NAO estao inscritos no evento,
   para montar o <select> da tela.                          */

include_once('conexao.php');

if (!isset($_GET['id_partida'])) {
    responder('nok', 'Informe o id do evento.');
}

$id_partida = (int) $_GET['id_partida'];

$stmt = $conexao->prepare(
    'SELECT a.id_atleta, a.nome, a.posicao,
            TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) AS idade
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
$tabela    = [];

while ($linha = $resultado->fetch_assoc()) {
    $tabela[] = $linha;
}

$stmt->close();
$conexao->close();
responder('ok', 'Consulta efetuada.', $tabela);
