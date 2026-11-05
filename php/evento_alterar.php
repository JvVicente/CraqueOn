<?php // php/evento_alterar.php  ->  UPDATE

/* O id do evento vem pela URL   ($_GET['id'])
   Os dados novos vem pelo body  ($_POST, via FormData) */

include_once('conexao.php');

if (!isset($_GET['id'])) {
    responder('nok', 'Id do evento nao informado.');
}

$id             = (int) $_GET['id'];
$titulo         = trim($_POST['titulo'] ?? '');
$id_modalidade  = (int)   ($_POST['id_modalidade']   ?? 0);
$id_organizador = (int)   ($_POST['id_organizador']  ?? 0);
$id_local       = (int)   ($_POST['id_local']        ?? 0);
$data_evento    = trim($_POST['data_evento'] ?? '');
$horario        = trim($_POST['horario'] ?? '');
$vagas_total    = (int)   ($_POST['vagas_total']     ?? 0);
$valor          = (float) ($_POST['valor_inscricao'] ?? 0);
$status         = trim($_POST['status'] ?? 'AGENDADA');

// Mesma validacao do cadastro
if ($titulo == '' || $data_evento == '' || $horario == '') {
    responder('nok', 'Preencha o titulo, a data e o horario do evento.');
}

if ($id_modalidade < 1 || $id_organizador < 1 || $id_local < 1) {
    responder('nok', 'Selecione a modalidade, o local e o organizador.');
}

if ($vagas_total < 1) {
    responder('nok', 'A quantidade de vagas deve ser maior que zero.');
}

if ($valor < 0) {
    responder('nok', 'O valor da inscricao nao pode ser negativo.');
}

$situacoes = ['AGENDADA', 'EM_ANDAMENTO', 'FINALIZADA', 'CANCELADA'];

if (!in_array($status, $situacoes)) {
    responder('nok', 'Status invalido.');
}

// Nao deixa reduzir as vagas abaixo do numero de atletas ja inscritos
$stmt = $conexao->prepare(
    'SELECT COUNT(*) AS ocupadas
    FROM participacao
    WHERE id_partida = ?
    AND situacao IN ("PENDENTE_PAGAMENTO","CONFIRMADA","PRESENTE")'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$linha    = $stmt->get_result()->fetch_assoc();
$ocupadas = (int) $linha['ocupadas'];
$stmt->close();

if ($vagas_total < $ocupadas) {
    $conexao->close();
    responder('nok', 'Ja existem ' . $ocupadas . ' inscritos: as vagas nao podem ser menores que isso.');
}

// O banco guarda data e hora juntas na coluna data_hora (DATETIME)
$data_hora = $data_evento . ' ' . substr($horario, 0, 5) . ':00';

$stmt = $conexao->prepare(
    'UPDATE partida
        SET id_modalidade   = ?,
            id_organizador  = ?,
            id_local        = ?,
            titulo          = ?,
            data_hora       = ?,
            vagas_total     = ?,
            valor_inscricao = ?,
            status          = ?
        WHERE id_partida = ?
        AND tipo = "ABERTA"'
);

// iiissidsi = 3 integers + 2 strings + 1 integer + 1 double + 1 string + o id
$stmt->bind_param(
    'iiissidsi',
    $id_modalidade, $id_organizador, $id_local,
    $titulo, $data_hora, $vagas_total, $valor, $status, $id
);

// Nao usamos affected_rows aqui: se o usuario salvar sem mudar
// nenhum campo, o MySQL devolve 0 linhas afetadas mesmo tendo
// executado o UPDATE corretamente.
try {
    $stmt->execute();
    $stmt->close();
    $conexao->close();
    responder('ok', 'Evento alterado com sucesso.');

} catch (mysqli_sql_exception $e) {
    $stmt->close();
    $conexao->close();
    responder('nok', 'Nao foi possivel alterar: verifique modalidade, local e organizador.');
}
