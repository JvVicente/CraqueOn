<?php // php/evento_get.php  ->  READ (SELECT)

/* No banco do grupo, o "evento" do aplicativo e a PARTIDA do
   tipo ABERTA: aquela que tem vagas e aceita inscricao de
   atletas (tabela partida + participacao).

   Dois comportamentos num unico arquivo:
     - sem parametro    : devolve todos os eventos (listagem)
     - com ?id=X na URL : devolve apenas um evento (editar)   */

include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

if (isset($_GET['id'])) {
    // Buscar por ID -> usado na Fase 1 do alterar
    $id = (int) $_GET['id'];

    $stmt = $conexao->prepare(
        'SELECT p.id_partida,
                p.titulo,
                DATE(p.data_hora) AS data_evento,
                TIME(p.data_hora) AS horario,
                p.vagas_total,
                p.valor_inscricao,
                p.status,
                p.id_modalidade,
                m.nome AS modalidade,
                p.id_local,
                l.nome AS local_nome,
                p.id_organizador,
                u.nome AS organizador,
                (SELECT COUNT(*) FROM participacao pa
                  WHERE pa.id_partida = p.id_partida
                    AND pa.situacao IN ("PENDENTE_PAGAMENTO","CONFIRMADA","PRESENTE")
                ) AS vagas_ocupadas
           FROM partida p
           JOIN modalidade m      ON m.id_modalidade = p.id_modalidade
           JOIN usuario u         ON u.id_usuario    = p.id_organizador
      LEFT JOIN local_esportivo l ON l.id_local      = p.id_local
          WHERE p.tipo = "ABERTA"
            AND p.id_partida = ?'
    );
    $stmt->bind_param('i', $id);  // 'i' = integer

} else {
    // Buscar todos -> usado na listagem
    $stmt = $conexao->prepare(
        'SELECT p.id_partida,
                p.titulo,
                DATE(p.data_hora) AS data_evento,
                TIME(p.data_hora) AS horario,
                p.vagas_total,
                p.valor_inscricao,
                p.status,
                p.id_modalidade,
                m.nome AS modalidade,
                p.id_local,
                l.nome AS local_nome,
                p.id_organizador,
                u.nome AS organizador,
                (SELECT COUNT(*) FROM participacao pa
                  WHERE pa.id_partida = p.id_partida
                    AND pa.situacao IN ("PENDENTE_PAGAMENTO","CONFIRMADA","PRESENTE")
                ) AS vagas_ocupadas
           FROM partida p
           JOIN modalidade m      ON m.id_modalidade = p.id_modalidade
           JOIN usuario u         ON u.id_usuario    = p.id_organizador
      LEFT JOIN local_esportivo l ON l.id_local      = p.id_local
          WHERE p.tipo = "ABERTA"
          ORDER BY p.data_hora'
    );
}

$stmt->execute();
$resultado = $stmt->get_result();
$tabela = [];

if ($resultado->num_rows > 0) {
    while ($linha = $resultado->fetch_assoc()) {
        $tabela[] = $linha;  // adiciona cada linha ao array
    }
    $retorno['status']   = 'ok';
    $retorno['mensagem'] = 'Consulta efetuada.';
    $retorno['data']     = $tabela;
} else {
    $retorno['status']   = 'nok';
    $retorno['mensagem'] = 'Nao ha eventos cadastrados.';
}

$stmt->close();
$conexao->close();
header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
