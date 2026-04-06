<?php
$page_title = 'Cadastro de Tarefa';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';

$id = $_GET['id'] ?? null; $registro = null; $erro = ''; $sucesso = '';
$usuarios = $pdo->query("SELECT id, nome FROM usuarios WHERE status = 'ativo'")->fetchAll();
$colunas = $pdo->query("SELECT id, titulo FROM kanban_colunas ORDER BY ordem ASC")->fetchAll();
$clientes_lista = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC")->fetchAll();
$processos_lista = $pdo->query("SELECT p.id, p.numero_processo, c.nome as cliente_nome FROM processos p JOIN clientes c ON p.id_cliente = c.id ORDER BY c.nome ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null; $titulo = trim($_POST['titulo']); $descricao = trim($_POST['descricao']);
    $id_responsavel = !empty($_POST['id_responsavel']) ? $_POST['id_responsavel'] : null;
    $prioridade = $_POST['prioridade']; 
    $id_quadro = !empty($_POST['id_quadro']) ? $_POST['id_quadro'] : null;
    $data_inicio = !empty($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
    $data_final = !empty($_POST['data_final']) ? $_POST['data_final'] : null;
    $observacoes = trim($_POST['observacoes']);
    
    // Novos campos
    $id_cliente = !empty($_POST['id_cliente']) ? $_POST['id_cliente'] : null;
    $id_processo = !empty($_POST['id_processo']) ? $_POST['id_processo'] : null;
    $info_cliente = trim($_POST['info_cliente'] ?? '');
    
    $nova_etapa = trim($_POST['nova_etapa'] ?? '');
    $novo_item = trim($_POST['novo_item'] ?? '');

    if (empty($titulo) || empty($id_quadro)) { $erro = 'Título e Quadro são obrigatórios.'; } else {
        try {
            // Pegar info cliente antigo para saber se mudou e lançar na Timeline
            $info_cliente_antigo = '';
            if ($id) {
                $stmt_old = $pdo->prepare("SELECT info_cliente FROM tarefas WHERE id = ?");
                $stmt_old->execute([$id]);
                $info_cliente_antigo = $stmt_old->fetchColumn();
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE tarefas SET titulo=?, descricao=?, id_responsavel=?, prioridade=?, id_quadro=?, data_inicio=?, data_final=?, observacoes=?, id_cliente=?, id_processo=?, info_cliente=? WHERE id=?");
                $stmt->execute([$titulo, $descricao, $id_responsavel, $prioridade, $id_quadro, $data_inicio, $data_final, $observacoes, $id_cliente, $id_processo, $info_cliente, $id]);
                $sucesso = 'Tarefa atualizada.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO tarefas (titulo, descricao, id_responsavel, prioridade, id_quadro, data_inicio, data_final, observacoes, id_cliente, id_processo, info_cliente) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titulo, $descricao, $id_responsavel, $prioridade, $id_quadro, $data_inicio, $data_final, $observacoes, $id_cliente, $id_processo, $info_cliente]);
                $sucesso = 'Tarefa cadastrada.'; $id = $pdo->lastInsertId();
            }
            
            // Lógica de Linha do Tempo (Processo -> Timeline)
            if (!empty($info_cliente) && $info_cliente !== $info_cliente_antigo && $id_processo) {
                // Verificar se é a PRIMEIRA atualização do cliente nesse processo
                $stmt_chk_tl = $pdo->prepare("SELECT COUNT(*) FROM timeline WHERE processo_id = ? AND visibilidade = 'cliente'");
                $stmt_chk_tl->execute([$id_processo]);
                $is_primeira_att = ($stmt_chk_tl->fetchColumn() == 0);

                $msg_timeline = "Informação Repassada: $info_cliente";
                $pdo->prepare("INSERT INTO timeline (processo_id, descricao, visibilidade) VALUES (?, ?, 'cliente')")->execute([$id_processo, $msg_timeline]);
                $sucesso .= " Atualização enviada para a Trilha do Cliente.";

                if ($is_primeira_att) {
                    // Pega dados para o email
                    $stmt_dados_email = $pdo->prepare("SELECT p.numero_processo, p.token_acesso, c.nome, c.email FROM processos p JOIN clientes c ON p.id_cliente = c.id WHERE p.id = ?");
                    $stmt_dados_email->execute([$id_processo]);
                    $dados_email = $stmt_dados_email->fetch();

                    if ($dados_email && !empty($dados_email['email']) && !empty($dados_email['token_acesso'])) {
                        require_once __DIR__ . '/../../includes/mailer.php';
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                        $link_rastreio = $protocol . $_SERVER['HTTP_HOST'] . BASE_URL . 'andamento.php?token=' . $dados_email['token_acesso'];
                        
                        $res_email = enviar_email_link_rastreio($dados_email['email'], explode(' ', $dados_email['nome'])[0], $dados_email['numero_processo'], $link_rastreio);
                        if ($res_email === true) {
                            $sucesso .= " 📧 E-mail com o Portal enviado ao cliente!";
                        } else {
                            $erro .= " (O envio do email falhou: " . $res_email . ")";
                        }
                    }
                }
            }

            // Lógica de Upload de Documento na Etapa
            $caminho_final = null;
            if (isset($_FILES['arquivo_etapa']) && $_FILES['arquivo_etapa']['error'] === UPLOAD_ERR_OK) {
                $dir_uploads = __DIR__ . '/../../uploads/documentos/';
                if (!is_dir($dir_uploads)) { @mkdir($dir_uploads, 0755, true); }
                $ext = strtolower(pathinfo($_FILES['arquivo_etapa']['name'], PATHINFO_EXTENSION));
                $arquivos_permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
                if (in_array($ext, $arquivos_permitidos)) {
                    $novoNome = uniqid('doc_tarefa_') . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['arquivo_etapa']['tmp_name'], $dir_uploads . $novoNome)) {
                        $caminho_final = 'uploads/documentos/' . $novoNome;
                    } else {
                        $erro = 'Falha ao anexar o documento físico.';
                    }
                } else {
                    $erro = 'Formato Recusado no anexo! Envie PDF, Word ou Imagens.';
                }
            }

            // Lógica de Criar Checklist/Etapa (Opcional)
            if (empty($erro) && $id_processo && !empty($nova_etapa) && !empty($novo_item)) {
                $pdo->prepare("INSERT INTO checklists (processo_id, item, etapa) VALUES (?, ?, ?)")->execute([$id_processo, $novo_item, $nova_etapa]);
                
                if ($caminho_final) {
                    $titulo_doc = "Doc Tarefa: " . $novo_item;
                    $pdo->prepare("INSERT INTO documentos (titulo, id_cliente, id_processo, tipo_documento, descricao, caminho_arquivo) VALUES (?, ?, ?, ?, ?, ?)")
                        ->execute([$titulo_doc, $id_cliente, $id_processo, $nova_etapa, "Documento anexado na criação da tarefa/etapa.", $caminho_final]);
                    $sucesso .= " Arquivo anexado ao GED do processo.";
                }

                require_once __DIR__ . '/../../modules/assistente/assistente.php';
                $msg_time = "Nova etapa/documento registrado(a) pela Tarefa: $nova_etapa -> $novo_item." . ($caminho_final ? " [Documento Anexado no Sistema]" : "");
                lexflow_registrar_timeline($pdo, $id_processo, $msg_time);
                $sucesso .= " Etapa ($nova_etapa) adicionada ao Processo.";
            }

        } catch(PDOException $e) { $erro = "Erro: " . $e->getMessage(); }
    }
}
if ($id) { $stmt = $pdo->prepare("SELECT * FROM tarefas WHERE id = ?"); $stmt->execute([$id]); $registro = $stmt->fetch(); }
?>
<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-tasks"></i> Detalhes da Tarefa</h3><a href="index.php" class="btn btn-sm btn-blue">Voltar ao Quadro</a></div>
    <div class="panel-body">
        <?php if($erro): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
        <?php if($sucesso): ?><div class="alert alert-success"><?php echo $sucesso; ?></div><?php endif; ?>
        <form action="form.php<?php echo $id ? "?id=$id" : ""; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-grid">
                <div class="form-group full-width"><label>Título *</label><input type="text" name="titulo" class="form-control" required value="<?php echo htmlspecialchars($registro['titulo'] ?? ''); ?>"></div>
                <div class="form-group full-width"><label>Descrição</label><textarea name="descricao" class="form-control"><?php echo htmlspecialchars($registro['descricao'] ?? ''); ?></textarea></div>
                <div class="form-group">
                    <label>Quadro (Status) *</label>
                    <select name="id_quadro" class="form-control" required>
                        <option value="">-- Selecione --</option>
                        <?php foreach($colunas as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo (isset($registro['id_quadro']) && $registro['id_quadro'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['titulo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Responsável</label><select name="id_responsavel" class="form-control"><option value="">-- Sem Atribuição --</option><?php foreach($usuarios as $u): ?><option value="<?php echo $u['id']; ?>" <?php echo (isset($registro['id_responsavel']) && $registro['id_responsavel'] == $u['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['nome']); ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Prioridade</label><select name="prioridade" class="form-control"><option value="baixa" <?php echo (isset($registro['prioridade']) && $registro['prioridade'] == 'baixa') ? 'selected' : ''; ?>>Baixa</option><option value="media" <?php echo (!isset($registro['prioridade']) || $registro['prioridade'] == 'media') ? 'selected' : ''; ?>>Média</option><option value="alta" <?php echo (isset($registro['prioridade']) && $registro['prioridade'] == 'alta') ? 'selected' : ''; ?>>Alta</option></select></div>
                
                <div class="form-group"><label>Data Início</label><input type="date" name="data_inicio" class="form-control" value="<?php echo $registro['data_inicio'] ?? ''; ?>"></div>
                <div class="form-group"><label>Data Final</label><input type="date" name="data_final" class="form-control" value="<?php echo $registro['data_final'] ?? ''; ?>"></div>
                
                <h4 style="grid-column: 1 / -1; margin-top: 15px; border-bottom: 2px solid #eee; padding-bottom: 5px; color: var(--primary-color);"><i class="fas fa-link"></i> Vínculos (Cliente / Processo)</h4>
                
                <div class="form-group">
                    <label>Atrelar Cliente</label>
                    <select name="id_cliente" class="form-control">
                        <option value="">-- Selecione o Cliente --</option>
                        <?php foreach($clientes_lista as $cli): ?>
                            <option value="<?php echo $cli['id']; ?>" <?php echo (isset($registro['id_cliente']) && $registro['id_cliente'] == $cli['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cli['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Atrelar Processo (Para criar Timeline)</label>
                    <select name="id_processo" id="id_processo_select" class="form-control" onchange="toggleEtapaFields()">
                        <option value="">-- Selecione o Processo --</option>
                        <?php foreach($processos_lista as $proc): ?>
                            <option value="<?php echo $proc['id']; ?>" <?php echo (isset($registro['id_processo']) && $registro['id_processo'] == $proc['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($proc['cliente_nome'] . ' - Proc: ' . $proc['numero_processo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Novos Campos: Planejar Etapa -->
                <div id="div_etapa_processo" style="display: none; grid-column: 1 / -1; background: #fcfcfc; padding: 15px; border-radius: 5px; border-left: 4px solid var(--success); margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h5 style="margin-bottom: 10px; font-size: 13px; color: var(--primary-color);"><i class="fas fa-plus-circle"></i> Criar Etapa / Documento no Processo (Opcional)</h5>
                    <p style="font-size: 11px; color: #666; margin-bottom: 10px; margin-top: 0;">Preencha os campos abaixo se quiser que esta tarefa crie automaticamente um item na <strong>Organização Processual por Etapas</strong> lá no processo.</p>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 120px;">
                            <label style="font-size: 11px; color: #888; font-weight:600;">Etapa do Processo</label>
                            <input type="text" name="nova_etapa" class="form-control" style="font-size: 13px; padding: 8px;" placeholder="Ex: Fase Recursal, Provas...">
                        </div>
                        <div style="flex: 2; min-width: 180px;">
                            <label style="font-size: 11px; color: #888; font-weight:600;">Qual o Documento/Tarefa para a Trilha?</label>
                            <input type="text" name="novo_item" class="form-control" style="font-size: 13px; padding: 8px;" placeholder="Ex: Apresentar Agravo, Testemunha...">
                        </div>
                    </div>
                    <div style="margin-top: 15px; background: #fff; padding: 10px; border-radius: 4px; border: 1px dashed #ccc;">
                        <label style="font-size: 11px; color: var(--primary-color); font-weight:600;"><i class="fas fa-paperclip"></i> Anexar Arquivo (Opcional)</label>
                        <input type="file" name="arquivo_etapa" id="arquivo_etapa_input" class="form-control" style="font-size: 13px; padding: 6px;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.rar">
                        <small style="color:#aaa; font-size:11px; display:block; margin-top:4px;">O arquivo subirá automaticamente para o "Documentos/GED" do processo associado.</small>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Informações passadas para o cliente (Feedbacks / Andamentos)<br><small class="text-muted">Descrever aqui o que foi passado ao cliente. Ao salvar com um Processo atrelado, será automaticamente gerado um evento na LexFlow Timeline do processo.</small></label>
                    <textarea name="info_cliente" class="form-control"><?php echo htmlspecialchars($registro['info_cliente'] ?? ''); ?></textarea>
                </div>

                <div class="form-group full-width"><label>Observações Internas (Só para equipe)</label><textarea name="observacoes" class="form-control"><?php echo htmlspecialchars($registro['observacoes'] ?? ''); ?></textarea></div>
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button></div>
        </form>
    </div>
</div>

<script>
function toggleEtapaFields() {
    const sel = document.getElementById('id_processo_select');
    const div = document.getElementById('div_etapa_processo');
    if(sel && sel.value !== '') {
        div.style.display = 'block';
    } else {
        div.style.display = 'none';
        // Limpar os campos quando ocultar para não enviar sujeira caso mude de ideia
        document.querySelector('input[name="nova_etapa"]').value = '';
        document.querySelector('input[name="novo_item"]').value = '';
        const arqInput = document.getElementById('arquivo_etapa_input');
        if (arqInput) arqInput.value = '';
    }
}
// Chamar ao carregar a página para garantir que mostra se já houver processo atrelado
document.addEventListener('DOMContentLoaded', toggleEtapaFields);
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
