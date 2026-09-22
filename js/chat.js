const CONFIG = Object.freeze({
    STORAGE_KEY: "chatMensagens",
    CHATS_STORAGE_KEY: "chatContatos",
    ACTIVE_CHAT_KEY: "chatAtual",
    EDIT_TIME_LIMIT_MINUTES: 15,
    SENDER_ME: "eu",
    DEFAULT_CHAT_ID: "joao-silva"
});

const DOM_IDS = Object.freeze({
    messages: "messages",
    messageForm: "messageForm",
    messageInput: "messageInput",
    editModal: "editModal",
    editInput: "editInput",
    headerName: "headerName",
    headerStatus: "headerStatus",
    contacts: "contacts"
});

const DEFAULT_CHATS = Object.freeze([
    {
        id: "joao-silva",
        name: "João Silva",
        status: "Online"
    },
    {
        id: "maria-santos",
        name: "Maria Santos",
        status: "Online recentemente"
    }
]);

let mensagemEditandoId = null;
let chatAtual = CONFIG.DEFAULT_CHAT_ID;

const utils = {
    escapeHtml(texto) {
        const div = document.createElement("div");
        div.textContent = texto;
        return div.innerHTML;
    },

    formatTime(data) {
        return data.toLocaleTimeString("pt-BR", {
            hour: "2-digit",
            minute: "2-digit"
        });
    },

    generateId() {
        return Date.now().toString();
    },

    parseIsoDate(isoString) {
        const data = new Date(isoString);
        return isNaN(data.getTime()) ? null : data;
    },

    isNonEmptyString(valor) {
        return typeof valor === "string" && valor.trim().length > 0;
    },

    cloneProfundo(objeto) {
        return JSON.parse(JSON.stringify(objeto));
    }
};

const storage = {
    getItem(chave, padrao = null) {
        try {
            const dados = localStorage.getItem(chave);
            return dados || padrao;
        } catch (erro) {
            console.error(`Erro ao ler ${chave} do localStorage:`, erro);
            return padrao;
        }
    },

    setItem(chave, valor) {
        try {
            localStorage.setItem(chave, valor);
        } catch (erro) {
            console.error(`Erro ao salvar ${chave} no localStorage:`, erro);
        }
    },

    getMessages() {
        try {
            const dados = this.getItem(CONFIG.STORAGE_KEY);
            return dados ? JSON.parse(dados) : [];
        } catch (erro) {
            console.error("Erro ao parsear mensagens:", erro);
            return [];
        }
    },

    saveMessages(mensagens) {
        this.setItem(CONFIG.STORAGE_KEY, JSON.stringify(mensagens));
    },

    getChats() {
        try {
            const dados = this.getItem(CONFIG.CHATS_STORAGE_KEY);
            return dados ? JSON.parse(dados) : null;
        } catch (erro) {
            console.error("Erro ao parsear contatos:", erro);
            return null;
        }
    },

    saveChats(chats) {
        this.setItem(CONFIG.CHATS_STORAGE_KEY, JSON.stringify(chats));
    },

    getActiveChat() {
        return this.getItem(CONFIG.ACTIVE_CHAT_KEY, CONFIG.DEFAULT_CHAT_ID);
    },

    saveActiveChat(id) {
        this.setItem(CONFIG.ACTIVE_CHAT_KEY, id);
    }
};

const chatService = {
    initialize() {
        const chats = storage.getChats();

        if (!Array.isArray(chats) || chats.length === 0) {
            storage.saveChats(utils.cloneProfundo(DEFAULT_CHATS));
        }
    },

    getAll() {
        const chats = storage.getChats();
        return Array.isArray(chats) ? chats : utils.cloneProfundo(DEFAULT_CHATS);
    },

    getById(chatId) {
        return this.getAll().find((chat) => chat.id === chatId) || null;
    },

    getContactsContainer() {
        return document.getElementById(DOM_IDS.contacts);
    },

    updateHeader(chatId) {
        const chat = this.getById(chatId);

        if (!chat) {
            return;
        }

        const headerName = document.getElementById(DOM_IDS.headerName);
        const headerStatus = document.getElementById(DOM_IDS.headerStatus);

        if (headerName) {
            headerName.textContent = chat.name;
        }

        if (headerStatus) {
            headerStatus.textContent = chat.status;
        }
    },

    createContactElement(chat) {
        const element = document.createElement("div");
        element.className = `contact ${chat.id === chatAtual ? "active" : ""}`;
        element.dataset.chatId = chat.id;

        const name = document.createElement("div");
        name.className = "contact-name";
        name.textContent = chat.name;

        const status = document.createElement("div");
        status.className = "contact-status";
        status.textContent = chat.status;

        element.appendChild(name);
        element.appendChild(status);

        element.addEventListener("click", () => this.select(chat.id));

        return element;
    },

    renderContacts() {
        const container = this.getContactsContainer();
        const chats = this.getAll();

        container.innerHTML = "";

        chats.forEach((chat) => {
            container.appendChild(this.createContactElement(chat));
        });
    },

    updateActiveState(chatId) {
        const container = this.getContactsContainer();

        Array.from(container.children).forEach((element) => {
            element.classList.toggle(
                "active",
                element.dataset.chatId === chatId
            );
        });
    },

    select(chatId) {
        const chat = this.getById(chatId);

        if (!chat) {
            return;
        }

        chatAtual = chatId;
        storage.saveActiveChat(chatId);
        this.updateActiveState(chatId);
        this.updateHeader(chatId);
        renderer.render(chatId);
    }
};

const messageService = {
    findById(mensagens, id) {
        return mensagens.find((mensagem) => mensagem.id === id);
    },

    findIndexById(mensagens, id) {
        return mensagens.findIndex((mensagem) => mensagem.id === id);
    },

    canEdit(mensagem) {
        const dataEnvio = utils.parseIsoDate(mensagem.dataEnvio);

        if (!dataEnvio) {
            return false;
        }

        const diferencaEmMinutos =
            (Date.now() - dataEnvio.getTime()) / 60000;

        return diferencaEmMinutos <= CONFIG.EDIT_TIME_LIMIT_MINUTES;
    },

    build(texto, chatId) {
        const agora = new Date();

        return {
            id: utils.generateId(),
            chatId: chatId,
            texto: texto.trim(),
            remetente: CONFIG.SENDER_ME,
            horario: utils.formatTime(agora),
            dataEnvio: agora.toISOString()
        };
    },

    add(texto, chatId) {
        const mensagens = storage.getMessages();
        const novaMensagem = this.build(texto, chatId);

        mensagens.push(novaMensagem);
        storage.saveMessages(mensagens);
    },

    update(id, novoTexto) {
        const mensagens = storage.getMessages();
        const index = this.findIndexById(mensagens, id);

        if (index === -1) {
            return false;
        }

        if (!this.canEdit(mensagens[index])) {
            return false;
        }

        mensagens[index].texto = novoTexto.trim();
        storage.saveMessages(mensagens);
        return true;
    },

    delete(id) {
        const mensagens = storage.getMessages().filter(
            (mensagem) => mensagem.id !== id
        );

        storage.saveMessages(mensagens);
    },

    getByChat(chatId) {
        return storage.getMessages().filter(
            (mensagem) => mensagem.chatId === chatId
        );
    }
};

const renderer = {
    getMessagesContainer() {
        return document.getElementById(DOM_IDS.messages);
    },

    createActions(mensagem) {
        const actions = document.createElement("div");
        actions.className = "message-actions";

        if (messageService.canEdit(mensagem)) {
            const editButton = document.createElement("button");
            editButton.textContent = "Editar";
            editButton.addEventListener(
                "click",
                () => editarMensagem(mensagem.id)
            );
            actions.appendChild(editButton);
        }

        const deleteButton = document.createElement("button");
        deleteButton.textContent = "Excluir";
        deleteButton.addEventListener(
            "click",
            () => excluirMensagem(mensagem.id)
        );
        actions.appendChild(deleteButton);

        return actions;
    },

    createMessageElement(mensagem) {
        const element = document.createElement("div");
        const isSent = mensagem.remetente === CONFIG.SENDER_ME;

        element.className = `message ${isSent ? "sent" : "received"}`;

        const text = document.createElement("div");
        text.className = "message-text";
        text.textContent = mensagem.texto;
        element.appendChild(text);

        const info = document.createElement("div");
        info.className = "message-info";

        const time = document.createElement("span");
        time.textContent = mensagem.horario;
        info.appendChild(time);

        element.appendChild(info);

        if (isSent) {
            element.appendChild(this.createActions(mensagem));
        }

        return element;
    },

    render(chatId) {
        const container = this.getMessagesContainer();
        const mensagens = messageService.getByChat(chatId);

        container.innerHTML = "";

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
        this.getElement().classList.add("active");
        input.focus();
    },

    close() {
        mensagemEditandoId = null;
        this.getElement().classList.remove("active");
    }
};

function editarMensagem(id) {
    const mensagens = messageService.getByChat(chatAtual);
    const mensagem = messageService.findById(mensagens, id);

    if (!mensagem) {
        return;
    }

    if (!messageService.canEdit(mensagem)) {
        alert("Não é possível editar mensagens enviadas há mais de 15 minutos.");
        renderer.render(chatAtual);
        return;
    }

    mensagemEditandoId = id;
    modal.open(mensagem.texto);
}

function salvarEdicao() {
    const input = modal.getInput();
    const novoTexto = input.value.trim();

    if (!novoTexto) {
        alert("Digite uma mensagem.");
        return;
    }

    if (!mensagemEditandoId) {
        return;
    }

    if (!messageService.update(mensagemEditandoId, novoTexto)) {
        alert("Não é possível editar mensagens enviadas há mais de 15 minutos.");
    }

    modal.close();
    renderer.render(chatAtual);
}

function excluirMensagem(id) {
    const confirmar = confirm("Deseja realmente excluir esta mensagem?");

    if (!confirmar) {
        return;
    }

    messageService.delete(id);
    renderer.render(chatAtual);
}

function fecharModal() {
    modal.close();
}

function configurarEventos() {
    const form = document.getElementById(DOM_IDS.messageForm);
    const input = document.getElementById(DOM_IDS.messageInput);

    form.addEventListener("submit", (event) => {
        event.preventDefault();

        const texto = input.value.trim();

        if (!texto) {
            return;
        }

        messageService.add(texto, chatAtual);
        renderer.render(chatAtual);
        input.value = "";
        input.focus();
    });

    modal.getInput().addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            salvarEdicao();
        }

        if (event.key === "Escape") {
            fecharModal();
        }
    });
}

function inicializar() {
    chatService.initialize();
    chatAtual = storage.getActiveChat();
    configurarEventos();
    chatService.renderContacts();
    chatService.select(chatAtual);
}

inicializar();
