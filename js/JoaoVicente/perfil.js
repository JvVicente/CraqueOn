document.getElementById('btn_cadastrar').addEventListener('click', () => {
    cadastrar();
});

function limparCampos() {
    document.getElementById("name").value = "";
    document.getElementById("email").value = "";
    document.getElementById("telefone").value = "";
    document.getElementById("cidade").value = "";
    document.getElementById("estado").value = "";
}

// Monta o FormData com os dados digitados no formulário
function montarFormData() {
    const fd = new FormData();
    fd.append('nome', document.getElementById("name").value);
    fd.append('email', document.getElementById("email").value);
    fd.append('telefone', document.getElementById("telefone").value);
    fd.append('cidade', document.getElementById("cidade").value);
    fd.append('estado', document.getElementById("estado").value);
    return fd;
}

// CREATE
async function cadastrar() {
    let nome = document.getElementById("name").value;
    let email = document.getElementById("email").value;
    let telefone = document.getElementById("telefone").value;
    let cidade = document.getElementById("cidade").value;
    let estado = document.getElementById("estado").value;

    if (!nome || !email || !telefone || !cidade || !estado) {
        alert("Preencha todos os campos para cadastrar.");
        return;
    }

    const retorno = await fetch('../../php/JoaoVicente/perfil_novo.php', {
        method: 'POST',
        body: montarFormData()
    });
    const resposta = await retorno.json();

    if (resposta.status == 'ok') {
        alert('SUCESSO: ' + resposta.mensagem);
        limparCampos();
        listar();
    } else {
        alert('ERRO: ' + resposta.mensagem);
    }
};

// READ
async function listar() {
    document.getElementById('ul_dados').innerHTML = "";

    try {
        const retorno = await fetch('../../php/JoaoVicente/perfil_get.php');
        const resposta = await retorno.json();

        if (resposta.status != 'ok') {
            // mostra a mensagem que o PHP devolveu (ex.: "Nao ha registros" ou falha de conexao)
            document.getElementById('ul_dados').innerHTML =
                `<tr><td colspan='6'>${resposta.mensagem}</td></tr>`;
            return;
        }

        let lista = resposta.data;

        for (const usuario of lista) {
            document.getElementById('ul_dados').innerHTML += `<tr>
        <td>${usuario.nome}</td>
        <td>${usuario.email}</td>
        <td>${usuario.telefone}</td>
        <td>${usuario.cidade}</td>
        <td>${usuario.estado}</td>
        <td>
            <button class="btn_excluir" onclick="excluir(${usuario.id_usuario})">Excluir</button>
            <button class="btn_carregar" onclick="carregar(${usuario.id_usuario})">Carregar</button>
        </td>
    </tr>`;
        }
    } catch (erro) {
        // o PHP nao respondeu JSON: Apache desligado, pagina aberta fora do localhost ou erro no PHP
        document.getElementById('ul_dados').innerHTML =
            "<tr><td colspan='6'>Erro ao acessar o servidor. Abra pelo http://localhost/ com o Apache ligado.</td></tr>";
        console.error(erro);
    }
};

// DELETE
async function excluir(id) {
    let confirmar = confirm("Deseja realmente excluir este usuário?");

    if (!confirmar) { return; }

    const retorno = await fetch('../../php/JoaoVicente/perfil_excluir.php?id=' + id);
    const resposta = await retorno.json();

    alert(resposta.mensagem);

    if (resposta.status == 'ok') {
        document.getElementById("alterar").innerHTML = "";
        limparCampos();
        listar();
    }
};

// UPDATE - Fase 1: busca o registro pelo ID e preenche o formulário
async function carregar(id) {
    const retorno = await fetch('../../php/JoaoVicente/perfil_get.php?id=' + id);
    const resposta = await retorno.json();

    if (resposta.status == 'ok') {
        const reg = resposta.data[0];
        document.getElementById('name').value = reg.nome;
        document.getElementById('email').value = reg.email;
        document.getElementById('telefone').value = reg.telefone;
        document.getElementById('cidade').value = reg.cidade;
        document.getElementById('estado').value = reg.estado;
        document.getElementById("alterar").innerHTML = `<button class="btn_alterar" onclick="alterar(${reg.id_usuario})">Alterar</button>`;
    } else {
        alert('ERRO: ' + resposta.mensagem);
    }
};

// UPDATE - Fase 2: envia os dados novos (ID na URL + dados no body)
async function alterar(id) {
    const retorno = await fetch('../../php/JoaoVicente/perfil_alterar.php?id=' + id, {
        method: 'POST',
        body: montarFormData()
    });
    const resposta = await retorno.json();

    if (resposta.status == 'ok') {
        alert('SUCESSO: ' + resposta.mensagem);
        document.getElementById("alterar").innerHTML = "";
        limparCampos();
        listar();
    } else {
        alert('ERRO: ' + resposta.mensagem);
    }
};

listar();
