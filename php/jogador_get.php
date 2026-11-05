<?php // php/jogador_get.php  ->  READ (SELECT)

/* "Jogadores do evento" sao as inscricoes da tabela participacao:
   cada linha liga um atleta a uma partida.

   Dois comportamentos em um unico arquivo:
     - ?id_partida=X : todos os inscritos naquele evento (listagem)
     - ?id=X         : uma inscricao (preencher o formulario)      */

include_once('conexao.php');

$colunas = '
    pa.id_participacao,
    pa.id_partida,
    pa.id_atleta,
    pa.situacao,
    pa.data_inscricao,
    a.nome,
    a.posicao,
    a.cidade,
    a.estado,
    TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) AS idade';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conexao->prepare(
        'SELECT ' . $colunas . '
           FROM participacao pa
           JOIN atleta a ON a.id_atleta = pa.id_atleta
          WHERE pa.id_participacao = ?'
    );
    $stmt->bind_param('i', $id);

} elseif (isset($_GET['id_partida'])) {
    $id_partida = (int) $_GET['id_partida'];

    $stmt = $conexao->prepare(
        'SELECT ' . $colunas . '
           FROM participacao pa
           JOIN atleta a ON a.id_atleta = pa.id_atleta
          WHERE pa.id_partida = ?
          ORDER BY a.nome'
    );
    $stmt->bind_param('i', $id_partida);

} else {
    responder('nok', 'Informe o id do evento.');
}

$stmt->execute();
$resultado = $stmt->get_result();
$tabela    = [];

if ($resultado->num_rows > 0) {
    while ($linha = $resultado->fetch_assoc()) {
        $tabela[] = $linha;
    }

    $stmt->close();
    $conexao->close();
    responder('ok', 'Consulta efetuada.', $tabela);
}

$stmt->close();
$conexao->close();
responder('nok', 'Nenhum jogador inscrito neste evento.');
