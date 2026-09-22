document.getElementById('btn_cadastrar').addEventListener('click', () => {
    let nome = document.getElementById("name").value;
    let email = document.getElementById("email").value;
    let telefone = document.getElementById("telefone").value;
    let idade = document.getElementById("idade").value;
    let esporte = document.getElementById("esporte").value;

    let lista = JSON.parse(localStorage.getItem("usuarios"));

    if (!lista) { lista = []; }

    lista.push({ nome, email, telefone, idade, esporte });
    localStorage.setItem("usuarios", JSON.stringify(lista));

    listar();

    document.getElementById("name").value = "";
    document.getElementById("email").value = "";
    document.getElementById("telefone").value = "";
    document.getElementById("idade").value = "";
    document.getElementById("esporte").value = "";
});

function listar() {
    let lista = JSON.parse(localStorage.getItem("usuarios"));

    if (!lista) { lista = []; }

    document.getElementById('ul_dados').innerHTML = "";

    if (lista.length === 0) {
        document.getElementById('ul_dados').innerHTML = 
        "<tr><td colspan='6'>Nenhum usuário cadastrado.</td></tr>";
        return;
    }

    let indice = 0;
    for (usuario of lista) {
        document.getElementById('ul_dados').innerHTML += `<tr>
        <td>${usuario.nome}</td>
        <td>${usuario.email}</td>
        <td>${usuario.telefone}</td>
        <td>${usuario.idade}</td>
        <td>${usuario.esporte}</td>
        <td>
            <button class="btn_excluir" onclick="excluir(${indice})">Excluir</button>
            <button class="btn_carregar" onclick="carregar(${indice})">Carregar</button>
        </td>
    </tr>`;
        indice++;
    }
};

function excluir(indice) {
    let lista = JSON.parse(localStorage.getItem("usuarios"));
    lista.splice(indice, 1);
    localStorage.setItem("usuarios", JSON.stringify(lista));
    listar();
};

function carregar(indice) {
    let lista = JSON.parse(localStorage.getItem("usuarios"));
    document.getElementById('name').value = lista[indice].nome;
    document.getElementById('email').value = lista[indice].email;
    document.getElementById('telefone').value = lista[indice].telefone;
    document.getElementById('idade').value = lista[indice].idade;
    document.getElementById('esporte').value = lista[indice].esporte;
    document.getElementById("alterar").innerHTML = `<button class="btn_alterar" onclick="alterar(${indice})">Alterar</button>`;

};

function alterar(indice) {
    let lista = JSON.parse(localStorage.getItem("usuarios"));
    lista[indice].nome = document.getElementById('name').value;
    lista[indice].email = document.getElementById('email').value;
    lista[indice].telefone = document.getElementById('telefone').value;
    lista[indice].idade = document.getElementById('idade').value;
    lista[indice].esporte = document.getElementById('esporte').value;

    document.getElementById("alterar").innerHTML = "";

    localStorage.setItem("usuarios", JSON.stringify(lista));
    listar();

    document.getElementById('name').value = "";
    document.getElementById('email').value = "";
    document.getElementById('telefone').value = "";
    document.getElementById('idade').value = "";
    document.getElementById('esporte').value = "";

};

listar();