<?php // php/jogador_alterar.php  ->  UPDATE

/* O id da inscricao vem pela URL ($_GET['id'])
   Os dados novos vem pelo body   ($_POST, via FormData) */

include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

if (!isset($_GET['id'])) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Id da inscricao nao informado.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

$id        = (int) $_GET['id'];
$id_atleta = (int) ($_POST['id_atleta'] ?? 0);
$situacao  = trim($_POST['situacao'] ?? '');

if ($id_atleta < 1) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Selecione o atleta.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

if ($situacao != 'PENDENTE_PAGAMENTO' && $situacao != 'CONFIRMADA'
    && $situacao != 'CANCELADA' && $situacao != 'PRESENTE'
    && $situacao != 'FALTOU') {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Situacao invalida.';
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}

/* A tabela tem UNIQUE (id_partida, id_atleta): se o atleta escolhido
   ja tiver outra inscricao no mesmo evento, o UPDATE seria recusado.
   Conferir antes deixa a mensagem clara. */
$stmt = $conexao->prepare(
    'SELECT id_participacao
       FROM participacao
      WHERE id_atleta = ?
        AND id_partida = (SELECT id_partida FROM participacao WHERE id_participacao = ?)
        AND id_participacao <> ?'
);
$stmt->bind_param('iii', $id_atleta, $id, $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Este atleta ja esta inscrito neste evento.';
    $stmt->close();
    $conexao->close();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($retorno);
    exit;
}
$stmt->close();

$stmt = $conexao->prepare(
    'UPDATE participacao
        SET id_atleta = ?,
            situacao  = ?
      WHERE id_participacao = ?'
);

// Bind: 'isi' = 1 integer + 1 string + o id do WHERE
$stmt->bind_param('isi', $id_atleta, $situacao, $id);

/* Nao usamos affected_rows: salvar sem mudar nada devolve 0 linhas
   afetadas mesmo tendo executado o UPDATE corretamente. */
if ($stmt->execute()) {
    $retorno['status']   = 'ok';
    $retorno['mensagem'] = 'Inscricao alterada com sucesso.';
} else {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Nao foi possivel alterar a inscricao.';
}

$stmt->close();
$conexao->close();
header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
