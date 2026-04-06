-- Banco Completo Atualizado - Advocacia System
-- Contempla os módulos: LexFlow (Trilha do Cliente), Kanban, Financeiro e Agenda

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Tabela: clientes
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `endereco` varchar(200) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: usuarios
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('administrador','usuario') DEFAULT 'usuario',
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `acessos` text DEFAULT NULL,
  `primeiro_acesso` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `status`, `acessos`, `primeiro_acesso`, `criado_em`) VALUES
(1, 'Admin', 'admin@advocacia.com', '$2y$10$KgP04pRPnehawXazvSqXqOG5M/AqgrCnXdV1XwfghpHvnNajy0Hfi', 'administrador', 'ativo', '[]', 0, '2026-03-21 00:00:00');

-- Tabela: processos
DROP TABLE IF EXISTS `processos`;
CREATE TABLE `processos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_processo` varchar(50) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `tipo_acao` varchar(100) DEFAULT NULL,
  `vara_juizo` varchar(100) DEFAULT NULL,
  `comarca` varchar(100) DEFAULT NULL,
  `parte_contraria` varchar(150) DEFAULT NULL,
  `id_advogado` int(11) DEFAULT NULL,
  `status` enum('ativo','arquivado','suspenso','encerrado') DEFAULT 'ativo',
  `data_distribuicao` date DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `token_acesso` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_processo` (`numero_processo`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_advogado` (`id_advogado`),
  CONSTRAINT `processos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `processos_ibfk_2` FOREIGN KEY (`id_advogado`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: audiencias
DROP TABLE IF EXISTS `audiencias`;
CREATE TABLE `audiencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_processo` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `data_audiencia` date NOT NULL,
  `hora_audiencia` time NOT NULL,
  `local_audiencia` varchar(150) DEFAULT NULL,
  `tipo_audiencia` varchar(100) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `status` enum('agendada','realizada','cancelada') DEFAULT 'agendada',
  PRIMARY KEY (`id`),
  KEY `id_processo` (`id_processo`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `audiencias_ibfk_1` FOREIGN KEY (`id_processo`) REFERENCES `processos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `audiencias_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: checklists
DROP TABLE IF EXISTS `checklists`;
CREATE TABLE `checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `processo_id` int(11) NOT NULL,
  `item` varchar(255) NOT NULL,
  `status` enum('pendente','concluida') DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `processo_id` (`processo_id`),
  CONSTRAINT `checklists_ibfk_1` FOREIGN KEY (`processo_id`) REFERENCES `processos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: documentos
DROP TABLE IF EXISTS `documentos`;
CREATE TABLE `documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_processo` int(11) DEFAULT NULL,
  `tipo_documento` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `caminho_arquivo` varchar(255) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_processo` (`id_processo`),
  CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documentos_ibfk_2` FOREIGN KEY (`id_processo`) REFERENCES `processos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: honorarios
DROP TABLE IF EXISTS `honorarios`;
CREATE TABLE `honorarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `id_processo` int(11) DEFAULT NULL,
  `tipo_honorario` varchar(100) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date DEFAULT NULL,
  `data_pagamento` date DEFAULT NULL,
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `status` enum('pendente','pago','cancelado') DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_processo` (`id_processo`),
  CONSTRAINT `honorarios_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `honorarios_ibfk_2` FOREIGN KEY (`id_processo`) REFERENCES `processos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: kanban_colunas
DROP TABLE IF EXISTS `kanban_colunas`;
CREATE TABLE `kanban_colunas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `cor` varchar(20) DEFAULT '#1F6E8C',
  `ordem` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kanban_colunas` (`id`, `titulo`, `cor`, `ordem`) VALUES
(1, 'Pendentes', '#dc3545', 1),
(2, 'Em Andamento', '#ffc107', 2),
(3, 'Concluídas', '#28a745', 3);

-- Tabela: prazos
DROP TABLE IF EXISTS `prazos`;
CREATE TABLE `prazos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_processo` int(11) NOT NULL,
  `descricao_prazo` varchar(200) NOT NULL,
  `data_limite` date NOT NULL,
  `id_responsavel` int(11) DEFAULT NULL,
  `status` enum('pendente','cumprido','vencido') DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_processo` (`id_processo`),
  KEY `id_responsavel` (`id_responsavel`),
  CONSTRAINT `prazos_ibfk_1` FOREIGN KEY (`id_processo`) REFERENCES `processos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prazos_ibfk_2` FOREIGN KEY (`id_responsavel`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: sugestoes_juridicas
DROP TABLE IF EXISTS `sugestoes_juridicas`;
CREATE TABLE `sugestoes_juridicas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_processo` varchar(100) DEFAULT NULL,
  `sugestao` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sugestoes_juridicas` (`id`, `tipo_processo`, `sugestao`) VALUES
(1, 'alimentos', 'Pedido de tutela de urgência (alimentos provisórios)'),
(2, 'alimentos', 'Anexar lista de necessidades e plano financeiro'),
(3, 'execucao', 'Solicitar bloqueio de contas via SISBAJUD'),
(4, 'execucao', 'Apresentar cálculo atualizado do débito na planilha'),
(5, 'trabalhista', 'Solicitar recolhimento de impostos e comprovantes de ponto'),
(6, 'divorcio', 'Levantamento de bens comuns para partilha consensual');

-- Tabela: tarefas
DROP TABLE IF EXISTS `tarefas`;
CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `id_responsavel` int(11) DEFAULT NULL,
  `prioridade` enum('baixa','media','alta') DEFAULT 'media',
  `data_inicio` date DEFAULT NULL,
  `data_final` date DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `id_quadro` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_processo` int(11) DEFAULT NULL,
  `info_cliente` text DEFAULT NULL,
  `arquivado` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_responsavel` (`id_responsavel`),
  KEY `id_quadro` (`id_quadro`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_processo` (`id_processo`),
  CONSTRAINT `tarefas_ibfk_1` FOREIGN KEY (`id_responsavel`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tarefas_ibfk_2` FOREIGN KEY (`id_quadro`) REFERENCES `kanban_colunas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tarefas_ibfk_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tarefas_ibfk_processo` FOREIGN KEY (`id_processo`) REFERENCES `processos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: timeline
DROP TABLE IF EXISTS `timeline`;
CREATE TABLE `timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `processo_id` int(11) NOT NULL,
  `descricao` text NOT NULL,
  `visibilidade` enum('interno','cliente') DEFAULT 'interno',
  `data_evento` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `processo_id` (`processo_id`),
  CONSTRAINT `timeline_ibfk_1` FOREIGN KEY (`processo_id`) REFERENCES `processos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamentos`
--

CREATE TABLE `orcamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `descricao_servicos` text DEFAULT NULL,
  `logo_advogado` varchar(255) DEFAULT NULL,
  `validade_dias` int(11) DEFAULT 15,
  `status` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_aprovacao` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
