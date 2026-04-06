<?php
$page_title = 'Cadastro de Processo';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';

$id = $_GET['id'] ?? null;
$processo = null;
$erro = '';
$sucesso = '';

/** GET CLIENTES & ADVOGADOS */
$clientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC")->fetchAll();
$advogados = $pdo->query("SELECT id, nome FROM usuarios WHERE status='ativo' ORDER BY nome ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $numero_processo = trim($_POST['numero_processo']);
    $id_cliente = $_POST['id_cliente'];
    $tipo_acao = trim($_POST['tipo_acao']);
    $vara_juizo = trim($_POST['vara_juizo']);
    $comarca = trim($_POST['comarca']);
    $parte_contraria = trim($_POST['parte_contraria']);
    $id_advogado = !empty($_POST['id_advogado']) ? $_POST['id_advogado'] : null;
    $status = $_POST['status'];
    $data_distribuicao = !empty($_POST['data_distribuicao']) ? $_POST['data_distribuicao'] : null;
    $observacoes = trim($_POST['observacoes']);

    if (empty($numero_processo) || empty($id_cliente)) {
        $erro = 'Número do processo e cliente são obrigatórios.';
    } else {
        try {
            if ($id) {
                // Atualizar
                $stmt = $pdo->prepare("UPDATE processos SET numero_processo=?, id_cliente=?, tipo_acao=?, vara_juizo=?, comarca=?, parte_contraria=?, id_advogado=?, status=?, data_distribuicao=?, observacoes=? WHERE id=?");
                $stmt->execute([$numero_processo, $id_cliente, $tipo_acao, $vara_juizo, $comarca, $parte_contraria, $id_advogado, $status, $data_distribuicao, $observacoes, $id]);
                $sucesso = 'Ficha do Processo atualizada.';
                
                // HOOK LEXFLOW
                require_once __DIR__ . '/../../modules/assistente/assistente.php';
                lexflow_registrar_timeline($pdo, $id, "Ficha do processo atualizada (dados alterados).");
            } else {
                // Inserir
                $token_acesso = md5(uniqid(rand(), true)); // Link Mágico do Cliente
                $stmt = $pdo->prepare("INSERT INTO processos (numero_processo, id_cliente, tipo_acao, vara_juizo, comarca, parte_contraria, id_advogado, status, data_distribuicao, observacoes, token_acesso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$numero_processo, $id_cliente, $tipo_acao, $vara_juizo, $comarca, $parte_contraria, $id_advogado, $status, $data_distribuicao, $observacoes, $token_acesso]);
                $sucesso = 'Processo cadastrado com sucesso. Assistente LexFlow notificado.';
                $id = $pdo->lastInsertId();
                
                // --- HOOK DO LEXFLOW ---
                require_once __DIR__ . '/../../modules/assistente/assistente.php';
                lexflow_gerar_checklist_se_vazio($pdo, $id, $tipo_acao);
                lexflow_gerar_prazos_automaticos($pdo, $id, $tipo_acao);
                lexflow_registrar_timeline($pdo, $id, "Processo cadastrado no sistema.");
                // -----------------------
            }
        } catch(PDOException $e) {
            if($e->getCode() == 23000) {
                $erro = "Já existe um processo com este número.";
            } else {
                $erro = "Erro ao salvar: " . $e->getMessage();
            }
        }
    }
}

if ($id) {
    $stmt = $pdo->prepare("SELECT p.*, c.nome as cliente_nome, c.telefone as cliente_telefone FROM processos p LEFT JOIN clientes c ON p.id_cliente = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    $processo = $stmt->fetch();
    if($processo) {
        $page_title = 'Editar Processo - ' . htmlspecialchars($processo['numero_processo']);
    }
}
?>

<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-folder-open"></i> Informações do Processo</h3>
        <a href="index.php" class="btn btn-sm btn-blue">Voltar</a>
    </div>
    <div class="panel-body">
        <?php if($erro): ?> <div class="alert alert-danger"><?php echo $erro; ?></div> <?php endif; ?>
        <?php if($sucesso): ?> <div class="alert alert-success"><?php echo $sucesso; ?></div> <?php endif; ?>
        
        <?php if($id && !empty($processo['token_acesso'])): 
            $linkRastreio = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . BASE_URL . 'andamento.php?token=' . $processo['token_acesso'];
            
            $nomeClienteArr = explode(' ', $processo['cliente_nome'] ?? 'Cliente');
            $primeiroNome = $nomeClienteArr[0];
            $msgWpp = "Olá, " . $primeiroNome . ". Seu processo nº " . $processo['numero_processo'] . " foi cadastrado em nosso sistema e já começamos a trabalhar nele! Você pode acompanhar todas as movimentações e atualizações online, em tempo real, através deste link exclusivo em nosso Portal de Andamento:\n\n" . $linkRastreio;
            
            // Tratamento do telefone para o WhatsApp Desktop/Web Mobile
            $telWpp = preg_replace("/[^0-9]/", "", $processo['cliente_telefone'] ?? '');
            $urlWpp = "https://wa.me/";
            if(!empty($telWpp)){
                if(substr($telWpp, 0, 2) !== '55' && strlen($telWpp) >= 10){
                    $telWpp = '55' . $telWpp;
                }
                $urlWpp .= $telWpp;
            }
            $urlWpp .= "?text=" . rawurlencode($msgWpp);
        ?>
            <div class="alert alert-info" style="margin-bottom: 20px; background: #e3f2fd; border-left: 4px solid #0D8ABC;">
                <strong><i class="fas fa-link"></i> Link Trilha do Cliente:</strong><br>
                <small>Envie este link para o cliente acompanhar o processo (sem login):</small><br>
                <div style="display: flex; gap: 10px; margin-top: 10px; align-items: stretch; flex-wrap: wrap;">
                    <input type="text" class="form-control" style="flex: 1; cursor: pointer; background: #fff; min-width: 200px;" readonly value="<?php echo $linkRastreio; ?>" onclick="this.select(); document.execCommand('copy'); alert('Link copiado!');">
                    
                    <a href="<?php echo $urlWpp; ?>" target="_blank" class="btn" style="background-color: #25D366; color: white; display:flex; align-items:center; justify-content:center;">
                        <i class="fab fa-whatsapp" style="margin-right: 5px;"></i> Enviar Boas-Vindas
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <form action="form.php<?php echo $id ? "?id=$id" : ""; ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Número do Processo *</label>
                    <input type="text" name="numero_processo" class="form-control" required value="<?php echo htmlspecialchars($processo['numero_processo'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Cliente (Vincular) *</label>
                    <select name="id_cliente" class="form-control" required>
                        <option value="">-- Selecione --</option>
                        <?php foreach($clientes as $cli): ?>
                            <option value="<?php echo $cli['id']; ?>" <?php echo (isset($processo['id_cliente']) && $processo['id_cliente'] == $cli['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cli['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipo de Ação</label>
                    <input type="text" name="tipo_acao" class="form-control" placeholder="Ação Trabalhista, Divórcio..." value="<?php echo htmlspecialchars($processo['tipo_acao'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Vara/Juízo</label>
                    <input type="text" name="vara_juizo" class="form-control" placeholder="1ª Vara Cível, TRT..." value="<?php echo htmlspecialchars($processo['vara_juizo'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Comarca</label>
                    <input type="text" name="comarca" class="form-control" placeholder="São Paulo/SP" value="<?php echo htmlspecialchars($processo['comarca'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Parte Contrária</label>
                    <input type="text" name="parte_contraria" class="form-control" value="<?php echo htmlspecialchars($processo['parte_contraria'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Advogado Responsável</label>
                    <select name="id_advogado" class="form-control">
                        <option value="">-- Selecione --</option>
                        <?php foreach($advogados as $adv): ?>
                            <option value="<?php echo $adv['id']; ?>" <?php echo (isset($processo['id_advogado']) && $processo['id_advogado'] == $adv['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($adv['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Data de Distribuição</label>
                    <input type="date" name="data_distribuicao" class="form-control" value="<?php echo htmlspecialchars($processo['data_distribuicao'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="ativo" <?php echo (isset($processo['status']) && $processo['status'] == 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                        <option value="suspenso" <?php echo (isset($processo['status']) && $processo['status'] == 'suspenso') ? 'selected' : ''; ?>>Suspenso</option>
                        <option value="arquivado" <?php echo (isset($processo['status']) && $processo['status'] == 'arquivado') ? 'selected' : ''; ?>>Arquivado</option>
                        <option value="encerrado" <?php echo (isset($processo['status']) && $processo['status'] == 'encerrado') ? 'selected' : ''; ?>>Encerrado</option>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <label>Observações</label>
                    <textarea name="observacoes" class="form-control"><?php echo htmlspecialchars($processo['observacoes'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Processo</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
