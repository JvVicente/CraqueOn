/* ==========================================================
   JOGADORES DO EVENTO - CraqueOn
   FRONT > BACK > BANCO

   No banco do grupo, inscrever um jogador em um evento e
   criar uma linha na tabela participacao, que liga um
   ATLETA ja cadastrado a uma PARTIDA do tipo ABERTA.

   A pagina recebe o evento pela URL:
   jogadoresEvento.html?id=5
   ========================================================== */

// Caminho da pasta dos arquivos PHP (a partir de /pages/)
const CAMINHO_PHP = "../php/";

// Id do evento (partida) que esta sendo exibido
let idEvento = 0;

// Id da inscricao que esta sendo alterada (0 = nenhuma)
let idInscricaoEmEdicao = 0;

/* ---------- EVENTO: dados do cabecalho ---------- */

// Busca no banco o evento que veio no endereco da pagina.
async function carregarEvento() {
    // URLSearchParams le o parametro id da URL
    const url = new URLSearchParams(window.location.search);
    idEvento  = url.get("id");

    if (idEvento == null || idEvento == "") {
        document.getElementById("nomeEvento").textContent = "não informado";
        return false;
    }

    const retorno  = await fetch(CAMINHO_PHP + "evento_get.php?id=" + idEvento);
    const resposta = await retorno.json();

    if (resposta.status != "ok") {
        document.getElementById("nomeEvento").textContent = "não encontrado";
        // mostra o motivo real (evento inexistente ou PHP fora do ar)
        document.getElementById("infoEvento").textContent = resposta.mensagem;
        return false;
    }

    const evento = resposta.data[0];
    const local  = (evento.local_nome == null) ? "local a definir" : evento.local_nome;

    document.getElementById("esporte").textContent    = " - " + evento.modalidade;
    document.getElementById("nomeEvento").textContent = evento.titulo;

    document.getElementById("infoEvento").textContent =
        formatarData(evento.data_evento) + " às " + formatarHorario(evento.horario) +
        " · " + local +
        " · " + evento.vagas_ocupadas + " de " + evento.vagas_total + " vagas ocupadas";

    return true;
}


/* ---------- OPCOES: atletas disponiveis ---------- */

// Preenche o campo de selecao com os atletas que ainda nao
// estao inscritos neste evento.
async function carregarAtletas(idAtletaAtual) {
    const retorno  = await fetch(CAMINHO_PHP + "jogador_opcoes.php?id_partida=" + idEvento);
    const resposta = await retorno.json();

    const campo = document.getElementById("idAtleta");
    campo.innerHTML = "<option value=''>Selecione o atleta</option>";

    if (resposta.status != "ok") {
        return;
    }

    for (const atleta of resposta.data) {
        campo.innerHTML +=
            "<option value='" + atleta.id_atleta + "'>" +
                atleta.nome + " (" + formatarPosicao(atleta.posicao) + ")" +
            "</option>";
    }

    // Na alteracao, o atleta da propria inscricao precisa aparecer
    // na lista, senao o <select> fica sem a opcao que esta salva.
    if (idAtletaAtual != null) {
        await incluirAtletaAtual(idAtletaAtual);
    }
}

// Busca o atleta da inscricao que esta sendo alterada e o
// acrescenta no campo de selecao, caso ainda nao esteja la.
async function incluirAtletaAtual(idInscricao) {
    const retorno  = await fetch(CAMINHO_PHP + "jogador_get.php?id=" + idInscricao);
    const resposta = await retorno.json();

    if (resposta.status != "ok") {
        return;
    }

    const jogador = resposta.data[0];
    const campo   = document.getElementById("idAtleta");

    campo.innerHTML +=
        "<option value='" + jogador.id_atleta + "'>" +
            jogador.nome + " (" + formatarPosicao(jogador.posicao) + ")" +
        "</option>";

    campo.value = jogador.id_atleta;
}


/* ---------- READ: listar os jogadores do evento ---------- */

// GET com o id do evento na URL: jogador_get.php?id_partida=X
async function listar() {
    const corpoTabela = document.getElementById("corpoTabela");

    const retorno  = await fetch(CAMINHO_PHP + "jogador_get.php?id_partida=" + idEvento);
    const resposta = await retorno.json();

    corpoTabela.innerHTML = "";

    if (resposta.status != "ok") {
        corpoTabela.innerHTML =
            "<tr><td colspan='6'>" + resposta.mensagem + "</td></tr>";
        return;
    }

    for (const jogador of resposta.data) {
        const idade = (jogador.idade == null) ? "-" : jogador.idade;

        corpoTabela.innerHTML +=
            "<tr>" +
                "<td>" + jogador.nome + "</td>" +
                "<td>" + idade + "</td>" +
                "<td>" + formatarPosicao(jogador.posicao) + "</td>" +
                "<td>" + formatarSituacao(jogador.situacao) + "</td>" +
                "<td>" + formatarDataHora(jogador.data_inscricao) + "</td>" +
                "<td>" +
                    "<button class='btn-carregar' onclick='carregar(" + jogador.id_participacao + ")'>Alterar</button>" +
                    "<button class='btn-excluir' onclick='excluir(" + jogador.id_participacao + ")'>Excluir</button>" +
                "</td>" +
            "</tr>";
    }
}


/* ---------- CREATE ---------- */

// POST com FormData para jogador_novo.php
async function adicionar() {
    const idAtleta = document.getElementById("idAtleta").value;
    const situacao = document.getElementById("situacao").value;

    if (idAtleta == "" || situacao == "") {
        alert("Selecione o atleta e a situacao!");
        return;
    }

    // As chaves do FormData sao as mesmas lidas no $_POST do PHP
    const fd = new FormData();
    fd.append("id_partida", idEvento);
    fd.append("id_atleta",  idAtleta);
    fd.append("situacao",   situacao);

    const retorno  = await fetch(CAMINHO_PHP + "jogador_novo.php", {
        method: "POST",
        body:   fd
    });
    const resposta = await retorno.json();

    alert(resposta.mensagem);

    if (resposta.status == "ok") {
        await recarregarTela();
    }
}


/* ---------- UPDATE ---------- */

// Fase 1: busca a inscricao pelo id (GET ?id=X) e preenche os campos.
async function carregar(id) {
    const retorno  = await fetch(CAMINHO_PHP + "jogador_get.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status != "ok") {
        alert(resposta.mensagem);
        return;
    }

    const jogador = resposta.data[0];

    idInscricaoEmEdicao = jogador.id_participacao;

    await carregarAtletas(jogador.id_participacao);

    document.getElementById("situacao").value = jogador.situacao;

    document.getElementById("alterar").innerHTML =
        "<button class='btn-alterar' onclick='alterar()'>Salvar alteração</button>" +
        "<button class='btn-excluir' onclick='cancelarEdicao()'>Cancelar</button>";
}

// Fase 2: POST com o id na URL e os dados no body.
async function alterar() {
    const idAtleta = document.getElementById("idAtleta").value;
    const situacao = document.getElementById("situacao").value;

    if (idAtleta == "" || situacao == "") {
        alert("Selecione o atleta e a situacao!");
        return;
    }

    const fd = new FormData();
    fd.append("id_atleta", idAtleta);
    fd.append("situacao",  situacao);

    const retorno  = await fetch(CAMINHO_PHP + "jogador_alterar.php?id=" + idInscricaoEmEdicao, { method: "POST", body: fd });
    const resposta = await retorno.json();

    alert(resposta.mensagem);

    if (resposta.status == "ok") {
        await recarregarTela();
    }
}

// Sai do modo de alteracao e limpa os campos.
async function cancelarEdicao() {
    idInscricaoEmEdicao = 0;
    document.getElementById("alterar").innerHTML = "";
    document.getElementById("situacao").value    = "PENDENTE_PAGAMENTO";
    await carregarAtletas(null);
}


/* ---------- DELETE ---------- */

// GET com o id da inscricao na URL: jogador_excluir.php?id=X
async function excluir(id) {
    if (!confirm("Deseja realmente excluir esta inscricao?")) {
        return;
    }

    const retorno  = await fetch(CAMINHO_PHP + "jogador_excluir.php?id=" + id);
    const resposta = await retorno.json();

    alert(resposta.mensagem);

    if (resposta.status == "ok") {
        await recarregarTela();
    }
}


/* ---------- AUXILIARES ---------- */

// Depois de gravar algo, atualiza o cabecalho (vagas), a lista
// de atletas disponiveis e a tabela.
async function recarregarTela() {
    idInscricaoEmEdicao = 0;
    document.getElementById("alterar").innerHTML = "";
    document.getElementById("situacao").value    = "PENDENTE_PAGAMENTO";

    await carregarEvento();
    await carregarAtletas(null);
    await listar();
}

// Converte a data de 2026-11-08 para 08/11/2026
function formatarData(data) {
    const partes = data.split("-");
    return partes[2] + "/" + partes[1] + "/" + partes[0];
}

// Converte o horario de 09:00:00 para 09:00
function formatarHorario(horario) {
    return horario.substring(0, 5);
}

// Converte 2026-11-01 08:00:00 para 01/11/2026 08:00
function formatarDataHora(dataHora) {
    const partes = dataHora.split(" ");
    return formatarData(partes[0]) + " " + formatarHorario(partes[1]);
}

// Troca os ENUM do banco por um texto mais legivel
function formatarPosicao(posicao) {
    return posicao.charAt(0) + posicao.substring(1).toLowerCase();
}

function formatarSituacao(situacao) {
    if (situacao == "PENDENTE_PAGAMENTO") {
        return "Pendente de pagamento";
    }

    return situacao.charAt(0) + situacao.substring(1).toLowerCase();
}


/* ---------- INICIO ---------- */

// Primeiro busca o evento, depois os atletas disponiveis e a lista.
document.addEventListener("DOMContentLoaded", async () => {
    const achou = await carregarEvento();

    if (achou) {
        await carregarAtletas(null);
        await listar();
    } else {
        document.getElementById("corpoTabela").innerHTML =
            "<tr><td colspan='6'>Abra esta tela pelo botão Jogadores da lista de eventos.</td></tr>";
    }
});
