CREATE DATABASE IF NOT EXISTS craque_on CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE craque_on;
SET
  FOREIGN_KEY_CHECKS = 0;
DROP VIEW IF EXISTS vw_vagas_partida;
DROP VIEW IF EXISTS vw_agenda_partidas;
DROP VIEW IF EXISTS vw_ranking_atleta;
DROP TABLE IF EXISTS chamado_suporte;
DROP TABLE IF EXISTS notificacao;
DROP TABLE IF EXISTS mensagem;
DROP TABLE IF EXISTS avaliacao;
DROP TABLE IF EXISTS pagamento;
DROP TABLE IF EXISTS fila_espera;
DROP TABLE IF EXISTS participacao;
DROP TABLE IF EXISTS resultado_partida;
DROP TABLE IF EXISTS partida;
DROP TABLE IF EXISTS competicao_equipe;
DROP TABLE IF EXISTS competicao_modalidade;
DROP TABLE IF EXISTS competicao;
DROP TABLE IF EXISTS local_esportivo;
DROP TABLE IF EXISTS atleta_equipe;
DROP TABLE IF EXISTS equipe_modalidade;
DROP TABLE IF EXISTS equipe;
DROP TABLE IF EXISTS modalidade;
DROP TABLE IF EXISTS atleta;
DROP TABLE IF EXISTS login;
DROP TABLE IF EXISTS usuario_permissao;
DROP TABLE IF EXISTS permissao;
DROP TABLE IF EXISTS usuario;
SET
  FOREIGN_KEY_CHECKS = 1;
CREATE TABLE usuario (
  id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  telefone VARCHAR(20) NULL,
  cidade VARCHAR(80) NULL,
  estado CHAR(2) NULL,
  verificado TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
  data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_usuario_email UNIQUE (email),
  CONSTRAINT chk_usuario_email CHECK (email LIKE '%_@_%._%'),
  CONSTRAINT chk_usuario_uf CHECK (
    estado IS NULL
    OR estado REGEXP '^[A-Z]{2}$'
  )
) ENGINE = InnoDB;
CREATE INDEX ix_usuario_cidade ON usuario (estado, cidade);
CREATE TABLE permissao (
  id_permissao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(60) NOT NULL,
  descricao VARCHAR(200) NOT NULL,
  CONSTRAINT uq_permissao_nome UNIQUE (nome)
) ENGINE = InnoDB;
CREATE TABLE usuario_permissao (
  id_usuario INT UNSIGNED NOT NULL,
  id_permissao INT UNSIGNED NOT NULL,
  concedida_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_usuario, id_permissao),
  CONSTRAINT fk_up_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_up_permissao FOREIGN KEY (id_permissao) REFERENCES permissao (id_permissao) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;
CREATE INDEX ix_up_permissao ON usuario_permissao (id_permissao);
CREATE TABLE login (
  id_login INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NOT NULL,
  nome_usuario VARCHAR(60) NOT NULL,
  senha_hash VARCHAR(255) NOT NULL,
  ultimo_acesso DATETIME NULL,
  data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_login_usuario UNIQUE (id_usuario),
  CONSTRAINT uq_login_nome UNIQUE (nome_usuario),
  CONSTRAINT fk_login_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_login_nome CHECK (CHAR_LENGTH(nome_usuario) >= 4)
) ENGINE = InnoDB;
CREATE TABLE atleta (
  id_atleta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NULL,
  nome VARCHAR(100) NOT NULL,
  data_nascimento DATE NULL,
  genero ENUM('MASCULINO', 'FEMININO', 'OUTRO', 'NAO_INFORMADO') NOT NULL DEFAULT 'NAO_INFORMADO',
  posicao ENUM(
    'GOLEIRO',
    'DEFENSOR',
    'MEIA',
    'ATACANTE',
    'LIVRE',
    'OUTRO'
  ) NOT NULL DEFAULT 'LIVRE',
  cidade VARCHAR(80) NULL,
  estado CHAR(2) NULL,
  status ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
  CONSTRAINT uq_atleta_usuario UNIQUE (id_usuario),
  CONSTRAINT fk_atleta_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario) ON DELETE
  SET
    NULL ON UPDATE CASCADE,
    CONSTRAINT chk_atleta_uf CHECK (
      estado IS NULL
      OR estado REGEXP '^[A-Z]{2}$'
    ),
    CONSTRAINT chk_atleta_nasc CHECK (
      data_nascimento IS NULL
      OR data_nascimento >= '1900-01-01'
    )
) ENGINE = InnoDB;
CREATE INDEX ix_atleta_nome ON atleta (nome);
CREATE TABLE modalidade (
  id_modalidade INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  tipo ENUM('INDIVIDUAL', 'COLETIVA', 'AMBAS') NOT NULL,
  CONSTRAINT uq_modalidade_nome UNIQUE (nome)
) ENGINE = InnoDB;
CREATE TABLE equipe (
  id_equipe INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  cidade VARCHAR(80) NOT NULL,
  estado CHAR(2) NOT NULL,
  data_fundacao DATE NULL,
  status ENUM('ATIVA', 'INATIVA') NOT NULL DEFAULT 'ATIVA',
  CONSTRAINT uq_equipe UNIQUE (nome, cidade, estado),
  CONSTRAINT chk_equipe_uf CHECK (estado REGEXP '^[A-Z]{2}$')
) ENGINE = InnoDB;
CREATE INDEX ix_equipe_cidade ON equipe (estado, cidade);
CREATE TABLE equipe_modalidade (
  id_equipe INT UNSIGNED NOT NULL,
  id_modalidade INT UNSIGNED NOT NULL,
  nivel ENUM('INICIANTE', 'AMADOR', 'INTERMEDIARIO', 'AVANCADO') NOT NULL DEFAULT 'AMADOR',
  desde DATE NOT NULL,
  PRIMARY KEY (id_equipe, id_modalidade),
  CONSTRAINT fk_em_equipe FOREIGN KEY (id_equipe) REFERENCES equipe (id_equipe) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_em_modalidade FOREIGN KEY (id_modalidade) REFERENCES modalidade (id_modalidade) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;
CREATE INDEX ix_em_modalidade ON equipe_modalidade (id_modalidade);
CREATE TABLE atleta_equipe (
  id_atleta INT UNSIGNED NOT NULL,
  id_equipe INT UNSIGNED NOT NULL,
  data_entrada DATE NOT NULL,
  data_saida DATE NULL,
  funcao VARCHAR(60) NOT NULL,
  numero_camisa SMALLINT UNSIGNED NULL,
  PRIMARY KEY (id_atleta, id_equipe, data_entrada),
  CONSTRAINT fk_ae_atleta FOREIGN KEY (id_atleta) REFERENCES atleta (id_atleta) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_ae_equipe FOREIGN KEY (id_equipe) REFERENCES equipe (id_equipe) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_ae_datas CHECK (
    data_saida IS NULL
    OR data_saida >= data_entrada
  ),
  CONSTRAINT chk_ae_camisa CHECK (
    numero_camisa IS NULL
    OR numero_camisa BETWEEN 1
    AND 99
  )
) ENGINE = InnoDB;
CREATE INDEX ix_ae_equipe ON atleta_equipe (id_equipe);
CREATE TABLE local_esportivo (
  id_local INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  cidade VARCHAR(80) NOT NULL,
  estado CHAR(2) NOT NULL,
  endereco VARCHAR(180) NULL,
  capacidade INT UNSIGNED NULL,
  CONSTRAINT uq_local UNIQUE (nome, cidade, estado),
  CONSTRAINT chk_local_uf CHECK (estado REGEXP '^[A-Z]{2}$')
) ENGINE = InnoDB;
CREATE INDEX ix_local_cidade ON local_esportivo (estado, cidade);
CREATE TABLE competicao (
  id_competicao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  formato ENUM(
    'LIGA',
    'ELIMINATORIA',
    'GRUPOS',
    'AMISTOSO',
    'OUTRO'
  ) NOT NULL,
  data_inicio DATE NOT NULL,
  data_fim DATE NULL,
  status ENUM(
    'PLANEJADA',
    'EM_ANDAMENTO',
    'ENCERRADA',
    'CANCELADA'
  ) NOT NULL DEFAULT 'PLANEJADA',
  CONSTRAINT uq_competicao UNIQUE (nome, data_inicio),
  CONSTRAINT chk_competicao_datas CHECK (
    data_fim IS NULL
    OR data_fim >= data_inicio
  )
) ENGINE = InnoDB;
CREATE TABLE competicao_modalidade (
  id_competicao INT UNSIGNED NOT NULL,
  id_modalidade INT UNSIGNED NOT NULL,
  categoria VARCHAR(60) NOT NULL,
  regulamento_resumo VARCHAR(250) NULL,
  PRIMARY KEY (id_competicao, id_modalidade, categoria),
  CONSTRAINT fk_cm_competicao FOREIGN KEY (id_competicao) REFERENCES competicao (id_competicao) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cm_modalidade FOREIGN KEY (id_modalidade) REFERENCES modalidade (id_modalidade) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;
CREATE INDEX ix_cm_modalidade ON competicao_modalidade (id_modalidade);
CREATE TABLE competicao_equipe (
  id_competicao INT UNSIGNED NOT NULL,
  id_equipe INT UNSIGNED NOT NULL,
  data_inscricao DATE NOT NULL,
  situacao ENUM(
    'INSCRITA',
    'CONFIRMADA',
    'DESCLASSIFICADA',
    'CAMPEA'
  ) NOT NULL DEFAULT 'INSCRITA',
  PRIMARY KEY (id_competicao, id_equipe),
  CONSTRAINT fk_ce_competicao FOREIGN KEY (id_competicao) REFERENCES competicao (id_competicao) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_ce_equipe FOREIGN KEY (id_equipe) REFERENCES equipe (id_equipe) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;
CREATE INDEX ix_ce_equipe ON competicao_equipe (id_equipe);
CREATE TABLE partida (
  id_partida INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('ABERTA', 'COMPETICAO') NOT NULL DEFAULT 'ABERTA',
  id_modalidade INT UNSIGNED NOT NULL,
  id_organizador INT UNSIGNED NOT NULL,
  id_local INT UNSIGNED NULL,
  id_competicao INT UNSIGNED NULL,
  id_equipe_mandante INT UNSIGNED NULL,
  id_equipe_visitante INT UNSIGNED NULL,
  titulo VARCHAR(120) NULL,
  data_hora DATETIME NOT NULL,
  vagas_total SMALLINT UNSIGNED NULL,
  valor_inscricao DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  status ENUM(
    'AGENDADA',
    'EM_ANDAMENTO',
    'FINALIZADA',
    'CANCELADA'
  ) NOT NULL DEFAULT 'AGENDADA',
  CONSTRAINT fk_partida_modalidade FOREIGN KEY (id_modalidade) REFERENCES modalidade (id_modalidade) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_partida_organizador FOREIGN KEY (id_organizador) REFERENCES usuario (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_partida_local FOREIGN KEY (id_local) REFERENCES local_esportivo (id_local) ON DELETE
  SET
    NULL ON UPDATE CASCADE,
    CONSTRAINT fk_partida_competicao FOREIGN KEY (id_competicao) REFERENCES competicao (id_competicao) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_partida_mandante FOREIGN KEY (id_equipe_mandante) REFERENCES equipe (id_equipe) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_partida_visitante FOREIGN KEY (id_equipe_visitante) REFERENCES equipe (id_equipe) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_partida_equipes CHECK (
      id_equipe_mandante IS NULL
      OR id_equipe_visitante IS NULL
      OR id_equipe_mandante <> id_equipe_visitante
    ),
    CONSTRAINT chk_partida_tipo CHECK (
      (
        tipo = 'COMPETICAO'
        AND id_competicao IS NOT NULL
        AND id_equipe_mandante IS NOT NULL
        AND id_equipe_visitante IS NOT NULL
      )
      OR (
        tipo = 'ABERTA'
        AND id_equipe_mandante IS NULL
        AND id_equipe_visitante IS NULL
        AND vagas_total IS NOT NULL
        AND vagas_total > 0
      )
    ),
    CONSTRAINT chk_partida_valor CHECK (valor_inscricao >= 0)
) ENGINE = InnoDB;
CREATE INDEX ix_partida_agenda ON partida (data_hora, status);
CREATE INDEX ix_partida_busca ON partida (id_modalidade, status, data_hora);
CREATE INDEX ix_partida_local ON partida (id_local);
CREATE INDEX ix_partida_competicao ON partida (id_competicao);
CREATE INDEX ix_partida_organizador ON partida (id_organizador);
CREATE INDEX ix_partida_mandante ON partida (id_equipe_mandante);
CREATE INDEX ix_partida_visitante ON partida (id_equipe_visitante);
CREATE TABLE resultado_partida (
  id_resultado INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_partida INT UNSIGNED NOT NULL,
  pontuacao_mandante SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  pontuacao_visitante SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  observacao VARCHAR(250) NULL,
  CONSTRAINT uq_resultado_partida UNIQUE (id_partida),
  CONSTRAINT fk_resultado_partida FOREIGN KEY (id_partida) REFERENCES partida (id_partida) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;
CREATE TABLE participacao (
  id_participacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_partida INT UNSIGNED NOT NULL,
  id_atleta INT UNSIGNED NOT NULL,
  situacao ENUM(
    'PENDENTE_PAGAMENTO',
    'CONFIRMADA',
    'CANCELADA',
    'PRESENTE',
    'FALTOU'
  ) NOT NULL DEFAULT 'PENDENTE_PAGAMENTO',
  data_inscricao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_participacao UNIQUE (id_partida, id_atleta),
  CONSTRAINT fk_part_partida FOREIGN KEY (id_partida) REFERENCES partida (id_partida) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_part_atleta FOREIGN KEY (id_atleta) REFERENCES atleta (id_atleta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;
CREATE INDEX ix_participacao_atleta ON participacao (id_atleta, situacao);
CREATE TABLE fila_espera (
  id_fila INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_partida INT UNSIGNED NOT NULL,
  id_atleta INT UNSIGNED NOT NULL,
  posicao SMALLINT UNSIGNED NOT NULL,
  status ENUM('AGUARDANDO', 'PROMOVIDO', 'DESISTIU') NOT NULL DEFAULT 'AGUARDANDO',
  entrou_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_fila_atleta UNIQUE (id_partida, id_atleta),
  CONSTRAINT uq_fila_posicao UNIQUE (id_partida, posicao),
  CONSTRAINT fk_fila_partida FOREIGN KEY (id_partida) REFERENCES partida (id_partida) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_fila_atleta FOREIGN KEY (id_atleta) REFERENCES atleta (id_atleta) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_fila_posicao CHECK (posicao > 0)
) ENGINE = InnoDB;
CREATE INDEX ix_fila_atleta ON fila_espera (id_atleta);
CREATE TABLE pagamento (
  id_pagamento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_participacao INT UNSIGNED NOT NULL,
  valor DECIMAL(10, 2) NOT NULL,
  metodo ENUM('PIX', 'CARTAO', 'DINHEIRO') NOT NULL,
  status ENUM('PENDENTE', 'APROVADO', 'RECUSADO', 'ESTORNADO') NOT NULL DEFAULT 'PENDENTE',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  confirmado_em DATETIME NULL,
  CONSTRAINT fk_pagamento_participacao FOREIGN KEY (id_participacao) REFERENCES participacao (id_participacao) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_pagamento_valor CHECK (valor >= 0),
  CONSTRAINT chk_pagamento_confirm CHECK (
    status <> 'APROVADO'
    OR confirmado_em IS NOT NULL
  )
) ENGINE = InnoDB;
CREATE INDEX ix_pagamento_participacao ON pagamento (id_participacao, status);
CREATE TABLE avaliacao (
  id_avaliacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_partida INT UNSIGNED NOT NULL,
  id_atleta_avaliador INT UNSIGNED NOT NULL,
  id_atleta_avaliado INT UNSIGNED NULL,
  nota TINYINT UNSIGNED NOT NULL,
  comentario VARCHAR(300) NULL,
  criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  avaliado_key INT UNSIGNED AS (IFNULL(id_atleta_avaliado, 0)) STORED,
  CONSTRAINT uq_avaliacao UNIQUE (id_partida, id_atleta_avaliador, avaliado_key),
  CONSTRAINT fk_av_partida FOREIGN KEY (id_partida) REFERENCES partida (id_partida) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_av_avaliador FOREIGN KEY (id_atleta_avaliador) REFERENCES atleta (id_atleta) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_av_avaliado FOREIGN KEY (id_atleta_avaliado) REFERENCES atleta (id_atleta) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_av_nota CHECK (
    nota BETWEEN 1
    AND 5
  ),
  CONSTRAINT chk_av_self CHECK (
    id_atleta_avaliado IS NULL
    OR id_atleta_avaliado <> id_atleta_avaliador
  )
) ENGINE = InnoDB;
CREATE INDEX ix_av_avaliado ON avaliacao (id_atleta_avaliado);
CREATE INDEX ix_av_avaliador ON avaliacao (id_atleta_avaliador, criada_em);
CREATE TABLE mensagem (
  id_mensagem BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_remetente INT UNSIGNED NOT NULL,
  id_destinatario INT UNSIGNED NULL,
  id_partida INT UNSIGNED NULL,
  texto VARCHAR(1000) NOT NULL,
  enviada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  editada_em DATETIME NULL,
  lida TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_msg_remetente FOREIGN KEY (id_remetente) REFERENCES usuario (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_msg_destinatario FOREIGN KEY (id_destinatario) REFERENCES usuario (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_msg_partida FOREIGN KEY (id_partida) REFERENCES partida (id_partida) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_msg_texto CHECK (CHAR_LENGTH(TRIM(texto)) > 0),
  CONSTRAINT chk_msg_destino CHECK (
    (
      id_destinatario IS NOT NULL
      AND id_partida IS NULL
    )
    OR (
      id_destinatario IS NULL
      AND id_partida IS NOT NULL
    )
  ),
  CONSTRAINT chk_msg_self CHECK (
    id_destinatario IS NULL
    OR id_destinatario <> id_remetente
  ),
  CONSTRAINT chk_msg_edicao CHECK (
    editada_em IS NULL
    OR editada_em >= enviada_em
  )
) ENGINE = InnoDB;
CREATE INDEX ix_msg_enviadas ON mensagem (id_remetente, id_destinatario, enviada_em);
CREATE INDEX ix_msg_recebidas ON mensagem (id_destinatario, id_remetente, enviada_em);
CREATE INDEX ix_msg_partida ON mensagem (id_partida, enviada_em);
CREATE TABLE notificacao (
  id_notificacao BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NOT NULL,
  id_partida INT UNSIGNED NULL,
  tipo ENUM(
    'CONFIRMACAO',
    'ALTERACAO',
    'CANCELAMENTO',
    'VAGA_DISPONIVEL',
    'PAGAMENTO',
    'OUTRO'
  ) NOT NULL,
  mensagem VARCHAR(300) NOT NULL,
  lida TINYINT(1) NOT NULL DEFAULT 0,
  criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notif_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_notif_partida FOREIGN KEY (id_partida) REFERENCES partida (id_partida) ON DELETE
  SET
    NULL ON UPDATE CASCADE
) ENGINE = InnoDB;
CREATE INDEX ix_notif_caixa ON notificacao (id_usuario, lida, criada_em);
CREATE INDEX ix_notif_partida ON notificacao (id_partida);
CREATE TABLE chamado_suporte (
  id_chamado INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NOT NULL,
  id_partida INT UNSIGNED NULL,
  categoria ENUM('SISTEMA', 'PARTIDA', 'PAGAMENTO', 'CONTA') NOT NULL,
  assunto VARCHAR(120) NOT NULL,
  descricao VARCHAR(1000) NOT NULL,
  resposta VARCHAR(1000) NULL,
  status ENUM('ABERTO', 'EM_ANALISE', 'RESOLVIDO') NOT NULL DEFAULT 'ABERTO',
  aberto_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fechado_em DATETIME NULL,
  CONSTRAINT fk_cs_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cs_partida FOREIGN KEY (id_partida) REFERENCES partida (id_partida) ON DELETE
  SET
    NULL ON UPDATE CASCADE,
    CONSTRAINT chk_cs_fechamento CHECK (
      (
        status = 'RESOLVIDO'
        AND fechado_em IS NOT NULL
      )
      OR (
        status <> 'RESOLVIDO'
        AND fechado_em IS NULL
      )
    )
) ENGINE = InnoDB;
CREATE INDEX ix_cs_usuario ON chamado_suporte (id_usuario, status);
CREATE INDEX ix_cs_partida ON chamado_suporte (id_partida);
DELIMITER $ $ CREATE TRIGGER trg_participacao_bi BEFORE
INSERT
  ON participacao FOR EACH ROW BEGIN DECLARE v_tipo VARCHAR(20);
DECLARE v_status VARCHAR(20);
DECLARE v_vagas INT;
DECLARE v_ocupadas INT;
SELECT
  tipo,
  status,
  IFNULL(vagas_total, 0) INTO v_tipo,
  v_status,
  v_vagas
FROM
  partida
WHERE
  id_partida = NEW.id_partida;
IF v_tipo <> 'ABERTA' THEN SIGNAL SQLSTATE '45000'
SET
  MESSAGE_TEXT = 'Inscricao individual permitida apenas em partidas do tipo ABERTA.';
END IF;
IF v_status <> 'AGENDADA' THEN SIGNAL SQLSTATE '45000'
SET
  MESSAGE_TEXT = 'Somente partidas AGENDADAS aceitam novas inscricoes.';
END IF;
SELECT
  COUNT(*) INTO v_ocupadas
FROM
  participacao
WHERE
  id_partida = NEW.id_partida
  AND situacao IN ('PENDENTE_PAGAMENTO', 'CONFIRMADA', 'PRESENTE');
IF v_ocupadas >= v_vagas THEN SIGNAL SQLSTATE '45000'
SET
  MESSAGE_TEXT = 'Partida lotada: utilize a fila de espera.';
END IF;
END $ $ DELIMITER;
CREATE VIEW vw_vagas_partida AS
SELECT
  p.id_partida,
  p.titulo,
  p.data_hora,
  p.vagas_total,
  COUNT(pa.id_participacao) AS vagas_ocupadas,
  p.vagas_total - COUNT(pa.id_participacao) AS vagas_disponiveis,
  (
    SELECT
      COUNT(*)
    FROM
      fila_espera f
    WHERE
      f.id_partida = p.id_partida
      AND f.status = 'AGUARDANDO'
  ) AS na_fila
FROM
  partida p
  LEFT JOIN participacao pa ON pa.id_partida = p.id_partida
  AND pa.situacao IN ('PENDENTE_PAGAMENTO', 'CONFIRMADA', 'PRESENTE')
WHERE
  p.tipo = 'ABERTA'
GROUP BY
  p.id_partida,
  p.titulo,
  p.data_hora,
  p.vagas_total;
CREATE VIEW vw_agenda_partidas AS
SELECT
  p.id_partida,
  p.tipo,
  COALESCE(p.titulo, c.nome) AS descricao,
  m.nome AS modalidade,
  l.nome AS local,
  l.cidade,
  l.estado,
  p.data_hora,
  p.valor_inscricao,
  p.status,
  u.nome AS organizador
FROM
  partida p
  JOIN modalidade m ON m.id_modalidade = p.id_modalidade
  JOIN usuario u ON u.id_usuario = p.id_organizador
  LEFT JOIN local_esportivo l ON l.id_local = p.id_local
  LEFT JOIN competicao c ON c.id_competicao = p.id_competicao;
CREATE VIEW vw_ranking_atleta AS
SELECT
  a.id_atleta,
  a.nome AS atleta,
  COUNT(
    DISTINCT CASE
      WHEN pa.situacao IN ('CONFIRMADA', 'PRESENTE') THEN pa.id_partida
    END
  ) AS partidas,
  ROUND(AVG(av.nota), 2) AS media_avaliacao,
  COUNT(av.id_avaliacao) AS total_avaliacoes
FROM
  atleta a
  LEFT JOIN participacao pa ON pa.id_atleta = a.id_atleta
  LEFT JOIN avaliacao av ON av.id_atleta_avaliado = a.id_atleta
WHERE
  a.status = 'ATIVO'
GROUP BY
  a.id_atleta,
  a.nome;
INSERT INTO
  usuario (
    id_usuario,
    nome,
    email,
    telefone,
    cidade,
    estado,
    verificado,
    data_cadastro
  )
VALUES
  (
    1,
    'Ana Souza',
    'ana@craqueon.test',
    '41990000001',
    'Curitiba',
    'PR',
    1,
    '2026-09-01 09:00:00'
  ),
  (
    2,
    'Bruno Lima',
    'bruno@craqueon.test',
    '41990000002',
    'São José dos Pinhais',
    'PR',
    1,
    '2026-09-02 10:00:00'
  ),
  (
    3,
    'Camila Rocha',
    'camila@craqueon.test',
    '41990000003',
    'Curitiba',
    'PR',
    0,
    '2026-09-03 11:00:00'
  ),
  (
    4,
    'Diego Alves',
    'diego@craqueon.test',
    '41990000004',
    'Curitiba',
    'PR',
    0,
    '2026-09-04 12:00:00'
  ),
  (
    5,
    'Eduarda Costa',
    'eduarda@craqueon.test',
    '41990000005',
    'Colombo',
    'PR',
    0,
    '2026-09-05 13:00:00'
  ),
  (
    6,
    'Felipe Martins',
    'felipe@craqueon.test',
    '41990000006',
    'Pinhais',
    'PR',
    1,
    '2026-09-06 08:30:00'
  );
INSERT INTO
  permissao (id_permissao, nome, descricao)
VALUES
  (1, 'ADMIN', 'Administra cadastros e suporte'),
  (
    2,
    'ORGANIZADOR',
    'Cria e gerencia partidas e competições'
  ),
  (3, 'ATLETA', 'Participa de partidas e avalia'),
  (4, 'TECNICO', 'Gerencia informações da equipe'),
  (5, 'VISUALIZADOR', 'Consulta informações públicas');
INSERT INTO
  usuario_permissao (id_usuario, id_permissao, concedida_em)
VALUES
  (1, 1, '2026-09-01 09:10:00'),
  (1, 2, '2026-09-01 09:11:00'),
  (2, 3, '2026-09-02 10:10:00'),
  (3, 3, '2026-09-03 11:10:00'),
  (3, 4, '2026-09-03 11:11:00'),
  (4, 3, '2026-09-04 12:10:00'),
  (5, 3, '2026-09-05 13:10:00'),
  (6, 2, '2026-09-06 08:40:00');
INSERT INTO
  login (
    id_login,
    id_usuario,
    nome_usuario,
    senha_hash,
    data_criacao,
    ultimo_acesso
  )
VALUES
  (
    1,
    1,
    'ana.souza',
    '$2y$10$hashFicticioDemonstracao000000000000000000000001',
    '2026-09-01 09:00:00',
    '2026-10-30 20:10:00'
  ),
  (
    2,
    2,
    'bruno.lima',
    '$2y$10$hashFicticioDemonstracao000000000000000000000002',
    '2026-09-02 10:00:00',
    '2026-10-31 18:00:00'
  ),
  (
    3,
    3,
    'camila.rocha',
    '$2y$10$hashFicticioDemonstracao000000000000000000000003',
    '2026-09-03 11:00:00',
    NULL
  ),
  (
    4,
    4,
    'diego.alves',
    '$2y$10$hashFicticioDemonstracao000000000000000000000004',
    '2026-09-04 12:00:00',
    NULL
  ),
  (
    5,
    5,
    'eduarda.costa',
    '$2y$10$hashFicticioDemonstracao000000000000000000000005',
    '2026-09-05 13:00:00',
    NULL
  ),
  (
    6,
    6,
    'felipe.martins',
    '$2y$10$hashFicticioDemonstracao000000000000000000000006',
    '2026-09-06 08:30:00',
    NULL
  );
INSERT INTO
  atleta (
    id_atleta,
    id_usuario,
    nome,
    data_nascimento,
    genero,
    posicao,
    cidade,
    estado
  )
VALUES
  (
    1,
    2,
    'Bruno Lima',
    '2004-04-12',
    'MASCULINO',
    'ATACANTE',
    'São José dos Pinhais',
    'PR'
  ),
  (
    2,
    3,
    'Camila Rocha',
    '2003-08-23',
    'FEMININO',
    'MEIA',
    'Curitiba',
    'PR'
  ),
  (
    3,
    4,
    'Diego Alves',
    '2002-02-14',
    'MASCULINO',
    'GOLEIRO',
    'Curitiba',
    'PR'
  ),
  (
    4,
    5,
    'Eduarda Costa',
    '2005-12-01',
    'FEMININO',
    'DEFENSOR',
    'Colombo',
    'PR'
  ),
  (
    5,
    1,
    'Ana Souza',
    '2001-05-19',
    'FEMININO',
    'LIVRE',
    'Curitiba',
    'PR'
  ),
  (
    6,
    6,
    'Felipe Martins',
    '1998-07-30',
    'MASCULINO',
    'ATACANTE',
    'Pinhais',
    'PR'
  ),
  (
    7,
    NULL,
    'Hugo Pereira',
    '2000-09-09',
    'MASCULINO',
    'MEIA',
    'Curitiba',
    'PR'
  );
INSERT INTO
  modalidade (id_modalidade, nome, tipo)
VALUES
  (1, 'Futebol society', 'COLETIVA'),
  (2, 'Basquete', 'COLETIVA'),
  (3, 'Vôlei', 'COLETIVA'),
  (4, 'Tênis', 'AMBAS'),
  (5, 'Corrida de rua', 'INDIVIDUAL');
INSERT INTO
  equipe (id_equipe, nome, cidade, estado, data_fundacao)
VALUES
  (
    1,
    'Aurora Esporte Clube',
    'Curitiba',
    'PR',
    '2010-03-15'
  ),
  (
    2,
    'União Atlética',
    'São José dos Pinhais',
    'PR',
    '2012-06-20'
  ),
  (3, 'Fênix Sports', 'Curitiba', 'PR', '2015-01-10'),
  (4, 'Estrela do Sul', 'Pinhais', 'PR', '2018-08-05'),
  (5, 'Lobos do Paraná', 'Colombo', 'PR', '2011-11-11');
INSERT INTO
  equipe_modalidade (id_equipe, id_modalidade, nivel, desde)
VALUES
  (1, 1, 'AMADOR', '2020-01-01'),
  (1, 2, 'INICIANTE', '2024-02-01'),
  (2, 2, 'INTERMEDIARIO', '2021-01-01'),
  (3, 1, 'AMADOR', '2022-01-01'),
  (4, 3, 'INTERMEDIARIO', '2023-01-01'),
  (5, 5, 'AMADOR', '2022-05-01');
INSERT INTO
  atleta_equipe (
    id_atleta,
    id_equipe,
    data_entrada,
    data_saida,
    funcao,
    numero_camisa
  )
VALUES
  (1, 1, '2026-01-10', NULL, 'Atacante', 9),
  (1, 3, '2025-01-01', '2025-12-20', 'Atacante', 11),
  (2, 2, '2026-02-01', NULL, 'Armadora', 7),
  (3, 3, '2026-02-15', NULL, 'Goleiro', 1),
  (4, 4, '2026-03-01', NULL, 'Levantadora', 4),
  (5, 5, '2026-03-10', NULL, 'Corredora', NULL),
  (6, 4, '2026-04-01', NULL, 'Atacante', 10),
  (7, 1, '2026-05-02', NULL, 'Meia', 8);
INSERT INTO
  local_esportivo (
    id_local,
    nome,
    cidade,
    estado,
    endereco,
    capacidade
  )
VALUES
  (
    1,
    'Arena Aurora',
    'Curitiba',
    'PR',
    'Rua das Araucárias, 100',
    5000
  ),
  (
    2,
    'Ginásio União',
    'São José dos Pinhais',
    'PR',
    'Av. Esportiva, 250',
    1800
  ),
  (
    3,
    'Centro Fênix',
    'Curitiba',
    'PR',
    'Rua do Esporte, 80',
    1200
  ),
  (
    4,
    'Quadra Estrela',
    'Pinhais',
    'PR',
    'Rua das Flores, 45',
    600
  ),
  (
    5,
    'Parque dos Atletas',
    'Colombo',
    'PR',
    'Av. Verde, 900',
    3000
  );
INSERT INTO
  competicao (
    id_competicao,
    nome,
    formato,
    data_inicio,
    data_fim,
    status
  )
VALUES
  (
    1,
    'Copa Craque On de Futebol',
    'GRUPOS',
    '2026-11-01',
    '2026-11-30',
    'EM_ANDAMENTO'
  ),
  (
    2,
    'Liga Metropolitana de Basquete',
    'LIGA',
    '2026-11-05',
    '2026-12-10',
    'PLANEJADA'
  ),
  (
    3,
    'Festival Paranaense de Vôlei',
    'ELIMINATORIA',
    '2026-11-12',
    '2026-11-25',
    'PLANEJADA'
  ),
  (
    4,
    'Open Craque On de Tênis',
    'ELIMINATORIA',
    '2026-11-15',
    '2026-11-22',
    'PLANEJADA'
  ),
  (
    5,
    'Circuito Craque On de Corrida',
    'OUTRO',
    '2026-12-01',
    '2026-12-01',
    'PLANEJADA'
  );
INSERT INTO
  competicao_modalidade (
    id_competicao,
    id_modalidade,
    categoria,
    regulamento_resumo
  )
VALUES
  (
    1,
    1,
    'Adulto misto',
    'Fase de grupos e semifinal/final'
  ),
  (2, 2, 'Adulto', 'Turno e returno'),
  (
    3,
    3,
    'Sub-21',
    'Eliminatória em melhor de três sets'
  ),
  (4, 4, 'Aberto', 'Chave eliminatória'),
  (5, 5, 'Geral', 'Percurso de 5 km');
INSERT INTO
  competicao_equipe (
    id_competicao,
    id_equipe,
    data_inscricao,
    situacao
  )
VALUES
  (1, 1, '2026-10-01', 'CONFIRMADA'),
  (1, 3, '2026-10-02', 'CONFIRMADA'),
  (2, 2, '2026-10-03', 'CONFIRMADA'),
  (2, 4, '2026-10-04', 'INSCRITA'),
  (3, 4, '2026-10-05', 'CONFIRMADA'),
  (3, 5, '2026-10-06', 'INSCRITA'),
  (4, 1, '2026-10-07', 'INSCRITA'),
  (5, 5, '2026-10-08', 'CONFIRMADA');
INSERT INTO
  partida (
    id_partida,
    tipo,
    id_modalidade,
    id_organizador,
    id_local,
    id_competicao,
    id_equipe_mandante,
    id_equipe_visitante,
    titulo,
    data_hora,
    status
  )
VALUES
  (
    1,
    'COMPETICAO',
    1,
    1,
    1,
    1,
    1,
    3,
    'Rodada 1 — AEC x FEN',
    '2026-11-02 15:00:00',
    'FINALIZADA'
  ),
  (
    2,
    'COMPETICAO',
    2,
    4,
    2,
    2,
    2,
    4,
    'Rodada 1 — UAT x EDS',
    '2026-11-06 19:30:00',
    'AGENDADA'
  ),
  (
    3,
    'COMPETICAO',
    3,
    6,
    3,
    3,
    4,
    5,
    'Quartas — EDS x LOB',
    '2026-11-13 16:00:00',
    'AGENDADA'
  ),
  (
    4,
    'COMPETICAO',
    1,
    1,
    1,
    1,
    3,
    1,
    'Rodada 2 — FEN x AEC',
    '2026-11-09 15:00:00',
    'AGENDADA'
  );
INSERT INTO
  partida (
    id_partida,
    tipo,
    id_modalidade,
    id_organizador,
    id_local,
    titulo,
    data_hora,
    vagas_total,
    valor_inscricao,
    status
  )
VALUES
  (
    5,
    'ABERTA',
    1,
    1,
    1,
    'Fut society de quinta',
    '2026-10-22 20:00:00',
    4,
    25.00,
    'AGENDADA'
  ),
  (
    6,
    'ABERTA',
    1,
    6,
    4,
    'Pelada de sábado',
    '2026-11-08 09:00:00',
    4,
    20.00,
    'AGENDADA'
  ),
  (
    7,
    'ABERTA',
    3,
    4,
    3,
    'Vôlei misto iniciantes',
    '2026-11-10 19:00:00',
    6,
    15.00,
    'AGENDADA'
  ),
  (
    8,
    'ABERTA',
    2,
    6,
    2,
    'Basquete 5x5 livre',
    '2026-11-12 21:00:00',
    10,
    0.00,
    'AGENDADA'
  );
INSERT INTO
  resultado_partida (
    id_partida,
    pontuacao_mandante,
    pontuacao_visitante,
    observacao
  )
VALUES
  (1, 2, 1, 'Resultado demonstrativo'),
  (
    5,
    6,
    4,
    'Partida aberta: placar entre times sorteados'
  );
INSERT INTO
  participacao (
    id_participacao,
    id_partida,
    id_atleta,
    situacao,
    data_inscricao
  )
VALUES
  (1, 5, 1, 'CONFIRMADA', '2026-10-18 10:00:00'),
  (2, 5, 2, 'CONFIRMADA', '2026-10-18 10:05:00'),
  (3, 5, 3, 'CONFIRMADA', '2026-10-18 10:10:00'),
  (4, 5, 6, 'CONFIRMADA', '2026-10-20 09:00:00'),
  (5, 6, 1, 'CONFIRMADA', '2026-11-01 08:00:00'),
  (6, 6, 3, 'CONFIRMADA', '2026-11-01 08:10:00'),
  (7, 6, 6, 'PENDENTE_PAGAMENTO', '2026-11-01 08:20:00'),
  (8, 6, 7, 'PENDENTE_PAGAMENTO', '2026-11-01 08:30:00'),
  (9, 7, 2, 'CONFIRMADA', '2026-11-02 12:00:00'),
  (10, 7, 4, 'CONFIRMADA', '2026-11-02 12:05:00'),
  (11, 8, 5, 'CONFIRMADA', '2026-11-02 13:00:00');
INSERT INTO
  fila_espera (
    id_partida,
    id_atleta,
    posicao,
    status,
    entrou_em
  )
VALUES
  (6, 2, 1, 'AGUARDANDO', '2026-11-01 09:00:00'),
  (6, 4, 2, 'AGUARDANDO', '2026-11-01 09:05:00'),
  (5, 6, 1, 'PROMOVIDO', '2026-10-19 21:00:00'),
  (7, 1, 1, 'AGUARDANDO', '2026-11-02 12:30:00');
INSERT INTO
  pagamento (
    id_pagamento,
    id_participacao,
    valor,
    metodo,
    status,
    criado_em,
    confirmado_em
  )
VALUES
  (
    1,
    1,
    25.00,
    'PIX',
    'APROVADO',
    '2026-10-18 10:01:00',
    '2026-10-18 10:02:00'
  ),
  (
    2,
    2,
    25.00,
    'CARTAO',
    'APROVADO',
    '2026-10-18 10:06:00',
    '2026-10-18 10:07:00'
  ),
  (
    3,
    3,
    25.00,
    'PIX',
    'APROVADO',
    '2026-10-18 10:11:00',
    '2026-10-18 10:12:00'
  ),
  (
    4,
    4,
    25.00,
    'DINHEIRO',
    'APROVADO',
    '2026-10-20 09:01:00',
    '2026-10-20 09:02:00'
  ),
  (
    5,
    5,
    20.00,
    'PIX',
    'APROVADO',
    '2026-11-01 08:01:00',
    '2026-11-01 08:02:00'
  ),
  (
    6,
    6,
    20.00,
    'PIX',
    'APROVADO',
    '2026-11-01 08:11:00',
    '2026-11-01 08:12:00'
  ),
  (
    7,
    7,
    20.00,
    'PIX',
    'PENDENTE',
    '2026-11-01 08:21:00',
    NULL
  ),
  (
    8,
    8,
    20.00,
    'CARTAO',
    'RECUSADO',
    '2026-11-01 08:31:00',
    NULL
  ),
  (
    9,
    9,
    15.00,
    'PIX',
    'APROVADO',
    '2026-11-02 12:01:00',
    '2026-11-02 12:02:00'
  ),
  (
    10,
    10,
    15.00,
    'PIX',
    'APROVADO',
    '2026-11-02 12:06:00',
    '2026-11-02 12:07:00'
  );
UPDATE
  partida
SET
  status = 'FINALIZADA'
WHERE
  id_partida = 5;
UPDATE
  participacao
SET
  situacao = 'PRESENTE'
WHERE
  id_partida = 5
  AND id_atleta IN (1, 2, 3);
UPDATE
  participacao
SET
  situacao = 'FALTOU'
WHERE
  id_partida = 5
  AND id_atleta = 6;
INSERT INTO
  avaliacao (
    id_partida,
    id_atleta_avaliador,
    id_atleta_avaliado,
    nota,
    comentario
  )
VALUES
  (
    5,
    1,
    NULL,
    5,
    'Organização ótima, campo em bom estado'
  ),
  (5, 2, NULL, 4, 'Boa partida, faltou iluminação'),
  (5, 1, 2, 5, 'Jogou muito bem e foi pontual'),
  (
    5,
    2,
    1,
    4,
    'Bom jogador, poderia passar mais a bola'
  ),
  (5, 3, 1, 5, 'Excelente nível técnico'),
  (5, 1, 3, 4, 'Boa defesa'),
  (1, 1, 3, 5, 'Grande atuação na competição');
INSERT INTO
  mensagem (id_remetente, id_partida, texto, enviada_em)
VALUES
  (
    1,
    5,
    'Pessoal, confirmem presença até quarta.',
    '2026-10-20 18:00:00'
  ),
  (2, 5, 'Confirmado!', '2026-10-20 18:05:00'),
  (4, 5, 'Confirmado também.', '2026-10-20 18:10:00'),
  (
    6,
    6,
    'Levar colete claro e escuro.',
    '2026-11-02 10:00:00'
  ),
  (
    2,
    6,
    'Beleza, levo as bolas.',
    '2026-11-02 10:05:00'
  ),
  (
    3,
    7,
    'Quadra reservada das 19h às 20h.',
    '2026-11-03 09:00:00'
  );
INSERT INTO
  mensagem (
    id_remetente,
    id_destinatario,
    texto,
    enviada_em,
    editada_em,
    lida
  )
VALUES
  (
    1,
    2,
    'Fala, Bruno! Vai jogar sábado?',
    '2026-11-02 19:00:00',
    NULL,
    1
  ),
  (
    2,
    1,
    'Vou sim, já me inscrevi.',
    '2026-11-02 19:02:00',
    NULL,
    1
  ),
  (
    1,
    2,
    'Boa, a gente se vê lá.',
    '2026-11-02 19:03:00',
    '2026-11-02 19:05:00',
    1
  ),
  (
    3,
    1,
    'Ana, sobra vaga no vôlei?',
    '2026-11-03 08:10:00',
    NULL,
    0
  ),
  (
    1,
    3,
    'Sobra sim, te coloco na lista.',
    '2026-11-03 08:15:00',
    NULL,
    0
  ),
  (
    6,
    1,
    'Valeu pela organização de ontem!',
    '2026-11-03 09:30:00',
    NULL,
    0
  );
INSERT INTO
  notificacao (
    id_usuario,
    id_partida,
    tipo,
    mensagem,
    lida,
    criada_em
  )
VALUES
  (
    2,
    6,
    'CONFIRMACAO',
    'Sua inscrição na Pelada de sábado foi confirmada.',
    1,
    '2026-11-01 08:02:00'
  ),
  (
    4,
    6,
    'VAGA_DISPONIVEL',
    'Abriu uma vaga na Pelada de sábado.',
    0,
    '2026-11-02 19:00:00'
  ),
  (
    6,
    6,
    'PAGAMENTO',
    'Finalize o pagamento para garantir sua vaga.',
    0,
    '2026-11-01 08:21:00'
  ),
  (
    3,
    7,
    'ALTERACAO',
    'O Vôlei misto mudou para as 19h.',
    0,
    '2026-11-02 15:00:00'
  ),
  (
    1,
    5,
    'OUTRO',
    'Avalie o Fut society de quinta.',
    1,
    '2026-10-22 22:00:00'
  ),
  (
    5,
    NULL,
    'CANCELAMENTO',
    'Uma partida da sua agenda foi cancelada.',
    0,
    '2026-10-25 10:00:00'
  );
INSERT INTO
  chamado_suporte (
    id_chamado,
    id_usuario,
    id_partida,
    categoria,
    assunto,
    descricao,
    resposta,
    status,
    aberto_em,
    fechado_em
  )
VALUES
  (
    1,
    6,
    6,
    'PAGAMENTO',
    'Pagamento recusado',
    'Meu cartão foi recusado mas o valor aparece reservado.',
    NULL,
    'EM_ANALISE',
    '2026-11-01 09:00:00',
    NULL
  ),
  (
    2,
    3,
    7,
    'PARTIDA',
    'Mudança de horário',
    'Não fui avisado da mudança de horário da partida.',
    'Ajustamos o envio de notificações de alteração.',
    'RESOLVIDO',
    '2026-11-02 16:00:00',
    '2026-11-02 18:00:00'
  ),
  (
    3,
    5,
    NULL,
    'SISTEMA',
    'Erro ao abrir a lista',
    'A lista de partidas não carrega no celular.',
    NULL,
    'ABERTO',
    '2026-11-03 08:00:00',
    NULL
  );
SELECT
  u.nome AS usuario,
  u.email,
  p.nome AS permissao,
  up.concedida_em
FROM
  usuario u
  INNER JOIN usuario_permissao up ON up.id_usuario = u.id_usuario
  INNER JOIN permissao p ON p.id_permissao = up.id_permissao
ORDER BY
  u.nome,
  p.nome;
SELECT
  a.descricao,
  a.modalidade,
  a.local,
  a.cidade,
  a.data_hora,
  a.valor_inscricao,
  v.vagas_total,
  v.vagas_ocupadas,
  v.vagas_disponiveis,
  v.na_fila
FROM
  vw_agenda_partidas a
  JOIN vw_vagas_partida v ON v.id_partida = a.id_partida
WHERE
  a.status = 'AGENDADA'
ORDER BY
  a.data_hora DESC;
SELECT
  descricao,
  modalidade,
  local,
  cidade,
  data_hora
FROM
  vw_agenda_partidas
WHERE
  modalidade = 'Futebol society'
  AND cidade = 'Pinhais'
  AND data_hora BETWEEN '2026-11-01'
  AND '2026-11-30'
ORDER BY
  data_hora;
SELECT
  *
FROM
  vw_ranking_atleta
ORDER BY
  media_avaliacao DESC,
  partidas DESC,
  atleta;
SELECT
  av.criada_em,
  p.titulo AS partida,
  av.nota,
  av.comentario
FROM
  avaliacao av
  JOIN partida p ON p.id_partida = av.id_partida
WHERE
  av.id_atleta_avaliado = 1
ORDER BY
  av.criada_em DESC
LIMIT
  10;
SELECT
  p.data_hora,
  m.nome AS modalidade,
  l.nome AS local,
  pa.situacao,
  p.status
FROM
  participacao pa
  JOIN partida p ON p.id_partida = pa.id_partida
  JOIN modalidade m ON m.id_modalidade = p.id_modalidade
  LEFT JOIN local_esportivo l ON l.id_local = p.id_local
WHERE
  pa.id_atleta = 1
ORDER BY
  p.data_hora DESC;
SELECT
  p.titulo,
  COUNT(pg.id_pagamento) AS pagamentos,
  SUM(
    CASE
      WHEN pg.status = 'APROVADO' THEN pg.valor
      ELSE 0
    END
  ) AS total_arrecadado
FROM
  partida p
  JOIN participacao pa ON pa.id_partida = p.id_partida
  JOIN pagamento pg ON pg.id_participacao = pa.id_participacao
GROUP BY
  p.id_partida,
  p.titulo
ORDER BY
  total_arrecadado DESC;
SELECT
  p.titulo,
  f.posicao,
  a.nome AS atleta,
  f.status
FROM
  fila_espera f
  JOIN partida p ON p.id_partida = f.id_partida
  JOIN atleta a ON a.id_atleta = f.id_atleta
ORDER BY
  p.titulo,
  f.posicao;
SELECT
  c.nome AS competicao,
  em.nome AS mandante,
  ev.nome AS visitante,
  r.pontuacao_mandante,
  r.pontuacao_visitante,
  p.data_hora,
  p.status
FROM
  partida p
  JOIN competicao c ON c.id_competicao = p.id_competicao
  JOIN equipe em ON em.id_equipe = p.id_equipe_mandante
  JOIN equipe ev ON ev.id_equipe = p.id_equipe_visitante
  LEFT JOIN resultado_partida r ON r.id_partida = p.id_partida
ORDER BY
  p.data_hora;
SELECT
  c.id_chamado,
  u.nome AS solicitante,
  c.categoria,
  c.assunto,
  c.status,
  c.aberto_em
FROM
  chamado_suporte c
  JOIN usuario u ON u.id_usuario = c.id_usuario
WHERE
  c.status <> 'RESOLVIDO'
ORDER BY
  c.aberto_em;
SELECT
  c.id_usuario AS chat_id,
  c.nome AS contato,
  CASE
    WHEN l.ultimo_acesso >= NOW() - INTERVAL 5 MINUTE THEN 'Online'
    ELSE 'Online recentemente'
  END AS status,
  m.texto AS ultima_mensagem,
  m.enviada_em AS ultima_em,
  SUM(
    CASE
      WHEN m.id_destinatario = 1
      AND m.lida = 0 THEN 1
      ELSE 0
    END
  ) AS nao_lidas
FROM
  mensagem m
  JOIN usuario c ON c.id_usuario = IF(
    m.id_remetente = 1,
    m.id_destinatario,
    m.id_remetente
  )
  LEFT JOIN login l ON l.id_usuario = c.id_usuario
WHERE
  m.id_partida IS NULL
  AND 1 IN (m.id_remetente, m.id_destinatario)
GROUP BY
  c.id_usuario,
  c.nome,
  status
ORDER BY
  MAX(m.enviada_em) DESC;
SELECT
  m.id_mensagem,
  CASE
    WHEN m.id_remetente = 1 THEN 'eu'
    ELSE u.nome
  END AS remetente,
  m.texto,
  DATE_FORMAT(m.enviada_em, '%H:%i') AS horario,
  m.enviada_em,
  m.editada_em,
  (
    m.id_remetente = 1
    AND TIMESTAMPDIFF(MINUTE, m.enviada_em, NOW()) <= 15
  ) AS pode_editar
FROM
  mensagem m
  JOIN usuario u ON u.id_usuario = m.id_remetente
WHERE
  m.id_partida IS NULL
  AND (
    (
      m.id_remetente = 1
      AND m.id_destinatario = 2
    )
    OR (
      m.id_remetente = 2
      AND m.id_destinatario = 1
    )
  )
ORDER BY
  m.enviada_em;
SELECT
  u.nome AS autor,
  m.texto,
  DATE_FORMAT(m.enviada_em, '%d/%m %H:%i') AS quando
FROM
  mensagem m
  JOIN usuario u ON u.id_usuario = m.id_remetente
WHERE
  m.id_partida = 5
ORDER BY
  m.enviada_em;
