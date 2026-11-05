<?php // php/evento_opcoes.php  ->  listas para os campos de selecao

/* O formulario de evento nao digita o esporte, o local e o
organizador: eles sao chaves estrangeiras (id_modalidade,
id_local, id_organizador). Este arquivo devolve as opcoes
   que o JavaScript usa para montar os <select>.             */

include_once('conexao.php');

$dados = [
    'modalidades'   => [],
    'locais'        => [],
    'organizadores' => []
];

// Modalidades (o "esporte" do evento)
$resultado = $conexao->query(
    'SELECT id_modalidade, nome FROM modalidade ORDER BY nome'
);
while ($linha = $resultado->fetch_assoc()) {
    $dados['modalidades'][] = $linha;
}

// Locais esportivos
$resultado = $conexao->query(
    'SELECT id_local, nome, cidade, estado
    FROM local_esportivo
    ORDER BY nome'
);
while ($linha = $resultado->fetch_assoc()) {
    $dados['locais'][] = $linha;
}

// Organizadores: usuarios ativos com a permissao ORGANIZADOR ou ADMIN,
// mais quem ja organiza alguma partida (para nao sumir da lista na edicao)
$resultado = $conexao->query(
    'SELECT DISTINCT u.id_usuario, u.nome
    FROM usuario u
    LEFT JOIN usuario_permissao up ON up.id_usuario  = u.id_usuario
    LEFT JOIN permissao p          ON p.id_permissao = up.id_permissao
    WHERE u.status = "ATIVO"
        AND (p.nome IN ("ORGANIZADOR","ADMIN")
        OR u.id_usuario IN (SELECT id_organizador FROM partida))
    ORDER BY u.nome'
);
while ($linha = $resultado->fetch_assoc()) {
    $dados['organizadores'][] = $linha;
}

$conexao->close();
responder('ok', 'Opcoes carregadas.', $dados);
