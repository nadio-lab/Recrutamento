-- ============================================================
-- EMPREGA — PLATAFORMA DE RECRUTAMENTO ONLINE
-- Base de dados MySQL/MariaDB
-- ============================================================

CREATE DATABASE IF NOT EXISTS emprega_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE emprega_db;

-- ------------------------------------------------------------
-- CONFIGURAÇÕES DO SITE
-- ------------------------------------------------------------
CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO configuracoes (chave, valor) VALUES
('site_nome',        'Emprega'),
('site_slogan',      'O teu futuro começa aqui'),
('site_email',       'geral@emprega.ao'),
('site_telefone',    '+244 900 000 000'),
('site_pais',        'Angola'),
('cor_primaria',     '#0a2540'),
('cor_acento',       '#e63946'),
('cor_secundaria',   '#457b9d'),
('vagas_por_pagina', '12'),
('aprovacao_empresa','1'),
('site_descricao',   'Plataforma de emprego líder em Angola. Encontra o teu próximo emprego ou o teu próximo talento.');

-- ------------------------------------------------------------
-- CATEGORIAS DE EMPREGO
-- ------------------------------------------------------------
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    icone VARCHAR(50) DEFAULT 'briefcase',
    total_vagas INT DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1
);

INSERT INTO categorias (nome, slug, icone) VALUES
('Tecnologia & TI',       'tecnologia-ti',       'monitor'),
('Engenharia',            'engenharia',           'tool'),
('Saúde',                 'saude',                'heart'),
('Educação',              'educacao',             'book'),
('Finanças & Banca',      'financas-banca',       'dollar-sign'),
('Vendas & Comercial',    'vendas-comercial',     'shopping-bag'),
('Marketing & Publicidade','marketing',           'trending-up'),
('Recursos Humanos',      'recursos-humanos',     'users'),
('Logística & Transporte','logistica',            'truck'),
('Construção & Obras',    'construcao',           'home'),
('Petróleo & Gás',        'petroleo-gas',         'zap'),
('Direito & Jurídico',    'direito',              'shield'),
('Hotelaria & Turismo',   'hotelaria-turismo',    'coffee'),
('Agricultura',           'agricultura',          'sun'),
('Outros',                'outros',               'grid');

-- ------------------------------------------------------------
-- PROVÍNCIAS
-- ------------------------------------------------------------
CREATE TABLE provincias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL
);

INSERT INTO provincias (nome) VALUES
('Luanda'),('Benguela'),('Huambo'),('Huíla'),('Cabinda'),
('Malanje'),('Uíge'),('Lunda Norte'),('Lunda Sul'),('Bié'),
('Moxico'),('Cuando Cubango'),('Cunene'),('Namibe'),('Zaire'),
('Bengo'),('Kwanza Norte'),('Kwanza Sul'),('Kuando Kubango'),('Todo o País');

-- ------------------------------------------------------------
-- UTILIZADORES (candidatos, empresas, admin)
-- ------------------------------------------------------------
CREATE TABLE utilizadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    tipo ENUM('admin','empresa','candidato') NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    email_verificado TINYINT(1) DEFAULT 0,
    token_verificacao VARCHAR(100) DEFAULT NULL,
    token_reset VARCHAR(100) DEFAULT NULL,
    ultimo_login TIMESTAMP NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin padrão (senha: Admin@2025)
INSERT INTO utilizadores (nome, email, password, tipo, ativo, email_verificado)
VALUES ('Administrador', 'admin@emprega.ao',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin', 1, 1);

-- ------------------------------------------------------------
-- PERFIL CANDIDATO
-- ------------------------------------------------------------
CREATE TABLE candidatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilizador_id INT NOT NULL UNIQUE,
    foto VARCHAR(255) DEFAULT NULL,
    telefone VARCHAR(30),
    data_nascimento DATE,
    genero ENUM('M','F','outro'),
    nacionalidade VARCHAR(80) DEFAULT 'Angolana',
    provincia_id INT,
    morada TEXT,
    titulo_profissional VARCHAR(150) COMMENT 'Ex: Engenheiro de Software Sénior',
    sobre TEXT COMMENT 'Bio / resumo do candidato',
    cv_ficheiro VARCHAR(255) COMMENT 'Caminho para o PDF do CV',
    linkedin VARCHAR(200),
    portfolio VARCHAR(200),
    disponibilidade ENUM('imediata','1_mes','3_meses','empregado') DEFAULT 'imediata',
    salario_pretendido DECIMAL(12,2) DEFAULT NULL,
    moeda_salario VARCHAR(10) DEFAULT 'AOA',
    perfil_completo TINYINT(1) DEFAULT 0,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE,
    FOREIGN KEY (provincia_id) REFERENCES provincias(id)
);

-- ------------------------------------------------------------
-- EDUCAÇÃO DO CANDIDATO
-- ------------------------------------------------------------
CREATE TABLE candidato_educacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidato_id INT NOT NULL,
    instituicao VARCHAR(150) NOT NULL,
    curso VARCHAR(150) NOT NULL,
    nivel ENUM('basico','medio','bacharelato','licenciatura','mestrado','doutoramento','outro') DEFAULT 'licenciatura',
    data_inicio YEAR,
    data_fim YEAR,
    em_curso TINYINT(1) DEFAULT 0,
    descricao TEXT,
    FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- EXPERIÊNCIA DO CANDIDATO
-- ------------------------------------------------------------
CREATE TABLE candidato_experiencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidato_id INT NOT NULL,
    empresa VARCHAR(150) NOT NULL,
    cargo VARCHAR(150) NOT NULL,
    data_inicio DATE,
    data_fim DATE,
    atual TINYINT(1) DEFAULT 0,
    descricao TEXT,
    FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- COMPETÊNCIAS DO CANDIDATO
-- ------------------------------------------------------------
CREATE TABLE candidato_competencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidato_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    nivel ENUM('basico','intermedio','avancado','especialista') DEFAULT 'intermedio',
    FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- EMPRESAS
-- ------------------------------------------------------------
CREATE TABLE empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilizador_id INT NOT NULL UNIQUE,
    nome VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    logo VARCHAR(255),
    nif VARCHAR(30),
    website VARCHAR(200),
    telefone VARCHAR(30),
    email_contato VARCHAR(150),
    provincia_id INT,
    morada TEXT,
    setor VARCHAR(100),
    dimensao ENUM('startup','pequena','media','grande','multinacional') DEFAULT 'media',
    ano_fundacao YEAR,
    sobre TEXT,
    estado ENUM('pendente','aprovada','suspensa','rejeitada') DEFAULT 'pendente',
    verificada TINYINT(1) DEFAULT 0 COMMENT 'Selo verificado pelo admin',
    total_vagas_publicadas INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE,
    FOREIGN KEY (provincia_id) REFERENCES provincias(id)
);

-- ------------------------------------------------------------
-- VAGAS DE EMPREGO
-- ------------------------------------------------------------
CREATE TABLE vagas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    categoria_id INT,
    provincia_id INT,
    tipo_contrato ENUM('efectivo','contrato','part_time','freelance','estagio','voluntario') DEFAULT 'efectivo',
    modalidade ENUM('presencial','remoto','hibrido') DEFAULT 'presencial',
    nivel_experiencia ENUM('sem_experiencia','junior','medio','senior','diretor') DEFAULT 'medio',
    nivel_escolaridade ENUM('basico','medio','bacharelato','licenciatura','mestrado','doutoramento','indiferente') DEFAULT 'licenciatura',
    descricao LONGTEXT NOT NULL,
    requisitos TEXT,
    beneficios TEXT,
    salario_min DECIMAL(12,2) DEFAULT NULL,
    salario_max DECIMAL(12,2) DEFAULT NULL,
    moeda_salario VARCHAR(10) DEFAULT 'AOA',
    salario_negociavel TINYINT(1) DEFAULT 0,
    salario_visivel TINYINT(1) DEFAULT 1,
    vagas_disponiveis INT DEFAULT 1,
    data_publicacao DATE DEFAULT (CURRENT_DATE),
    data_encerramento DATE,
    estado ENUM('rascunho','pendente','publicada','encerrada','arquivada') DEFAULT 'pendente',
    destaque TINYINT(1) DEFAULT 0,
    total_candidaturas INT DEFAULT 0,
    total_visualizacoes INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (provincia_id) REFERENCES provincias(id)
);

-- ------------------------------------------------------------
-- CANDIDATURAS
-- ------------------------------------------------------------
CREATE TABLE candidaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vaga_id INT NOT NULL,
    candidato_id INT NOT NULL,
    carta_apresentacao TEXT,
    cv_ficheiro VARCHAR(255) COMMENT 'CV específico para esta candidatura',
    estado ENUM('enviada','vista','em_analise','entrevista','oferta','aceite','rejeitada','retirada') DEFAULT 'enviada',
    nota_empresa TEXT COMMENT 'Nota interna da empresa',
    nota_admin TEXT,
    data_candidatura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notificado TINYINT(1) DEFAULT 0,
    FOREIGN KEY (vaga_id) REFERENCES vagas(id) ON DELETE CASCADE,
    FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_candidatura (vaga_id, candidato_id)
);

-- ------------------------------------------------------------
-- VAGAS GUARDADAS (favoritos)
-- ------------------------------------------------------------
CREATE TABLE vagas_guardadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidato_id INT NOT NULL,
    vaga_id INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE,
    FOREIGN KEY (vaga_id) REFERENCES vagas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_guardada (candidato_id, vaga_id)
);

-- ------------------------------------------------------------
-- NOTIFICAÇÕES
-- ------------------------------------------------------------
CREATE TABLE notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilizador_id INT NOT NULL,
    tipo VARCHAR(60) NOT NULL COMMENT 'candidatura_recebida, estado_candidatura, vaga_nova, etc.',
    titulo VARCHAR(200) NOT NULL,
    mensagem TEXT,
    link VARCHAR(255),
    lida TINYINT(1) DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- ALERTAS DE EMPREGO (candidatos recebem emails de novas vagas)
-- ------------------------------------------------------------
CREATE TABLE alertas_emprego (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidato_id INT NOT NULL,
    categoria_id INT,
    provincia_id INT,
    palavra_chave VARCHAR(100),
    tipo_contrato VARCHAR(30),
    ativo TINYINT(1) DEFAULT 1,
    ultima_notificacao TIMESTAMP NULL,
    FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- LOG DE ACTIVIDADES
-- ------------------------------------------------------------
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilizador_id INT,
    acao VARCHAR(200) NOT NULL,
    detalhe TEXT,
    ip VARCHAR(50),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- ÍNDICES para performance
-- ------------------------------------------------------------
CREATE INDEX idx_vagas_estado     ON vagas(estado);
CREATE INDEX idx_vagas_categoria  ON vagas(categoria_id);
CREATE INDEX idx_vagas_provincia  ON vagas(provincia_id);
CREATE INDEX idx_vagas_empresa    ON vagas(empresa_id);
CREATE INDEX idx_vagas_destaque   ON vagas(destaque, estado);
CREATE INDEX idx_candidaturas_vaga     ON candidaturas(vaga_id);
CREATE INDEX idx_candidaturas_cand     ON candidaturas(candidato_id);
CREATE INDEX idx_notificacoes_user     ON notificacoes(utilizador_id, lida);
