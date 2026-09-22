/* ==========================================================
    CRUD DE EVENTOS - CraqueOn
    Os dados sao armazenados no LocalStorage do navegador.
   ========================================================== */

// Lista de objetos com todos os eventos cadastrados.
let eventos = [];


/* ---------- LOCALSTORAGE: ler e gravar ---------- */

// Recupera a lista que esta salva no LocalStorage.
function carregarEventos() {
    let dados = localStorage.getItem("eventos");

    if (dados == null) {
        eventos = [];
    } else {
        eventos = JSON.parse(dados);
    }
}

// Grava a lista no LocalStorage.
function gravarEventos() {
    localStorage.setItem("eventos", JSON.stringify(eventos));
}


/* ---------- CREATE e UPDATE ---------- */

// Gera um id novo, sempre maior que o maior id ja usado.
function gerarId() {
    let maiorId = 0;

    for (let i = 0; i < eventos.length; i++) {
        if (eventos[i].id > maiorId) {
            maiorId = eventos[i].id;
        }
    }

    return maiorId + 1;
}

// Cadastra um evento novo ou altera um evento ja existente.
function salvarEvento(e) {
    e.preventDefault();

    let id = document.getElementById("eventoId").value;

    let evento = {
        id: 0,
        nome: document.getElementById("nome").value,
        esporte: document.getElementById("esporte").value,
        data: document.getElementById("data").value,
        horario: document.getElementById("horario").value,
        local: document.getElementById("local").value,
        qtdJogadores: parseInt(document.getElementById("qtdJogadores").value),
        valor: parseFloat(document.getElementById("valor").value)
    };

    if (id == "") {
        // CREATE: cadastra um evento novo
        evento.id = gerarId();
        eventos.push(evento);
        alert("Evento cadastrado com sucesso!");
    } else {
        // UPDATE: altera o evento que tem esse id
        evento.id = parseInt(id);

        for (let i = 0; i < eventos.length; i++) {
            if (eventos[i].id == evento.id) {
                eventos[i] = evento;
            }
        }

        alert("Evento alterado com sucesso!");
    }

    gravarEventos();
    limparFormulario();
    listarEventos();
}

// Coloca os dados do evento no formulario para o usuario alterar.
function editarEvento(id) {
    for (let i = 0; i < eventos.length; i++) {
        if (eventos[i].id == id) {
            document.getElementById("eventoId").value = eventos[i].id;
            document.getElementById("nome").value = eventos[i].nome;
            document.getElementById("esporte").value = eventos[i].esporte;
            document.getElementById("data").value = eventos[i].data;
            document.getElementById("horario").value = eventos[i].horario;
            document.getElementById("local").value = eventos[i].local;
            document.getElementById("qtdJogadores").value = eventos[i].qtdJogadores;
            document.getElementById("valor").value = eventos[i].valor;

            document.getElementById("tituloFormulario").textContent = "Alterando evento";
            document.getElementById("botaoSalvar").textContent = "Salvar alteracoes";
        }
    }
}

// Limpa o formulario e volta para o modo de cadastro.
function limparFormulario() {
    document.getElementById("formEvento").reset();
    document.getElementById("eventoId").value = "";
    document.getElementById("tituloFormulario").textContent = "Novo evento";
    document.getElementById("botaoSalvar").textContent = "Cadastrar";
}


/* ---------- DELETE ---------- */

// Exclui o evento que tem o id informado.
function excluirEvento(id) {
    if (confirm("Deseja realmente excluir este evento?")) {

        // Monta uma lista nova com todos os eventos, menos o excluido
        let novaLista = [];

        for (let i = 0; i < eventos.length; i++) {
            if (eventos[i].id != id) {
                novaLista.push(eventos[i]);
            }
        }

        eventos = novaLista;

        gravarEventos();
        listarEventos();
    }
}


/* ---------- READ ---------- */

// Monta a tabela na tela com os eventos da lista.
function listarEventos() {
    let corpoTabela = document.getElementById("corpoTabela");

    corpoTabela.innerHTML = "";

    for (let i = 0; i < eventos.length; i++) {
        let evento = eventos[i];

        corpoTabela.innerHTML +=
            "<tr>" +
                "<td>" + evento.id + "</td>" +
                "<td>" + evento.nome + "</td>" +
                "<td>" + evento.esporte + "</td>" +
                "<td>" + formatarData(evento.data) + "</td>" +
                "<td>" + evento.horario + "</td>" +
                "<td>" + evento.local + "</td>" +
                "<td>" + evento.qtdJogadores + "</td>" +
                "<td>R$ " + evento.valor.toFixed(2) + "</td>" +
                "<td>" +
                    "<button class='editar' onclick='editarEvento(" + evento.id + ")'>Editar</button> " +
                    "<button class='excluir' onclick='excluirEvento(" + evento.id + ")'>Excluir</button>" +
                    "<button class='detalhes' onclick='window.location.href=\"pain/index.html?id=" + evento.id + "\"'>Detalhes</button>" +
                "</td>" +
            "</tr>";
    }
}

// Converte a data de 2026-10-15 para 15/10/2026
function formatarData(data) {
    let partes = data.split("-");
    return partes[2] + "/" + partes[1] + "/" + partes[0];
}


/* ---------- INICIO ---------- */

// Quando a pagina abre, le o LocalStorage e monta a tabela.
window.onload = function () {
    carregarEventos();
    listarEventos();
};
