<?php // php/jogador_get.php  ->  READ (SELECT)

/* "Jogadores do evento" sao as inscricoes da tabela participacao:
   cada linha liga um atleta a uma partida.

   Dois comportamentos num unico arquivo:
     - ?id_partida=X : todos os inscritos naquele evento (listagem)
     - ?id=X         : uma inscricao (preencher o formulario)      */

include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conexao->prepare(
        'SELECT pa.id_participacao,
                pa.id_partida,
                pa.id_atleta,
                pa.situacao,
                pa.data_inscricao,
                a.nome,
                a.posicao,
                TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) AS idade
           FROM participacao pa
           JOIN atleta a ON a.id_atleta = pa.id_atleta
          WHERE pa.id_participacao = ?'
    );
    $stmt->bind_param('i', $id);

} else {
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
        'SELECT pa.id_participacao,
                pa.id_partida,
                pa.id_atleta,
                pa.situacao,
                pa.data_inscricao,
                a.nome,
                a.posicao,
                TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) AS idade
           FROM participacao pa
           JOIN atleta a ON a.id_atleta = pa.id_atleta
          WHERE pa.id_partida = ?
          ORDER BY a.nome'
    );
    $stmt->bind_param('i', $id_partida);
}

$stmt->execute();
$resultado = $stmt->get_result();
$tabela = [];

if ($resultado->num_rows > 0) {
    while ($linha = $resultado->fetch_assoc()) {
        $tabela[] = $linha;
    }
    $retorno['status']   = 'ok';
    $retorno['mensagem'] = 'Consulta efetuada.';
    $retorno['data']     = $tabela;
} else {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Nenhum jogador inscrito neste evento.';
}

$stmt->close();
$conexao->close();
header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
