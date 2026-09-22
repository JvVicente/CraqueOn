/* ==========================================================
   JOGADORES DO EVENTO - CraqueOn
   Os dados sao armazenados no LocalStorage do navegador.
   ========================================================== */

// Lista de objetos com todos os jogadores cadastrados.
let jogadores = [];


/* ---------- LOCALSTORAGE: ler e gravar ---------- */

// Recupera a lista que esta salva no LocalStorage.
function carregarJogadores() {
    let dados = localStorage.getItem("jogadores");

    if (dados == null) {
        jogadores = [];
    } else {
        jogadores = JSON.parse(dados);
    }
}

// Grava a lista no LocalStorage.
function gravarJogadores() {
    localStorage.setItem("jogadores", JSON.stringify(jogadores));
}


/* ---------- EVENTO: nome e posicoes do esporte ---------- */

// Mostra o evento que veio no endereco da pagina (jogadoresEvento.html?id=1)
// e monta as posicoes de acordo com o esporte dele.
function carregarEvento() {
    // O endereco termina com "?id=1": o split corta no "=" e pega o numero
    let id = window.location.search.split("=")[1];

    // Procura o evento na lista que a tela de eventos salvou
    let dados = localStorage.getItem("eventos");
    let eventos = [];

    if (dados != null) {
        eventos = JSON.parse(dados);
    }

    let evento = null;

    for (let i = 0; i < eventos.length; i++) {
        if (eventos[i].id == id) {
            evento = eventos[i];
        }
    }

    if (evento == null) {
        document.getElementById("nomeEvento").textContent = "não encontrado";
        return;
    }

    document.getElementById("esporte").textContent = " - " + evento.esporte;
    document.getElementById("nomeEvento").textContent = evento.nome;

    // Posicoes de cada esporte
    let posicoes = [];

    if (evento.esporte == "Futebol") {
        posicoes = ["Goleiro", "Zagueiro", "Lateral", "Volante", "Meio-campista", "Ponta", "Atacante"];
    } else if (evento.esporte == "Futsal") {
        posicoes = ["Goleiro", "Fixo", "Ala", "Pivô"];
    } else if (evento.esporte == "Volei") {
        posicoes = ["Levantador", "Oposto", "Ponteiro", "Central", "Líbero"];
    } else if (evento.esporte == "Basquete") {
        posicoes = ["Armador", "Ala-armador", "Ala", "Ala-pivô", "Pivô"];
    } else if (evento.esporte == "Handebol") {
        posicoes = ["Goleiro", "Armador", "Meia", "Ponta", "Pivô"];
    } else if (evento.esporte == "Tenis") {
        posicoes = ["Simples", "Duplas"];
    }

    // Coloca as posicoes dentro do campo de selecao
    let campoPosicao = document.getElementById("posicao");

    campoPosicao.innerHTML = "<option value=''>Selecione a posição</option>";

    for (let i = 0; i < posicoes.length; i++) {
        campoPosicao.innerHTML += "<option value='" + posicoes[i] + "'>" + posicoes[i] + "</option>";
    }
}


/* ---------- CREATE ---------- */

// Cadastra um jogador novo.
function adicionar() {
    let nome = document.getElementById("nome").value;
    let idade = document.getElementById("idade").value;
    let posicao = document.getElementById("posicao").value;

    // Verifica se os campos estao preenchidos
    if (nome == "" || idade == "" || posicao == "") {
        alert("Preencha todos os campos!");
        return;
    }

    jogadores.push({
        nome: nome,
        idade: parseInt(idade),
        posicao: posicao
    });

    gravarJogadores();
    limparCampos();
    listar();
}


/* ---------- UPDATE ---------- */

// Coloca os dados do jogador nos campos e mostra o botao de salvar a alteracao.
function carregar(indice) {
    document.getElementById("nome").value = jogadores[indice].nome;
    document.getElementById("idade").value = jogadores[indice].idade;
    document.getElementById("posicao").value = jogadores[indice].posicao;

    document.getElementById("alterar").innerHTML =
        "<button class='btn-alterar' onclick='alterar(" + indice + ")'>Salvar alteração</button>";
}

// Salva as alteracoes do jogador que esta nessa posicao da lista.
function alterar(indice) {
    let nome = document.getElementById("nome").value;
    let idade = document.getElementById("idade").value;
    let posicao = document.getElementById("posicao").value;

    if (nome == "" || idade == "" || posicao == "") {
        alert("Preencha todos os campos!");
        return;
    }

    jogadores[indice].nome = nome;
    jogadores[indice].idade = parseInt(idade);
    jogadores[indice].posicao = posicao;

    gravarJogadores();

    document.getElementById("alterar").innerHTML = "";

    limparCampos();
    listar();
}


/* ---------- DELETE ---------- */

// Exclui o jogador que esta nessa posicao da lista.
function excluir(indice) {
    if (confirm("Deseja realmente excluir este jogador?")) {

        // Monta uma lista nova com todos os jogadores, menos o excluido
        let novaLista = [];

        for (let i = 0; i < jogadores.length; i++) {
            if (i != indice) {
                novaLista.push(jogadores[i]);
            }
        }

        jogadores = novaLista;

        gravarJogadores();
        listar();
    }
}


/* ---------- READ ---------- */

// Monta a tabela na tela com os jogadores da lista.
function listar() {
    let corpoTabela = document.getElementById("corpoTabela");

    corpoTabela.innerHTML = "";

    for (let i = 0; i < jogadores.length; i++) {
        corpoTabela.innerHTML +=
            "<tr>" +
                "<td>" + jogadores[i].nome + "</td>" +
                "<td>" + jogadores[i].idade + "</td>" +
                "<td>" + jogadores[i].posicao + "</td>" +
                "<td>" +
                    "<button class='btn-carregar' onclick='carregar(" + i + ")'>Alterar</button>" +
                    "<button class='btn-excluir' onclick='excluir(" + i + ")'>Excluir</button>" +
                "</td>" +
            "</tr>";
    }
}

// Limpa os campos do formulario.
function limparCampos() {
    document.getElementById("nome").value = "";
    document.getElementById("idade").value = "";
    document.getElementById("posicao").value = "";
}


/* ---------- INICIO ---------- */

// Quando a pagina abre, mostra o evento e monta a tabela de jogadores.
window.onload = function () {
    carregarEvento();
    carregarJogadores();
    listar();
};
listar();