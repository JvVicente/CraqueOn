<?php // php/evento_get.php  ->  READ (SELECT)

/* No banco do grupo, o "evento" do aplicativo e a PARTIDA do
tipo ABERTA: aquela que tem vagas e aceita inscricao de
atletas (tabela partida + participacao).

Dois comportamentos em um unico arquivo:
    - sem parametro    : devolve todos os eventos (listagem)
     - com ?id=X na URL : devolve apenas um evento (editar)   */

include_once('conexao.php');

// Colunas do SELECT (as mesmas nos dois casos).
// DATE() e TIME() separam o campo data_hora para os inputs
// type="date" e type="time" do formulario.
$colunas = '
    p.id_partida,
    p.titulo,
    p.data_hora,
    DATE(p.data_hora)        AS data_evento,
    TIME(p.data_hora)        AS horario,
    p.vagas_total,
    p.valor_inscricao,
    p.status,
    p.id_modalidade,
    m.nome                   AS modalidade,
    p.id_local,
    l.nome                   AS local_nome,
    l.cidade,
    l.estado,
    p.id_organizador,
    u.nome                   AS organizador,
    (SELECT COUNT(*)
    FROM participacao pa
    WHERE pa.id_partida = p.id_partida
        AND pa.situacao IN (\'PENDENTE_PAGAMENTO\',\'CONFIRMADA\',\'PRESENTE\')
    ) AS vagas_ocupadas';

$juncoes = '
    FROM partida p
    JOIN modalidade m       ON m.id_modalidade = p.id_modalidade
    JOIN usuario u          ON u.id_usuario    = p.id_organizador
    LEFT JOIN local_esportivo l  ON l.id_local      = p.id_local';

if (isset($_GET['id'])) {
    // SELECT por id -> usado para preencher o formulario na edicao
    $id = (int) $_GET['id'];

    $stmt = $conexao->prepare(
        'SELECT ' . $colunas . $juncoes .
        ' WHERE p.tipo = "ABERTA" AND p.id_partida = ?'
    );
    $stmt->bind_param('i', $id);  // 'i' = integer
} else {
    // SELECT de todos -> usado na listagem da tela
    $stmt = $conexao->prepare(
        'SELECT ' . $colunas . $juncoes .
        ' WHERE p.tipo = "ABERTA" ORDER BY p.data_hora'
    );
}

$stmt->execute();
$resultado = $stmt->get_result();
$tabela    = [];

if ($resultado->num_rows > 0) {
    // Percorre cada linha retornada pelo banco
    while ($linha = $resultado->fetch_assoc()) {
        $tabela[] = $linha;
    }

    $stmt->close();
    $conexao->close();
    responder('ok', 'Consulta efetuada.', $tabela);
}

$stmt->close();
$conexao->close();
responder('nok', 'Nao ha eventos cadastrados.');
