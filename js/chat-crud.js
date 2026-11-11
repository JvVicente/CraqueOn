const API_BASE = '../php/JoaoVicente';

const DOM_IDS = Object.freeze({
    messages: 'messages',
    messageForm: 'messageForm',
    messageInput: 'messageInput',
    editModal: 'editModal',
    editInput: 'editInput',
    headerName: 'headerName',
    headerStatus: 'headerStatus',
    contacts: 'contacts'
});

const SENDER_ME = 'eu';

let mensagemEditandoId = null;
let chatAtual = null;
let contatos = [];
let mensagensAtuais = [];

const utils = {
    escapeHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }
};

const chatService = {
    async loadContacts() {
        const resposta = await fetch(`${API_BASE}/chat_contatos.php`);
        const json = await resposta.json();

        if (json.status !== 'ok') {
            throw new Error(json.mensagem || 'Erro ao carregar contatos.');
        }

        contatos = Array.isArray(json.data) ? json.data : [];
    },

    getAll() {
        return contatos;
    },

    getById(chatId) {
        return contatos.find((chat) => String(chat.id) === String(chatId)) || null;
    },

    getContactsContainer() {
        return document.getElementById(DOM_IDS.contacts);
    },

    updateHeader(chatId) {
        const chat = this.getById(chatId);
        const headerName = document.getElementById(DOM_IDS.headerName);
        const headerStatus = document.getElementById(DOM_IDS.headerStatus);

        if (!chat) {
            if (headerName) headerName.textContent = '';
            if (headerStatus) headerStatus.textContent = '';
            return;
        }

        if (headerName) headerName.textContent = chat.name;
        if (headerStatus) headerStatus.textContent = chat.status;
    },

    createContactElement(chat) {
        const element = document.createElement('div');
        element.className = `contact ${String(chat.id) === String(chatAtual) ? 'active' : ''}`;
        element.dataset.chatId = chat.id;

        const name = document.createElement('div');
        name.className = 'contact-name';
        name.textContent = chat.name;

        const status = document.createElement('div');
        status.className = 'contact-status';
        status.textContent = chat.status;

        element.appendChild(name);
        element.appendChild(status);

        element.addEventListener('click', () => this.select(chat.id));

        return element;
    },

    renderContacts() {
        const container = this.getContactsContainer();
        if (!container) {
            return;
        }

        container.innerHTML = '';

        contatos.forEach((chat) => {
            container.appendChild(this.createContactElement(chat));
        });
    },

    updateActiveState(chatId) {
        const container = this.getContactsContainer();
        if (!container) {
            return;
        }

        Array.from(container.children).forEach((element) => {
            element.classList.toggle('active', String(element.dataset.chatId) === String(chatId));
        });
    },

    async select(chatId) {
        const chat = this.getById(chatId);

        if (!chat) {
            return;
        }

        chatAtual = String(chatId);
        localStorage.setItem('chatAtual', chatAtual);
        this.updateActiveState(chatAtual);
        this.updateHeader(chatAtual);

        await messageService.load(chatAtual);
        renderer.render(mensagensAtuais);
    }
};

const messageService = {
    findById(id) {
        return mensagensAtuais.find((mensagem) => String(mensagem.id) === String(id)) || null;
    },

    async load(chatId) {
        const resposta = await fetch(`${API_BASE}/chat_mensagens.php?contato_id=${encodeURIComponent(chatId)}`);
        const json = await resposta.json();

        if (json.status !== 'ok') {
            throw new Error(json.mensagem || 'Erro ao carregar mensagens.');
        }

        mensagensAtuais = Array.isArray(json.data) ? json.data : [];
    },

    async add(texto, chatId) {
        const resposta = await fetch(`${API_BASE}/chat_novo.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ contato_id: chatId, texto: texto.trim() })
        });
        const json = await resposta.json();
        return json.status === 'ok';
    },

    async update(id, novoTexto) {
        const resposta = await fetch(`${API_BASE}/chat_alterar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id, texto: novoTexto.trim() })
        });
        const json = await resposta.json();
        return json.status === 'ok';
    },

    async delete(id) {
        const resposta = await fetch(`${API_BASE}/chat_excluir.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id })
        });
        const json = await resposta.json();
        return json.status === 'ok';
    }
};

const renderer = {
    getMessagesContainer() {
        return document.getElementById(DOM_IDS.messages);
    },

    createActions(mensagem) {
        const actions = document.createElement('div');
        actions.className = 'message-actions';

        if (mensagem.pode_editar) {
            const editButton = document.createElement('button');
            editButton.textContent = 'Editar';
            editButton.addEventListener('click', () => editarMensagem(mensagem.id));
            actions.appendChild(editButton);
        }

        const deleteButton = document.createElement('button');
        deleteButton.textContent = 'Excluir';
        deleteButton.addEventListener('click', () => excluirMensagem(mensagem.id));
        actions.appendChild(deleteButton);

        return actions;
    },

    createMessageElement(mensagem) {
        const element = document.createElement('div');
        const isSent = mensagem.remetente === SENDER_ME;

        element.className = `message ${isSent ? 'sent' : 'received'}`;

        const text = document.createElement('div');
        text.className = 'message-text';
        text.textContent = mensagem.texto;
        element.appendChild(text);

        const info = document.createElement('div');
        info.className = 'message-info';

        const time = document.createElement('span');
        time.textContent = mensagem.horario;
        info.appendChild(time);

        element.appendChild(info);

        if (isSent) {
            element.appendChild(this.createActions(mensagem));
        }

        return element;
    },

    render(mensagens) {
        const container = this.getMessagesContainer();
        if (!container) {
            return;
        }

        container.innerHTML = '';

        mensagens.forEach((mensagem) => {
            container.appendChild(this.createMessageElement(mensagem));
        });

        container.scrollTop = container.scrollHeight;
    }
};

const modal = {
    getElement() {
        return document.getElementById(DOM_IDS.editModal);
    },

    getInput() {
        return document.getElementById(DOM_IDS.editInput);
    },

    open(texto) {
        const input = this.getInput();
        input.value = texto;
        this.getElement().classList.add('active');
        input.focus();
    },

    close() {
        mensagemEditandoId = null;
        this.getElement().classList.remove('active');
    }
};

async function editarMensagem(id) {
    const mensagem = messageService.findById(id);

    if (!mensagem) {
        return;
    }

    if (!mensagem.pode_editar) {
        alert('Nao e possivel editar mensagens enviadas ha mais de 15 minutos.');
        return;
    }

    mensagemEditandoId = id;
    modal.open(mensagem.texto);
}

async function salvarEdicao() {
    const input = modal.getInput();
    const novoTexto = input.value.trim();

    if (!novoTexto) {
        alert('Digite uma mensagem.');
        return;
    }

    if (!mensagemEditandoId) {
        return;
    }

    const sucesso = await messageService.update(mensagemEditandoId, novoTexto);

    if (!sucesso) {
        alert('Nao e possivel editar a mensagem. Pode estar fora do prazo de 15 minutos.');
    }

    modal.close();

    if (chatAtual) {
        await messageService.load(chatAtual);
        renderer.render(mensagensAtuais);
    }
}

async function excluirMensagem(id) {
    const confirmar = confirm('Deseja realmente excluir esta mensagem?');

    if (!confirmar) {
        return;
    }

    await messageService.delete(id);

    if (chatAtual) {
        await messageService.load(chatAtual);
        renderer.render(mensagensAtuais);
    }
}

function fecharModal() {
    modal.close();
}

function configurarEventos() {
    const form = document.getElementById(DOM_IDS.messageForm);
    const input = document.getElementById(DOM_IDS.messageInput);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const texto = input.value.trim();

        if (!texto || !chatAtual) {
            return;
        }

        await messageService.add(texto, chatAtual);

        input.value = '';
        input.focus();

        await messageService.load(chatAtual);
        renderer.render(mensagensAtuais);
    });

    modal.getInput().addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            salvarEdicao();
        }

        if (event.key === 'Escape') {
            fecharModal();
        }
    });
}

async function inicializar() {
    configurarEventos();

    try {
        await chatService.loadContacts();
        chatService.renderContacts();

        const salvo = localStorage.getItem('chatAtual');
        chatAtual = (salvo && chatService.getById(salvo)) ? salvo : (contatos[0] ? String(contatos[0].id) : null);

        if (chatAtual) {
            await chatService.select(chatAtual);
        }
    } catch (erro) {
        console.error('Erro ao inicializar chat:', erro);
        alert('Erro ao carregar o chat. Verifique o console.');
    }
}

inicializar();
