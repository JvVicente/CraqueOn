<?php // php/evento_opcoes.php  ->  listas para os campos de selecao

/* O formulario de evento nao digita o esporte, o local e o
   organizador: eles sao chaves estrangeiras (id_modalidade,
   id_local, id_organizador). Este arquivo devolve as opcoes
   que o JavaScript usa para montar os <select>.             */

include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

$modalidades   = [];
$locais        = [];
$organizadores = [];

// Modalidades (o "esporte" do evento)
$stmt = $conexao->prepare('SELECT id_modalidade, nome FROM modalidade ORDER BY nome');
$stmt->execute();
$resultado = $stmt->get_result();

while ($linha = $resultado->fetch_assoc()) {
    $modalidades[] = $linha;
}
$stmt->close();

// Locais esportivos
$stmt = $conexao->prepare(
    'SELECT id_local, nome, cidade, estado FROM local_esportivo ORDER BY nome'
);
$stmt->execute();
$resultado = $stmt->get_result();

while ($linha = $resultado->fetch_assoc()) {
    $locais[] = $linha;
}
$stmt->close();

// Organizadores: usuarios ativos com permissao de ORGANIZADOR ou ADMIN,
// mais quem ja organiza alguma partida (para nao sumir da lista na edicao)
$stmt = $conexao->prepare(
    'SELECT DISTINCT u.id_usuario, u.nome
       FROM usuario u
  LEFT JOIN usuario_permissao up ON up.id_usuario  = u.id_usuario
  LEFT JOIN permissao p          ON p.id_permissao = up.id_permissao
      WHERE u.status = "ATIVO"
        AND (p.nome IN ("ORGANIZADOR","ADMIN")
             OR u.id_usuario IN (SELECT id_organizador FROM partida))
      ORDER BY u.nome'
);
$stmt->execute();
$resultado = $stmt->get_result();

while ($linha = $resultado->fetch_assoc()) {
    $organizadores[] = $linha;
}
$stmt->close();

$retorno['status']   = 'ok';
$retorno['mensagem'] = 'Opcoes carregadas.';
$retorno['data']     = [
    'modalidades'   => $modalidades,
    'locais'        => $locais,
    'organizadores' => $organizadores
];

$conexao->close();
header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
