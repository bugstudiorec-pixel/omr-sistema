CREATE TABLE IF NOT EXISTS alunos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    turma TEXT NOT NULL,
    ra TEXT,
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS provas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    disciplina TEXT NOT NULL,
    total_questoes INTEGER NOT NULL,
    total_alternativas INTEGER NOT NULL DEFAULT 4,
    layout_file TEXT NOT NULL,
    observacoes TEXT,
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS gabaritos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prova_id INTEGER NOT NULL,
    versao TEXT NOT NULL DEFAULT 'A',
    numero_questao INTEGER NOT NULL,
    resposta_correta TEXT NOT NULL,
    UNIQUE(prova_id, versao, numero_questao),
    FOREIGN KEY(prova_id) REFERENCES provas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS aplicacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    aluno_id INTEGER NOT NULL,
    prova_id INTEGER NOT NULL,
    versao TEXT NOT NULL DEFAULT 'A',
    token_qr TEXT NOT NULL UNIQUE,
    qr_path TEXT,
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY(prova_id) REFERENCES provas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS resultados (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    aplicacao_id INTEGER,
    token_qr TEXT,
    imagem_original TEXT,
    imagem_alinhada TEXT,
    leitura_json TEXT,
    acertos INTEGER NOT NULL DEFAULT 0,
    erros INTEGER NOT NULL DEFAULT 0,
    em_branco INTEGER NOT NULL DEFAULT 0,
    rasuras INTEGER NOT NULL DEFAULT 0,
    nota REAL NOT NULL DEFAULT 0,
    percentual REAL NOT NULL DEFAULT 0,
    revisado_manual INTEGER NOT NULL DEFAULT 0,
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(aplicacao_id) REFERENCES aplicacoes(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS resultado_questoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    resultado_id INTEGER NOT NULL,
    numero_questao INTEGER NOT NULL,
    resposta_lida TEXT,
    resposta_final TEXT,
    resposta_correta TEXT,
    status TEXT NOT NULL,
    score_json TEXT,
    UNIQUE(resultado_id, numero_questao),
    FOREIGN KEY(resultado_id) REFERENCES resultados(id) ON DELETE CASCADE
);

CREATE VIEW IF NOT EXISTS vw_historico AS
SELECT r.id,
       r.criado_em,
       al.nome AS aluno,
       al.turma,
       p.nome AS prova,
       p.disciplina,
       a.versao,
       a.token_qr,
       r.acertos,
       r.erros,
       r.em_branco,
       r.rasuras,
       r.nota,
       r.percentual,
       r.revisado_manual
FROM resultados r
LEFT JOIN aplicacoes a ON a.id = r.aplicacao_id
LEFT JOIN alunos al ON al.id = a.aluno_id
LEFT JOIN provas p ON p.id = a.prova_id;
