/* ==========================================================
    CRUD DE EVENTOS - CraqueOn
    FRONT > BACK > BANCO

    No banco do grupo, o "evento" e a PARTIDA do tipo ABERTA:
    a partida que tem vagas e recebe inscricao de atletas.
    Por isso o esporte, o local e o organizador sao escolhidos
    em listas (chaves estrangeiras), e nao digitados.

    Este arquivo NAO acessa o banco. Ele apenas:
      1. captura o que o usuario escolheu/digitou;
      2. monta um FormData (ou a URL, no caso de GET);
      3. envia com fetch() para o arquivo PHP correspondente;
      4. le o JSON de resposta (status / mensagem / data) e
         atualiza a tela.
   ========================================================== */

// Caminho da pasta dos arquivos PHP (a partir de /pages/)
const CAMINHO_PHP = "../php/";

/* ---------- OPCOES DOS CAMPOS DE SELECAO ---------- */

// Busca modalidades, locais e organizadores e monta os <select>.
async function carregarOpcoes() {
    const retorno  = await fetch(CAMINHO_PHP + "evento_opcoes.php");
    const resposta = await retorno.json();

    if (resposta.status != "ok") {
        alert(resposta.mensagem);
        return;
    }

    preencherSelect("idModalidade", resposta.data.modalidades, "id_modalidade",
        (item) => item.nome);

    preencherSelect("idLocal", resposta.data.locais, "id_local",
        (item) => item.nome + " - " + item.cidade + "/" + item.estado);

    preencherSelect("idOrganizador", resposta.data.organizadores, "id_usuario",
        (item) => item.nome);
}

// Monta as <option> de um campo de selecao.
function preencherSelect(idCampo, lista, campoValor, montarTexto) {
    const campo = document.getElementById(idCampo);

    campo.innerHTML = "<option value=''>Selecione...</option>";

    for (const item of lista) {
        campo.innerHTML +=
            "<option value='" + item[campoValor] + "'>" + montarTexto(item) + "</option>";
    }
}


/* ---------- READ: listar os eventos do banco ---------- */

// GET simples: evento_get.php sem parametro devolve todos os eventos.
async function listarEventos() {
    const retorno  = await fetch(CAMINHO_PHP + "evento_get.php");
    const resposta = await retorno.json();

    const corpoTabela = document.getElementById("corpoTabela");
    corpoTabela.innerHTML = "";

    if (resposta.status != "ok") {
        // Nao ha registros (ou houve erro): mostra um aviso na tabela
        corpoTabela.innerHTML =
            "<tr><td colspan='10'>" + resposta.mensagem + "</td></tr>";
        return;
    }

    // resposta.data e o array de eventos que o PHP leu do MySQL
    for (const evento of resposta.data) {
        const local = (evento.local_nome == null) ? "-" : evento.local_nome;

        corpoTabela.innerHTML +=
            "<tr>" +
                "<td>" + evento.id_partida + "</td>" +
                "<td>" + evento.titulo + "</td>" +
                "<td>" + evento.modalidade + "</td>" +
                "<td>" + formatarData(evento.data_evento) + "</td>" +
                "<td>" + formatarHorario(evento.horario) + "</td>" +
                "<td>" + local + "</td>" +
                "<td>" + evento.vagas_ocupadas + "/" + evento.vagas_total + "</td>" +
                "<td>R$ " + formatarValor(evento.valor_inscricao) + "</td>" +
                "<td>" + formatarStatus(evento.status) + "</td>" +
                "<td>" +
                    "<button class='editar' onclick='editarEvento(" + evento.id_partida + ")'>Editar</button> " +
                    "<button class='excluir' onclick='excluirEvento(" + evento.id_partida + ")'>Excluir</button> " +
                    "<button class='detalhes' onclick='abrirJogadores(" + evento.id_partida + ")'>Jogadores</button>" +
                "</td>" +
            "</tr>";
    }
}

// Abre a tela de jogadores daquele evento.
function abrirJogadores(id) {
    window.location.href = "jogadoresEvento.html?id=" + id;
}


/* ---------- CREATE e UPDATE ---------- */

// Chamada pelo submit do formulario.
// Se o campo escondido "eventoId" estiver vazio -> cadastro (INSERT).
// Se tiver um id -> alteracao (UPDATE).
async function salvarEvento(e) {
    e.preventDefault();

    const id = document.getElementById("eventoId").value;

    // FormData: e assim que os campos chegam no $_POST do PHP.
    // A chave usada aqui ("titulo", "id_modalidade", ...) e a mesma
    // que o PHP le em $_POST['titulo'], $_POST['id_modalidade'], ...
    const fd = new FormData();
    fd.append("titulo",          document.getElementById("titulo").value);
    fd.append("id_modalidade",   document.getElementById("idModalidade").value);
    fd.append("id_local",        document.getElementById("idLocal").value);
    fd.append("id_organizador",  document.getElementById("idOrganizador").value);
    fd.append("data_evento",     document.getElementById("data").value);
    fd.append("horario",         document.getElementById("horario").value);
    fd.append("vagas_total",     document.getElementById("vagasTotal").value);
    fd.append("valor_inscricao", document.getElementById("valor").value);
    fd.append("status",          document.getElementById("status").value);

    // CREATE: POST para evento_novo.php
    // UPDATE: POST para evento_alterar.php com o id na URL
    let url = "evento_novo.php";

    if (id != "") {
        url = "evento_alterar.php?id=" + id;
    }

    const retorno  = await fetch(CAMINHO_PHP + url, { method: "POST", body: fd });
    const resposta = await retorno.json();

    alert(resposta.mensagem);

    if (resposta.status == "ok") {
        limparFormulario();
        listarEventos();
    }
}

// Fase 1 da alteracao: busca o evento pelo id (GET ?id=X) e
// preenche o formulario com os dados que vieram do banco.
async function editarEvento(id) {
    const retorno  = await fetch(CAMINHO_PHP + "evento_get.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status != "ok") {
        alert(resposta.mensagem);
        return;
    }

    const evento = resposta.data[0];

    document.getElementById("eventoId").value      = evento.id_partida;
    document.getElementById("titulo").value        = evento.titulo;
    document.getElementById("idModalidade").value  = evento.id_modalidade;
    document.getElementById("idLocal").value       = evento.id_local;
    document.getElementById("idOrganizador").value = evento.id_organizador;
    document.getElementById("data").value          = evento.data_evento;
    // o MySQL devolve 19:30:00 e o input type=time usa 19:30
    document.getElementById("horario").value       = formatarHorario(evento.horario);
    document.getElementById("vagasTotal").value    = evento.vagas_total;
    document.getElementById("valor").value         = evento.valor_inscricao;
    document.getElementById("status").value        = evento.status;

    document.getElementById("tituloFormulario").textContent = "Alterando evento";
    document.getElementById("botaoSalvar").textContent      = "Salvar alteracoes";

    window.scrollTo(0, 0);
}

// Limpa o formulario e volta para o modo de cadastro.
function limparFormulario() {
    document.getElementById("formEvento").reset();
    document.getElementById("eventoId").value = "";
    document.getElementById("tituloFormulario").textContent = "Novo evento";
    document.getElementById("botaoSalvar").textContent      = "Cadastrar";
}


/* ---------- DELETE ---------- */

// GET com o id na URL: evento_excluir.php?id=X
async function excluirEvento(id) {
    if (!confirm("Excluir este evento? As inscricoes dele tambem serao apagadas.")) {
        return;
    }

    const retorno  = await fetch(CAMINHO_PHP + "evento_excluir.php?id=" + id);
    const resposta = await retorno.json();

    alert(resposta.mensagem);

    if (resposta.status == "ok") {
        limparFormulario();
        listarEventos();
    }
}


/* ---------- FORMATACAO ---------- */

// Converte a data de 2026-11-13 para 13/11/2026
function formatarData(data) {
    const partes = data.split("-");
    return partes[2] + "/" + partes[1] + "/" + partes[0];
}

// Converte o horario de 19:30:00 para 19:30
function formatarHorario(horario) {
    return horario.substring(0, 5);
}

// O MySQL devolve DECIMAL como texto ("25.00"); mostra com 2 casas
function formatarValor(valor) {
    return parseFloat(valor).toFixed(2);
}

// Troca o ENUM do banco por um texto mais legivel
function formatarStatus(status) {
    if (status == "EM_ANDAMENTO") {
        return "Em andamento";
    }

    return status.charAt(0) + status.substring(1).toLowerCase();
}


/* ---------- INICIO ---------- */

// Primeiro carrega as listas dos campos de selecao, depois a tabela.
document.addEventListener("DOMContentLoaded", async () => {
    await carregarOpcoes();
    listarEventos();
});
