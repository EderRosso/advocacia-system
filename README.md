## ⚖️ LexFlow — Sistema de Gestão Jurídica Inteligente

O **LexFlow** é uma plataforma completa de gestão e automação para escritórios de advocacia modernos. Desenvolvido com foco em **produtividade, controle processual e inteligência operacional**, o sistema elimina fluxos manuais, centraliza informações e oferece suporte estratégico à tomada de decisão.

> 💡 Ideal para escritórios que desejam escalar operação sem perder controle.

---

## 🚀 Visão Geral

O LexFlow integra **gestão jurídica, financeira e operacional** em um único ambiente, utilizando uma arquitetura modular e expansível.

Principais objetivos do sistema:

* Reduzir tarefas operacionais repetitivas
* Centralizar dados jurídicos e administrativos
* Automatizar fluxos de trabalho
* Fornecer insights estratégicos (Analytics)
* Melhorar a comunicação com clientes

---

## 🧩 Funcionalidades Principais

### 👥 Gestão de Clientes

* Cadastro completo de pessoas físicas e jurídicas
* Histórico centralizado por cliente
* Relacionamento direto com processos, documentos e honorários

---

### 📂 Gestão de Processos (LexFlow Core)

* Linha do tempo processual completa
* Organização por etapas (checklists jurídicos)
* Controle de movimentações
* Acesso externo via **token seguro (Portal do Cliente)**

---

### 📅 Prazos e Audiências

* Controle rigoroso de prazos processuais
* Contagem automática de dias restantes
* Alertas visuais de urgência
* Integração com **Google Calendar**

---

### 📋 Kanban de Tarefas

* Gestão visual de tarefas (*Drag and Drop*)
* Status: Pendentes, Em andamento, Concluídas
* Integração com processos (tarefas viram etapas automaticamente)

---

### ☁️ GED — Gestão Eletrônica de Documentos

* Upload e armazenamento seguro de arquivos
* Suporte a múltiplos formatos (PDF, DOCX, imagens, ZIP)
* Associação com clientes e processos
* Estrutura organizada para consultas rápidas

---

### 💰 Gestão de Honorários

* Controle financeiro completo
* Parcelamento automático com cálculo de vencimentos
* Alertas de inadimplência
* Visualização consolidada na dashboard

---

### 📊 LexFlow Analytics

* Monitoramento de produtividade da equipe
* Identificação de sobrecarga operacional
* Indicadores estratégicos para tomada de decisão
* Visão gerencial centralizada

---

### 🤖 Inteligência Jurídica (DataJud)

* Sugestão de ações judiciais com base na demanda
* Apoio na elaboração de petições
* Integração com dados públicos (DataJud)

---

### 📱 Progressive Web App (PWA)

* Instalação como aplicativo no celular ou desktop
* Funcionamento offline parcial (cache via Service Worker)
* Experiência semelhante a apps nativos

---

## 🛠️ Stack Tecnológica

### Backend

* PHP (Vanilla, arquitetura modular)
* PDO (Proteção contra SQL Injection)

### Banco de Dados

* MySQL / MariaDB
* Modelagem relacional com integridade referencial (FK + CASCADE)

### Frontend

* HTML5
* CSS3 (Design System próprio)
* JavaScript (interações dinâmicas)

### Recursos adicionais

* PWA (manifest + service worker)
* FontAwesome (ícones)
* SortableJS (Kanban drag-and-drop)

---

## 📁 Estrutura do Projeto

```bash
advocacia-system/
├── assets/        # CSS, ícones PWA, imagens
├── config/        # Conexão com banco (PDO)
├── includes/      # Componentes reutilizáveis (header, footer, mailer)
├── modules/       # Funcionalidades modulares (Analytics, Assistente)
├── pages/         # Núcleo do sistema
│   ├── agenda/     # Agenda de compromissos
│   ├── audiencias/ # Audiências
│   ├── clientes/   # Clientes
│   ├── documentos/ # Documentos
│   ├── honorarios/ # Honorários
│   ├── prazos/     # Prazos
│   ├── processos/   # Processos
│   ├── tarefas/    # Tarefas
│   └── usuarios/   # Usuários
├── sql/           # Script do banco de dados
├── uploads/       # Armazenamento de arquivos
├── andamento.php  # Portal do cliente (via token)
├── dashboard.php  # Painel principal
├── index.php      # Roteador
└── README.md
```

---

## ⚙️ Instalação (Ambiente Local)

### 🔧 Requisitos

* PHP 7.4+ (recomendado PHP 8+)
* MySQL ou MariaDB
* Apache ou Nginx

---

### 📌 Passo a Passo

#### 1. Clonar ou copiar projeto

```bash
C:\xampp\htdocs\advocacia-system
```

Acesse:

```
http://localhost/advocacia-system/
```

---

#### 2. Banco de Dados

1. Acesse o phpMyAdmin
2. Crie o banco:

```
advocacia_db
```

3. Importe:

```
/sql/banco.sql
```

---

#### 3. Configuração de conexão

Arquivo:

```
/config/conexao.php
```

Exemplo:

```php
$host = 'localhost';
$db   = 'advocacia_db';
$user = 'root';
$pass = '';
```

---

#### 4. Acesso inicial

* Email: `admin@advocacia.com`
* Senha: definida no banco (ver hash ou redefinir)

---

## 🔐 Segurança

* Uso de PDO com prepared statements
* Controle de acesso por usuário
* Tokens seguros para acesso externo
* Recomendado uso de HTTPS em produção

---

## ⚠️ Observações Técnicas

* Ajustar `upload_max_filesize` no `php.ini` para uploads maiores
* Ativar `mod_rewrite` (Apache)
* Configurar SSL em ambiente produtivo para funcionamento completo do PWA

---

## 📈 Possíveis Evoluções

* Integração completa com tribunais (eproc, PJe, etc.)
* Sistema de notificações em tempo real
* API REST para integração externa
* Aplicativo mobile nativo
* IA para análise preditiva de processos

---

## 📄 Licença

Projeto de uso educacional/comercial sob análise. Definir licença conforme estratégia de distribuição.

---

## 👨‍💻 Autor

Desenvolvido por **Éder Rosso**
🔗 GitHub: [https://github.com/EderRosso](https://github.com/EderRosso)

---

## ⭐ Diferencial do Projeto

O LexFlow não é apenas um sistema jurídico — é uma **plataforma de gestão estratégica**, focada em transformar escritórios tradicionais em operações digitais eficientes.

---


