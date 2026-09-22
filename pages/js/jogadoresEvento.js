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
// listar os jogadores

function listar() {

    // Pega os jogadores salvos no localStorage
    let jogadores = JSON.parse(localStorage.getItem("jogadores")) || [];

    
    document.getElementById("tabela-jogadores").innerHTML = `
        <thead>
            <tr>
                <th>Nome</th>
                <th>Idade</th>
                <th>Posição</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody id="corpo-tabela"></tbody>
    `;

    // Cria o índice para identificar cada jogador
    let indice = 0;

    // Percorre todos os jogadores
    for (let jogador of jogadores) {

        // Adiciona uma nova linha na tabela
        document.getElementById("corpo-tabela").innerHTML += `

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

        // Passa para o próximo jogador
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
//alterar jogadores
function carregar(indice) {

    let jogadores = JSON.parse(localStorage.getItem("jogadores"));

    document.getElementById("nome").value = jogadores[indice].nome;

    document.getElementById("idade").value = jogadores[indice].idade;

    document.getElementById("posicao").value = jogadores[indice].posicao;
     // Esconde o botão de adicionar
    document.getElementById("btn-adicionar").style.display = "none";

    document.getElementById("alterar").innerHTML = `

        <button 
            class="btn-alterar"
            onclick="alterar(${indice})">

            Salvar alteração

        </button>

    `;
}
//salvar alterações do jogador
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
    //botão de adicionar volta a aparecer
    document.getElementById("btn-adicionar").style.display = "block";

    limparCampos();

    listar();
}
function limparCampos() {

    document.getElementById("nome").value = "";

    document.getElementById("idade").value = "";

    document.getElementById("posicao").value = "";
}

listar();