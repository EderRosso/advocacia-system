# Documentação Geral do Advocacia System (LexFlow)

Esta é a documentação abrangente do aplicativo **Advocacia System**, uma plataforma web de gestão jurídica e fluxo de trabalho projetada para digitalizar, organizar e automatizar as operações diárias de um escritório de advocacia.

---

## 🚀 1. Visão Geral e Arquitetura

O sistema é construído como uma aplicação web responsiva foca na facilidade de uso em múltiplos dispositivos.
*   **Back-end:** PHP (Padrão MVC e Integrações).
*   **Banco de Dados:** MySQL (Relacional, armazenando entidades interligadas).
*   **Front-end:** HTML5, CSS3, JavaScript.
*   **PWA (Progressive Web App):** Configurado com `manifest.json` e Service Worker (`sw.js`), permitindo que a aplicação seja "instalada" em smartphones e desktops, garantindo aparência de aplicativo nativo.

---

## 🧩 2. Módulos do Sistema

O sistema é modular, permitindo que administradores concedam ou bloqueiem acessos específicos para os usuários da equipe.

### 2.1. Dashboard Central e LexFlow Analytics
Painel de controle principal para tomada rápida de decisão:
*   **Cards de Resumo:** Indicadores essenciais sobre total de clientes, processos ativos, tarefas e documentos guardados.
*   **Visão Financeira:** Visualização dos honorários pendentes (com recurso de ocultação de valores visando a privacidade da tela perante terceiros).
*   **Próximos Compromissos:** Tabelas destacando "Audiências Próximas" e "Prazos a Vencer" (próximos 15-30 dias).
*   **Integração com Google Calendar:** Botão prático (ícone do Google) que exporta prazos e audiências diretamente para o calendário pessoal do usuário.
*   **LexFlow Analytics:** Uma tabela administrativa avançada que cruza o volume de tarefas e prazos em aberto com os advogados encarregados e calcula um "Nível de Sobrecarga" (Leve, Moderada, Alta/Crítica), vital na gestão da equipe.

### 2.2. Gestão de Clientes
*   **O que faz:** Cadastro detalhado com dados civis (CPF, RG, Data de Nascimento, Endereço, Contato).
*   **Relacionamento:** É o eixo do sistema, possibilitando o vínculo posterior de Processos, Honorários, Documentos e Tarefas daquele cliente específico.

### 2.3. LexFlow Core (Gestão de Processos)
*   **O que faz:** Cadastra cada lide (ação legal), número do processo, juízo/vara, parte contrária, além do status processual (Ativo, Arquivado, Suspenso, Encerrado).
*   **Linha do Tempo (Timeline):** Cada processo tem um histórico de eventos, podendo estes serem configurados como de visibilidade interna (só o escritório vê) ou visibilidade cliente (quando exposto no portal de acompanhamento).
*   **Checklists:** Subdivisão de processos em pequenas etapas rastreáveis. Ao concluir uma etapa da ação, arquiva-se no check.
*   **Acesso do Cliente:** Gera um "Token" único, habilitando que o próprio cliente consulte a situação de seu interesse sem intervenção do escritório.

### 2.4. Gestão do Tempo: Audiências e Prazos
*   **Audiências:** Monitora a data, horário, localização, os envolvidos (cliente e processo), marcando se estão aguardando realização, se já foram realizadas ou canceladas.
*   **Prazos processuais:** Controle com prazo fatal. Se vencer antes de concluído, o painel central alertará os responsáveis envolvidos.

### 2.5. Tarefas (Kanban)
*   **O que faz:** Um painel visual e interativo dividido em quadros (Tipicamente: Pendentes, Em Andamento e Concluídas).
*   **Relacionamento:** Vincula atividades não puramente processuais a usuários para o andamento do escritório.

### 2.6. Automação de Documentos (GED)
*   **O que faz:** Guarda arquivos (PDFs, imagens) em nuvem/servidor atrelados aos processos e clientes correspondentes. Essencial para abolir pastas físicas e localizar anexos rapidamente.

### 2.7. Financeiro (Honorários)
*   **O que faz:** Contabilidade primária baseada em contas a receber de clientes e processos. Controla tipo, forma de pagamento, e gerencia status da transação.

### 2.8. Assistente de Dados (Datajud / Tribunais)
*   **O que faz:** Um motor (api/scraper) focado em integrar o sistema aos tribunais (TJRS, TJSC, TRF4) e base do CNJ (Datajud). Ao aplicar o número do processo, ele traz informações consolidadas da justiça para evitar cadastro manual massivo.

### 2.9. Gestão de Usuários
*   **O que faz:** Central onde administradores adicionam colabores, resetam contas, monitoram o primeiro acesso dos advogados, e determinam (via permissões restritivas JSON) quais módulos o usuário final pode enxergar na plataforma.

---

## 🔎 3. Inteligência Artificial e Produtividade Embutida
*   **Sugestões Jurídicas Automáticas:** O banco prevê orientações (dicas de jurisprudência/agilização) baseadas no tipo da ação inserida. (Ex: Sugere pedir tutela em casos trabalhistas/alimentos, recolhimentos ou lista de proventos em divórcio).

## 💡 4. Resumo de Valor
O **Advocacia System** vai além do cadastro estático de clientes: ele atua como um sistema preditivo que **prevê a sobrecarga** das equipes (LexFlow Analytics), garante que prazos não sejam perdidos (alertas e Google Calendar), conecta todas as áreas do processo e ainda oferece portabilidade e responsividade total com sua infraestrutura de PWA.
