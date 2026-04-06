# ⚖️ Advocacia System (LexFlow)

Um sistema completo, modular e responsivo (PWA) de gerenciamento e workflow jurídico para escritórios de advocacia modernos. O Advocacia System foi desenhado para eliminar processos de papel, automatizar tarefas do dia a dia e fornecer análises vitais (Analytics) sobre a carga horária e honorários da sua equipe de advogados.

---

## 🚀 Principais Funcionalidades

O sistema é construído de forma modular para que administradores deleguem funções aos advogados com flexibilidade:

- **👥 Gestão de Clientes:** Cadastro e base de dados fundamental de todos os envolvidos no escritório.
- **📂 LexFlow Core (Processos):** Acompanhamento inteligente de processos com Linha do Tempo e "Organização Processual por Etapas" (Checklists processuais). Acesso seguro por *Token* permitindo criação de "Portal do Cliente" sem expor dados internos.
- **📅 Prazos e Audiências:** Gerenciamento rígido de prazos com cálculo de dias restantes, interface de contagem e botões integrados com o **Google Calendar**.
- **📋 Kanban de Tarefas:** Um quadro interativo estilo *Drag and Drop* para controle dinâmico da rotina interna do escritório (Pendentes, Em andamento, Concluídos). Crie tarefas que automaticamente geram etapas na ficha do Processo, unificando telas.
- **☁️ GED (Gestão Eletrônica de Documentos):** Armazenamento seguro de anexos, petições e laudos (.PDF, imagens, Word, Zip) amarrado com clientes e processos. O envio em nuvem integrado pode ser realizado até mesmo enquanto você estipula uma nova tarefa.
- **💰 Honorários:** Planejamento financeiro inteligente. Aceita pagamentos à vista e o módulo exclusivo de **Parcelamento Automático** — indicando a quantidade, o sistema calcula os vencimentos subsequentes automaticamente. Alerta crítico e visual de honorários vencendo e atrasados na página inicial.
- **📊 LexFlow Analytics:** Uma camada preditiva na Dashboard da direção, que calcula quem (advogado) está com a pior distribuição de prazos vencidos vs. tarefas e mostra em alerta o nível de "Sobrecarga" da equipe.
- **🤖 Sugestões e Ações Judiciais (Datajud):** Inteligência que propõe petições com base na classificação da demanda cadastrada.
- **📱 Progressive Web App (PWA):** Instale o sistema nativamente no celular ou desktop via "Adicionar à Tela Inicial", possibilitando a experiência sem barra de URL como os aplicativos mobile normais.

---

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP Vanilla (Estrutura em arquivos amigáveis e modulares) - PDO para segurança em injeção de dados.
- **Banco de Dados:** MySQL (Consultas otimizadas e relacionamento firme como chaves estrangeiras com CASCADE).
- **Frontend:** HTML5, CSS3 dinâmico com Design System minimalista, Javascript.
- **PWA:** Manifest (`manifest.json`) e Service Worker autônomo para cache estrutural (`sw.js`).
- **Scripts Externos:** FontAwesome (ícones), SortableJS (movimentação dos quadros de tarefas).

---

## 📁 Estrutura de Diretórios 

```text
c:\xampp\htdocs\advocacia-system\
├── assets/          # Ícones PWA, estilos dinâmicos CSS e eventuais imagens estáticas
├── config/          # Central de conexão com banco de dados (conexao.php)
├── includes/        # Cabeçalhos, rodapés e envio de e-mails via Mailer
├── modules/         # Módulos customizáveis (ex: "Assistente" LexFlow Analytics)
├── pages/           # Todo o núcleo do software interativo da Dashboard
│   ├── agenda/      # Planejador
│   ├── audiencias/  # Alertas e agendamentos com tribunal
│   ├── clientes/    # CRUD de pessoas / empresas
│   ├── documentos/  # Formulário para upload do GED
│   ├── honorarios/  # Caixa financeiro de sucumbências e parcelamentos
│   ├── prazos/      # Gerenciamento de prazo fatal com painel visual
│   ├── processos/   # Acesso as Linhas de Tempo, visibilidade de Token
│   ├── tarefas/     # Painel visual dos quadros dinâmicos Kanban
│   └── usuarios/    # Gestão da equipe (apenas admin)
├── sql/             # Banco de dados raiz para importação
├── uploads/         # Repositório de Nuvem de Segurança que guarda os anexos do GED (.PDF, DOCX)
├── README.md        # Esta documentação
├── andamento.php    # Acesso restrito para o portão principal do Cliente (acesso via token)
├── dashboard.php    # Sala de comando e Analytics central pós-login
└── index.php        # Gatekeeper / Roteador geral
```

---

## ⚙️ Instalação e Configuração (Uso Local)

Siga os passos para rodar o software ambiente Windows com XAMPP, ou equivalente:

### 1. Requisitos:
* **PHP >= 7.4** ou superior (testado fluidamente no PHP 8).
* **MySQL** ou **MariaDB**.
* Servidor Apache/Nginx (com `mod_rewrite` habilitado no Apache).

### 2. Configurando o Diretório
1. Clone este repositório ou cole a pasta `advocacia-system` em `C:\xampp\htdocs\`.
2. O sistema ficará acessível via _http://localhost/advocacia-system/_.

### 3. Banco de Dados
1. Abra o phpMyAdmin (_http://localhost/phpmyadmin_).
2. Crie um banco de dados chamado `advocacia_db` (ou similar) formatado em `utf8mb4_general_ci`.
3. Importe o script nativo contido na pasta em `sql/banco.sql`. Ele criará todas as tabelas, colunas, visões interligadas e regras restritas, além de aplicar o primeiro Administrador oficial.

### 4. Conexão do BD no PHP
1. Navegue até a pasta `/config/`.
2. Abra o arquivo `conexao.php` e preencha suas métricas seguras do PDO:
    - Host (Padrão: `localhost`)
    - Nome do Banco (Nome escolhido acima)
    - Usuário (XAMPP padrão: `root`)
    - Senha (XAMPP padrão: `vazio`)

### 5. Primeiro Acesso
- O banco de dados já inicializa provendo um usuário oficial de acesso para você testar tudo rapidamente:
  - **E-mail:** `admin@advocacia.com`
  - **Senha Criptografada Padrão:** Verificar a hash do arquivo ou utilizar a senha primária estabelecida no momento da criação pela equipe de segurança.

---

## 💡 Customizações Automáticas Destacadas

* Se o servidor rejeitar envio de tamanho alto de arquivo nos Honorários/Petições, você talvez necessite checar as travas de `upload_max_filesize` em seu `php.ini`.
* Recomenda-se para instalação num Servidor Hospedado real o uso de um protocolo HTTPS rigoroso. Certificados SSL ativam em paralelo a viabilidade da manifestação e ativadores do nosso aplicativo PWA.

*(Desenvolvido originalmente para otimização jurídica e fluxo digital.)*
