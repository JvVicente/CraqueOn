document.getElementById("btn-adicionar").addEventListener("click", () => {
    //pegar os valores dos inputs
    let nome = document.getElementById("nome").value;
    let idade = document.getElementById("idade").value;
    let posicao = document.getElementById("posicao").value;
    //verificar se os campos estão preenchidos
    let jogadores = JSON.parse(localStorage.getItem("jogadores"));

    if (!jogadores) {
        jogadores = [];
    }
    //adicionar o jogador no array
    jogadores.push({
        nome: nome,
        idade: idade,
        posicao: posicao
    });

    localStorage.setItem("jogadores", JSON.stringify(jogadores));

    limparCampos();

    listar();

});
//listar os jogadores 
function listar() {

    let jogadores = JSON.parse(localStorage.getItem("jogadores")) || [];

    document.querySelector("#tabela-jogadores tbody").innerHTML = "";

    let indice = 0;

    for (let jogador of jogadores) {

        document.querySelector("#tabela-jogadores tbody").innerHTML += `
            <tr>
                <td>${jogador.nome}</td>
                <td>${jogador.idade}</td>
                <td>${jogador.posicao}</td>

                <td>
                    <button 
                        class="btn-carregar"
                        onclick="carregar(${indice})">
                        Alterar
                    </button>

                    <button 
                        class="btn-excluir"
                        onclick="excluir(${indice})">
                        Excluir
                    </button>
                </td>
            </tr>
        `;

        indice++;
    }
}
//excluir jogador
function excluir(indice) {

    let jogadores = JSON.parse(localStorage.getItem("jogadores"));

    jogadores.splice(indice, 1);

    localStorage.setItem(
        "jogadores",
        JSON.stringify(jogadores)
    );

    listar();
}
//carregar jogador
function carregar(indice) {

    let jogadores = JSON.parse(localStorage.getItem("jogadores"));

    document.getElementById("nome").value = jogadores[indice].nome;

    document.getElementById("idade").value = jogadores[indice].idade;

    document.getElementById("posicao").value = jogadores[indice].posicao;

    document.getElementById("alterar").innerHTML = `

        <button 
            class="btn-alterar"
            onclick="alterar(${indice})">

            Salvar alteração

        </button>

    `;
}
//alterar jogador
function alterar(indice) {

    let jogadores = JSON.parse(localStorage.getItem("jogadores"));

    jogadores[indice].nome =
        document.getElementById("nome").value;

    jogadores[indice].idade =
        document.getElementById("idade").value;

    jogadores[indice].posicao =
        document.getElementById("posicao").value;

    localStorage.setItem(
        "jogadores",
        JSON.stringify(jogadores)
    );

    document.getElementById("alterar").innerHTML = "";

    limparCampos();

    listar();
}
function limparCampos() {

    document.getElementById("nome").value = "";

    document.getElementById("idade").value = "";

    document.getElementById("posicao").value = "";
}
//listar os jogadores que ja estao salvos quando a pagina abre
listar();