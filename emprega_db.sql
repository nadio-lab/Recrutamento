-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15-Mar-2026 às 15:16
-- Versão do servidor: 10.4.18-MariaDB
-- versão do PHP: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `emprega_db`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `alertas_emprego`
--

CREATE TABLE `alertas_emprego` (
  `id` int(11) NOT NULL,
  `candidato_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `provincia_id` int(11) DEFAULT NULL,
  `palavra_chave` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_contrato` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `ultima_notificacao` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `candidatos`
--

CREATE TABLE `candidatos` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `genero` enum('M','F','outro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nacionalidade` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT 'Angolana',
  `provincia_id` int(11) DEFAULT NULL,
  `morada` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titulo_profissional` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ex: Engenheiro de Software Sénior',
  `sobre` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Bio / resumo do candidato',
  `cv_ficheiro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Caminho para o PDF do CV',
  `linkedin` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portfolio` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disponibilidade` enum('imediata','1_mes','3_meses','empregado') COLLATE utf8mb4_unicode_ci DEFAULT 'imediata',
  `salario_pretendido` decimal(12,2) DEFAULT NULL,
  `moeda_salario` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'AOA',
  `perfil_completo` tinyint(1) DEFAULT 0,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `candidatos`
--

INSERT INTO `candidatos` (`id`, `utilizador_id`, `foto`, `telefone`, `data_nascimento`, `genero`, `nacionalidade`, `provincia_id`, `morada`, `titulo_profissional`, `sobre`, `cv_ficheiro`, `linkedin`, `portfolio`, `disponibilidade`, `salario_pretendido`, `moeda_salario`, `perfil_completo`, `atualizado_em`) VALUES
(1, 2, NULL, NULL, NULL, NULL, 'Angolana', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'imediata', NULL, 'AOA', 0, '2026-03-14 21:10:32');

-- --------------------------------------------------------

--
-- Estrutura da tabela `candidato_competencias`
--

CREATE TABLE `candidato_competencias` (
  `id` int(11) NOT NULL,
  `candidato_id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel` enum('basico','intermedio','avancado','especialista') COLLATE utf8mb4_unicode_ci DEFAULT 'intermedio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `candidato_educacao`
--

CREATE TABLE `candidato_educacao` (
  `id` int(11) NOT NULL,
  `candidato_id` int(11) NOT NULL,
  `instituicao` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `curso` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel` enum('basico','medio','bacharelato','licenciatura','mestrado','doutoramento','outro') COLLATE utf8mb4_unicode_ci DEFAULT 'licenciatura',
  `data_inicio` year(4) DEFAULT NULL,
  `data_fim` year(4) DEFAULT NULL,
  `em_curso` tinyint(1) DEFAULT 0,
  `descricao` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `candidato_experiencia`
--

CREATE TABLE `candidato_experiencia` (
  `id` int(11) NOT NULL,
  `candidato_id` int(11) NOT NULL,
  `empresa` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cargo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `atual` tinyint(1) DEFAULT 0,
  `descricao` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `candidaturas`
--

CREATE TABLE `candidaturas` (
  `id` int(11) NOT NULL,
  `vaga_id` int(11) NOT NULL,
  `candidato_id` int(11) NOT NULL,
  `carta_apresentacao` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cv_ficheiro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CV específico para esta candidatura',
  `estado` enum('enviada','vista','em_analise','entrevista','oferta','aceite','rejeitada','retirada') COLLATE utf8mb4_unicode_ci DEFAULT 'enviada',
  `nota_empresa` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nota interna da empresa',
  `nota_admin` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_candidatura` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notificado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `candidaturas`
--

INSERT INTO `candidaturas` (`id`, `vaga_id`, `candidato_id`, `carta_apresentacao`, `cv_ficheiro`, `estado`, `nota_empresa`, `nota_admin`, `data_candidatura`, `data_atualizacao`, `notificado`) VALUES
(1, 1, 1, '', '69b6a74048b2a5.52680955.pdf', 'vista', 'Deve enviar outros ducumentos', NULL, '2026-03-15 12:34:08', '2026-03-15 13:42:04', 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'briefcase',
  `total_vagas` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `slug`, `icone`, `total_vagas`, `ativo`) VALUES
(1, 'Tecnologia & TI', 'tecnologia-ti', 'monitor', 0, 1),
(2, 'Engenharia', 'engenharia', 'tool', 0, 1),
(3, 'Saúde', 'saude', 'heart', 0, 1),
(4, 'Educação', 'educacao', 'book', 0, 1),
(5, 'Finanças & Banca', 'financas-banca', 'dollar-sign', 0, 1),
(6, 'Vendas & Comercial', 'vendas-comercial', 'shopping-bag', 0, 1),
(7, 'Marketing & Publicidade', 'marketing', 'trending-up', 0, 1),
(8, 'Recursos Humanos', 'recursos-humanos', 'users', 0, 1),
(9, 'Logística & Transporte', 'logistica', 'truck', 0, 1),
(10, 'Construção & Obras', 'construcao', 'home', 0, 1),
(11, 'Petróleo & Gás', 'petroleo-gas', 'zap', 0, 1),
(12, 'Direito & Jurídico', 'direito', 'shield', 0, 1),
(13, 'Hotelaria & Turismo', 'hotelaria-turismo', 'coffee', 0, 1),
(14, 'Agricultura', 'agricultura', 'sun', 0, 1),
(15, 'Outros', 'outros', 'grid', 0, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `chave`, `valor`, `atualizado_em`) VALUES
(1, 'site_nome', 'EmpregaNews', '2026-03-14 21:12:43'),
(2, 'site_slogan', 'O teu futuro começa aqui', '2026-03-14 21:09:46'),
(3, 'site_email', 'geral@emprega.ao', '2026-03-14 21:09:46'),
(4, 'site_telefone', '+244 930 581 053', '2026-03-14 21:12:43'),
(5, 'site_pais', 'Angola', '2026-03-14 21:09:46'),
(6, 'cor_primaria', '#0a2540', '2026-03-14 21:09:46'),
(7, 'cor_acento', '#e63946', '2026-03-14 21:09:46'),
(8, 'cor_secundaria', '#457b9d', '2026-03-14 21:09:46'),
(9, 'vagas_por_pagina', '12', '2026-03-14 21:09:46'),
(10, 'aprovacao_empresa', '1', '2026-03-14 21:09:46'),
(11, 'site_descricao', 'Plataforma de emprego líder em Angola. Encontra o teu próximo emprego ou o teu próximo talento.', '2026-03-14 21:09:46'),
(12, 'site_logo', '69b6ab3deba8d7.78468980.png', '2026-03-15 12:51:09');

-- --------------------------------------------------------

--
-- Estrutura da tabela `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `nome` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nif` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_contato` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provincia_id` int(11) DEFAULT NULL,
  `morada` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `setor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dimensao` enum('startup','pequena','media','grande','multinacional') COLLATE utf8mb4_unicode_ci DEFAULT 'media',
  `ano_fundacao` year(4) DEFAULT NULL,
  `sobre` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('pendente','aprovada','suspensa','rejeitada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `verificada` tinyint(1) DEFAULT 0 COMMENT 'Selo verificado pelo admin',
  `total_vagas_publicadas` int(11) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `empresas`
--

INSERT INTO `empresas` (`id`, `utilizador_id`, `nome`, `slug`, `logo`, `nif`, `website`, `telefone`, `email_contato`, `provincia_id`, `morada`, `setor`, `dimensao`, `ano_fundacao`, `sobre`, `estado`, `verificada`, `total_vagas_publicadas`, `criado_em`, `atualizado_em`) VALUES
(1, 3, 'Empreganiws1', 'empreganiws1', '69b5d0acc41d60.91979150.png', '', '', '', '', NULL, '', '', 'media', NULL, '', 'aprovada', 1, 1, '2026-03-14 21:13:30', '2026-03-14 21:18:36');

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) DEFAULT NULL,
  `acao` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalhe` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `tipo` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'candidatura_recebida, estado_candidatura, vaga_nova, etc.',
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `notificacoes`
--

INSERT INTO `notificacoes` (`id`, `utilizador_id`, `tipo`, `titulo`, `mensagem`, `link`, `lida`, `criado_em`) VALUES
(1, 3, 'empresa_aprovada', 'A tua empresa foi aprovada!', 'Já podes publicar vagas.', 'http://localhost/betilson/recrutamento/empresa/index.php', 0, '2026-03-14 21:14:21'),
(2, 3, 'candidatura_recebida', 'Nova candidatura recebida', 'Rodrigues Pongolola candidatou-se à vaga \"Engenheiro Informático\".', 'http://localhost/betilson/recrutamento/empresa/candidaturas.php', 0, '2026-03-15 12:34:08'),
(3, 2, 'estado_candidatura', 'Candidatura actualizada', 'Estado: Vista.', '', 1, '2026-03-15 12:38:05'),
(4, 2, 'estado_candidatura', 'Candidatura actualizada', 'Estado: Em análise.', '', 1, '2026-03-15 12:38:18'),
(5, 2, 'estado_candidatura', 'Candidatura actualizada', 'Estado: Enviada.', '', 1, '2026-03-15 12:38:22'),
(6, 2, 'estado_candidatura', 'Candidatura actualizada', 'Estado: Vista.', '', 1, '2026-03-15 12:38:25'),
(7, 2, 'estado_candidatura', 'Candidatura actualizada', 'Estado: Oferta.', '', 1, '2026-03-15 12:38:31'),
(8, 2, 'estado_candidatura', 'Candidatura actualizada', 'Estado: Vista.', '', 1, '2026-03-15 12:38:34'),
(9, 2, 'estado_candidatura', 'Candidatura actualizada', 'Estado actualizado para: Vista.', '', 1, '2026-03-15 12:40:28'),
(10, 2, 'estado_candidatura', 'Candidatura actualizada', 'Estado actualizado para: Vista.', '', 1, '2026-03-15 12:40:43'),
(11, 2, 'estado_candidatura', 'Candidatura actualizada', 'O administrador atualizou o estado para: Enviada.', '', 1, '2026-03-15 13:41:47'),
(12, 2, 'estado_candidatura', 'Candidatura actualizada', 'O administrador atualizou o estado para: Vista.', '', 1, '2026-03-15 13:42:04');

-- --------------------------------------------------------

--
-- Estrutura da tabela `provincias`
--

CREATE TABLE `provincias` (
  `id` int(11) NOT NULL,
  `nome` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `provincias`
--

INSERT INTO `provincias` (`id`, `nome`) VALUES
(1, 'Luanda'),
(2, 'Benguela'),
(3, 'Huambo'),
(4, 'Huíla'),
(5, 'Cabinda'),
(6, 'Malanje'),
(7, 'Uíge'),
(8, 'Lunda Norte'),
(9, 'Lunda Sul'),
(10, 'Bié'),
(11, 'Moxico'),
(12, 'Cuando Cubango'),
(13, 'Cunene'),
(14, 'Namibe'),
(15, 'Zaire'),
(16, 'Bengo'),
(17, 'Kwanza Norte'),
(18, 'Kwanza Sul'),
(19, 'Kuando Kubango'),
(20, 'Todo o País');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_expira` datetime DEFAULT NULL,
  `tipo` enum('admin','empresa','candidato') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `email_verificado` tinyint(1) DEFAULT 0,
  `token_verificacao` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_reset` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`id`, `nome`, `email`, `foto`, `password`, `reset_token`, `reset_expira`, `tipo`, `ativo`, `email_verificado`, `token_verificacao`, `token_reset`, `ultimo_login`, `criado_em`) VALUES
(1, 'Administrador', 'admin@emprega.ao', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'admin', 1, 1, NULL, NULL, '2026-03-15 14:12:50', '2026-03-14 21:09:46'),
(2, 'Rodrigues Pongolola', 'rodriguespongolola47@gmail.com', NULL, '$2y$10$z2TBTyy21CwSxH6Zq8qDD.fk4zljkQA6ESCWDQF1VKsasWlz8f9ci', '3310bdd59c58bd6ffbddef73894b72792bffe596c4ebfbd458a9f36a44b2f1da', '2026-03-15 16:10:31', 'candidato', 1, 1, NULL, NULL, '2026-03-15 13:50:46', '2026-03-14 21:10:32'),
(3, 'Domingos', 'domingos@gmail.com', NULL, '$2y$10$CWc4B6uFxf/PAtfGyW729O7e68KOj5P1ga8yGnODnF1k4gGk1laDa', NULL, NULL, 'empresa', 1, 1, NULL, NULL, '2026-03-15 12:35:05', '2026-03-14 21:13:30');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vagas`
--

CREATE TABLE `vagas` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `provincia_id` int(11) DEFAULT NULL,
  `tipo_contrato` enum('efectivo','contrato','part_time','freelance','estagio','voluntario') COLLATE utf8mb4_unicode_ci DEFAULT 'efectivo',
  `modalidade` enum('presencial','remoto','hibrido') COLLATE utf8mb4_unicode_ci DEFAULT 'presencial',
  `nivel_experiencia` enum('sem_experiencia','junior','medio','senior','diretor') COLLATE utf8mb4_unicode_ci DEFAULT 'medio',
  `nivel_escolaridade` enum('basico','medio','bacharelato','licenciatura','mestrado','doutoramento','indiferente') COLLATE utf8mb4_unicode_ci DEFAULT 'licenciatura',
  `descricao` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `requisitos` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficios` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salario_min` decimal(12,2) DEFAULT NULL,
  `salario_max` decimal(12,2) DEFAULT NULL,
  `moeda_salario` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'AOA',
  `salario_negociavel` tinyint(1) DEFAULT 0,
  `salario_visivel` tinyint(1) DEFAULT 1,
  `vagas_disponiveis` int(11) DEFAULT 1,
  `data_publicacao` date DEFAULT curdate(),
  `data_encerramento` date DEFAULT NULL,
  `estado` enum('rascunho','pendente','publicada','encerrada','arquivada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `destaque` tinyint(1) DEFAULT 0,
  `total_candidaturas` int(11) DEFAULT 0,
  `total_visualizacoes` int(11) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `vagas`
--

INSERT INTO `vagas` (`id`, `empresa_id`, `titulo`, `slug`, `categoria_id`, `provincia_id`, `tipo_contrato`, `modalidade`, `nivel_experiencia`, `nivel_escolaridade`, `descricao`, `requisitos`, `beneficios`, `salario_min`, `salario_max`, `moeda_salario`, `salario_negociavel`, `salario_visivel`, `vagas_disponiveis`, `data_publicacao`, `data_encerramento`, `estado`, `destaque`, `total_candidaturas`, `total_visualizacoes`, `criado_em`, `atualizado_em`) VALUES
(1, 1, 'Engenheiro Informático', 'engenheiro-informatico', 2, 1, 'contrato', 'presencial', 'junior', 'licenciatura', 'boa.', 'Bom dominio em inglês.', 'SEguro de Saúde.', '23000.00', '30000.00', 'AOA', 1, 1, 20, '2026-03-14', '2026-08-25', 'encerrada', 0, 1, 5, '2026-03-14 21:17:37', '2026-03-15 12:42:21');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vagas_guardadas`
--

CREATE TABLE `vagas_guardadas` (
  `id` int(11) NOT NULL,
  `candidato_id` int(11) NOT NULL,
  `vaga_id` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `vagas_guardadas`
--

INSERT INTO `vagas_guardadas` (`id`, `candidato_id`, `vaga_id`, `criado_em`) VALUES
(1, 1, 1, '2026-03-15 12:33:29');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `alertas_emprego`
--
ALTER TABLE `alertas_emprego`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidato_id` (`candidato_id`);

--
-- Índices para tabela `candidatos`
--
ALTER TABLE `candidatos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilizador_id` (`utilizador_id`),
  ADD KEY `provincia_id` (`provincia_id`);

--
-- Índices para tabela `candidato_competencias`
--
ALTER TABLE `candidato_competencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidato_id` (`candidato_id`);

--
-- Índices para tabela `candidato_educacao`
--
ALTER TABLE `candidato_educacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidato_id` (`candidato_id`);

--
-- Índices para tabela `candidato_experiencia`
--
ALTER TABLE `candidato_experiencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidato_id` (`candidato_id`);

--
-- Índices para tabela `candidaturas`
--
ALTER TABLE `candidaturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_candidatura` (`vaga_id`,`candidato_id`),
  ADD KEY `idx_candidaturas_vaga` (`vaga_id`),
  ADD KEY `idx_candidaturas_cand` (`candidato_id`);

--
-- Índices para tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices para tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices para tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilizador_id` (`utilizador_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `provincia_id` (`provincia_id`);

--
-- Índices para tabela `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilizador_id` (`utilizador_id`);

--
-- Índices para tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notificacoes_user` (`utilizador_id`,`lida`);

--
-- Índices para tabela `provincias`
--
ALTER TABLE `provincias`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `vagas`
--
ALTER TABLE `vagas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_vagas_estado` (`estado`),
  ADD KEY `idx_vagas_categoria` (`categoria_id`),
  ADD KEY `idx_vagas_provincia` (`provincia_id`),
  ADD KEY `idx_vagas_empresa` (`empresa_id`),
  ADD KEY `idx_vagas_destaque` (`destaque`,`estado`);

--
-- Índices para tabela `vagas_guardadas`
--
ALTER TABLE `vagas_guardadas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_guardada` (`candidato_id`,`vaga_id`),
  ADD KEY `vaga_id` (`vaga_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alertas_emprego`
--
ALTER TABLE `alertas_emprego`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `candidatos`
--
ALTER TABLE `candidatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `candidato_competencias`
--
ALTER TABLE `candidato_competencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `candidato_educacao`
--
ALTER TABLE `candidato_educacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `candidato_experiencia`
--
ALTER TABLE `candidato_experiencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `candidaturas`
--
ALTER TABLE `candidaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `provincias`
--
ALTER TABLE `provincias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `vagas`
--
ALTER TABLE `vagas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `vagas_guardadas`
--
ALTER TABLE `vagas_guardadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `alertas_emprego`
--
ALTER TABLE `alertas_emprego`
  ADD CONSTRAINT `alertas_emprego_ibfk_1` FOREIGN KEY (`candidato_id`) REFERENCES `candidatos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `candidatos`
--
ALTER TABLE `candidatos`
  ADD CONSTRAINT `candidatos_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidatos_ibfk_2` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`id`);

--
-- Limitadores para a tabela `candidato_competencias`
--
ALTER TABLE `candidato_competencias`
  ADD CONSTRAINT `candidato_competencias_ibfk_1` FOREIGN KEY (`candidato_id`) REFERENCES `candidatos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `candidato_educacao`
--
ALTER TABLE `candidato_educacao`
  ADD CONSTRAINT `candidato_educacao_ibfk_1` FOREIGN KEY (`candidato_id`) REFERENCES `candidatos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `candidato_experiencia`
--
ALTER TABLE `candidato_experiencia`
  ADD CONSTRAINT `candidato_experiencia_ibfk_1` FOREIGN KEY (`candidato_id`) REFERENCES `candidatos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `candidaturas`
--
ALTER TABLE `candidaturas`
  ADD CONSTRAINT `candidaturas_ibfk_1` FOREIGN KEY (`vaga_id`) REFERENCES `vagas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidaturas_ibfk_2` FOREIGN KEY (`candidato_id`) REFERENCES `candidatos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `empresas_ibfk_2` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`id`);

--
-- Limitadores para a tabela `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD CONSTRAINT `notificacoes_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `vagas`
--
ALTER TABLE `vagas`
  ADD CONSTRAINT `vagas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vagas_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `vagas_ibfk_3` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`id`);

--
-- Limitadores para a tabela `vagas_guardadas`
--
ALTER TABLE `vagas_guardadas`
  ADD CONSTRAINT `vagas_guardadas_ibfk_1` FOREIGN KEY (`candidato_id`) REFERENCES `candidatos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vagas_guardadas_ibfk_2` FOREIGN KEY (`vaga_id`) REFERENCES `vagas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
