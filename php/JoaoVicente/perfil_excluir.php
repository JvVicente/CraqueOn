<?php // php/JoaoVicente/perfil_excluir.php
include_once('conexao.php');

$retorno = ['status' => '', 'mensagem' => '', 'data' => []];

if (isset($_GET['id'])) {
    $stmt = $conexao->prepare('DELETE FROM usuario WHERE id_usuario = ?');
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno['status'] = 'ok';
        $retorno['mensagem'] = 'Registro excluido';
    } else {
        $retorno['status'] = 'nok';
        if ($stmt->errno == 1451) {
            // chave estrangeira: o usuario e organizador de alguma partida (ON DELETE RESTRICT)
            $retorno['mensagem'] = 'Nao e possivel excluir: o usuario organiza partidas.';
        } else {
            $retorno['mensagem'] = 'Registro nao excluido';
        }
    }

    $stmt->close();
} else {
    $retorno['status'] = 'nok';
    $retorno['mensagem'] = 'E necessario informar um ID para exclusao';
}

$conexao->close();

header('Content-type: application/json; charset=utf-8');
echo json_encode($retorno);
