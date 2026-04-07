<?php
$page_title = 'Cadastro de Orçamento/Proposta';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';

$id = $_GET['id'] ?? null;
$registro = null;
$erro = '';
$sucesso = '';
$clientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $id_cliente = $_POST['id_cliente'];
    $titulo = trim($_POST['titulo']);
    
    // Tratamento de conversão de moeda
    $valor_bruto = trim($_POST['valor']);
    if (strpos($valor_bruto, ',') !== false) {
        $valor = str_replace('.', '', $valor_bruto);
        $valor = (float)str_replace(',', '.', $valor);
    } else {
        $valor = (float)$valor_bruto;
    }
    
    $validade_dias = (int)$_POST['validade_dias'];
    $descricao_servicos = trim($_POST['descricao_servicos']);
    $status = $_POST['status'] ?? 'pendente';
    
    $caminho_logo_1 = $registro['logo_1'] ?? null;
    $caminho_logo_2 = $registro['logo_2'] ?? null;

    if ($id) {
        $stmt_l = $pdo->prepare("SELECT logo_1, logo_2 FROM orcamentos WHERE id=?");
        $stmt_l->execute([$id]);
        $row_l = $stmt_l->fetch();
        if ($row_l) {
            $caminho_logo_1 = $row_l['logo_1'];
            $caminho_logo_2 = $row_l['logo_2'];
        }
    }

    // Tratamento Upload das duas Logos
    $dir_uploads = __DIR__ . '/../../uploads/logos/';
    if (!is_dir($dir_uploads)) { @mkdir($dir_uploads, 0755, true); }

    if (isset($_FILES['logo_1']) && $_FILES['logo_1']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo_1']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $novoNome = uniqid('logo1_') . '.' . $ext;
            if (move_uploaded_file($_FILES['logo_1']['tmp_name'], $dir_uploads . $novoNome)) {
                if ($caminho_logo_1 && file_exists(__DIR__ . '/../../' . $caminho_logo_1)) { @unlink(__DIR__ . '/../../' . $caminho_logo_1); }
                $caminho_logo_1 = 'uploads/logos/' . $novoNome;
            }
        } else { $erro = "Formato de logo 1 inválido! Apenas PNG e JPG."; }
    }
    
    if (isset($_FILES['logo_2']) && $_FILES['logo_2']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo_2']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $novoNome = uniqid('logo2_') . '.' . $ext;
            if (move_uploaded_file($_FILES['logo_2']['tmp_name'], $dir_uploads . $novoNome)) {
                if ($caminho_logo_2 && file_exists(__DIR__ . '/../../' . $caminho_logo_2)) { @unlink(__DIR__ . '/../../' . $caminho_logo_2); }
                $caminho_logo_2 = 'uploads/logos/' . $novoNome;
            }
        } else { $erro = "Formato de logo 2 inválido! Apenas PNG e JPG."; }
    }

    $ass1_nome = $_POST['assinatura_1_nome'] ?? 'Daiane de Oliveira Soares';
    $ass1_oab = $_POST['assinatura_1_oab'] ?? 'OAB/RS 129238';
    $ass2_nome = $_POST['assinatura_2_nome'] ?? 'Hérica Patrícia Matos de Morais';
    $ass2_oab = $_POST['assinatura_2_oab'] ?? 'OAB/RS 108.077';

    if (empty($id_cliente) || empty($titulo) || empty($valor)) {
        $erro = 'Cliente, título e valor são obrigatórios.';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE orcamentos SET id_cliente=?, titulo=?, valor=?, descricao_servicos=?, logo_1=?, logo_2=?, validade_dias=?, assinatura_1_nome=?, assinatura_1_oab=?, assinatura_2_nome=?, assinatura_2_oab=?, status=? WHERE id=?");
                $stmt->execute([$id_cliente, $titulo, $valor, $descricao_servicos, $caminho_logo_1, $caminho_logo_2, $validade_dias, $ass1_nome, $ass1_oab, $ass2_nome, $ass2_oab, $status, $id]);
                $sucesso = 'Orçamento/Proposta atualizada com sucesso.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO orcamentos (id_cliente, titulo, valor, descricao_servicos, logo_1, logo_2, validade_dias, assinatura_1_nome, assinatura_1_oab, assinatura_2_nome, assinatura_2_oab, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_cliente, $titulo, $valor, $descricao_servicos, $caminho_logo_1, $caminho_logo_2, $validade_dias, $ass1_nome, $ass1_oab, $ass2_nome, $ass2_oab, $status]);
                $sucesso = 'Orçamento criado com sucesso.';
                $id = $pdo->lastInsertId();
            }
        } catch(PDOException $e) { 
            $erro = "Erro: " . $e->getMessage(); 
        }
    }
}

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM orcamentos WHERE id = ?");
    $stmt->execute([$id]);
    $registro = $stmt->fetch();
}
?>

<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-file-signature"></i> <?php echo $id ? 'Editar' : 'Nova'; ?> Proposta/Orçamento</h3>
        <a href="index.php" class="btn btn-sm btn-blue">Voltar</a>
    </div>
    <div class="panel-body">
        <?php if($erro): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
        <?php if($sucesso): ?><div class="alert alert-success"><?php echo $sucesso; ?></div><?php endif; ?>
        
        <form action="form.php<?php echo $id ? "?id=$id" : ""; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Título da Proposta *</label>
                    <input type="text" name="titulo" class="form-control" required placeholder="Ex: Proposta de Prestação de Serviços - Inventário" value="<?php echo htmlspecialchars($registro['titulo'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Cliente Associado *</label>
                    <select name="id_cliente" class="form-control" required>
                        <option value="">-- Selecione o Cliente --</option>
                        <?php foreach($clientes as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo (isset($registro['id_cliente']) && $registro['id_cliente'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Valor dos Honorários (R$) *</label>
                    <input type="text" name="valor" class="form-control" placeholder="0.00" required value="<?php echo htmlspecialchars($registro['valor'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Validade da Proposta (Dias)</label>
                    <input type="number" name="validade_dias" class="form-control" value="<?php echo htmlspecialchars($registro['validade_dias'] ?? '15'); ?>">
                </div>

                <div class="form-group">
                    <label>Status Atual *</label>
                    <select name="status" class="form-control" required>
                        <option value="pendente" <?php echo (isset($registro['status']) && $registro['status'] == 'pendente') ? 'selected' : ''; ?>>Pendente (Aguardando Retorno)</option>
                        <option value="aprovado" <?php echo (isset($registro['status']) && $registro['status'] == 'aprovado') ? 'selected' : ''; ?>>Aprovado</option>
                        <option value="rejeitado" <?php echo (isset($registro['status']) && $registro['status'] == 'rejeitado') ? 'selected' : ''; ?>>Rejeitado</option>
                    </select>
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1; display:flex; gap:20px; background:#f9f9f9; padding: 15px; border-radius:4px; border:1px solid #ddd;">
                    <div style="flex:1;">
                        <label><i class="fas fa-image"></i> Logo Esquerda (Advogado 1)</label>
                        <?php if(!empty($registro['logo_1'])): ?>
                            <div style="margin-bottom:10px;"><img src="../../<?php echo $registro['logo_1']; ?>" height="40"></div>
                        <?php endif; ?>
                        <input type="file" name="logo_1" class="form-control" accept=".png,.jpg,.jpeg">
                    </div>
                    <div style="flex:1;">
                        <label><i class="fas fa-image"></i> Logo Direita (Advogado 2)</label>
                        <?php if(!empty($registro['logo_2'])): ?>
                            <div style="margin-bottom:10px;"><img src="../../<?php echo $registro['logo_2']; ?>" height="40"></div>
                        <?php endif; ?>
                        <input type="file" name="logo_2" class="form-control" accept=".png,.jpg,.jpeg">
                    </div>
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1; display:flex; gap:20px; background:#fcfcfc; padding: 15px; border-radius:4px; border:1px dashed #ccc;">
                    <div style="flex:1;">
                        <label>📝 Assinatura 1 (Esquerda)</label>
                        <input type="text" name="assinatura_1_nome" class="form-control" value="<?php echo htmlspecialchars($registro['assinatura_1_nome'] ?? 'Daiane de Oliveira Soares'); ?>" placeholder="Nome do Advogado">
                        <input type="text" name="assinatura_1_oab" class="form-control" value="<?php echo htmlspecialchars($registro['assinatura_1_oab'] ?? 'OAB/RS 129238'); ?>" placeholder="OAB" style="margin-top:5px;">
                    </div>
                    <div style="flex:1;">
                        <label>📝 Assinatura 2 (Direita)</label>
                        <input type="text" name="assinatura_2_nome" class="form-control" value="<?php echo htmlspecialchars($registro['assinatura_2_nome'] ?? 'Hérica Patrícia Matos de Morais'); ?>" placeholder="Nome do Advogado">
                        <input type="text" name="assinatura_2_oab" class="form-control" value="<?php echo htmlspecialchars($registro['assinatura_2_oab'] ?? 'OAB/RS 108.077'); ?>" placeholder="OAB" style="margin-top:5px;">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Descrição Detalhada do Serviço</label>
                    <textarea id="descricao_servicos_editor" name="descricao_servicos" class="form-control" rows="15" placeholder="Descreva aqui o escopo do serviço da forma mais detalhada possível..."><?php echo htmlspecialchars($registro['descricao_servicos'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="form-actions" style="display:flex; justify-content:space-between; margin-top:20px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Proposta</button>
                <?php if($id): ?>
                    <a href="imprimir.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-print"></i> Gerar Visualização / PDF</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Editor Rico para Descrição de Serviços -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#descricao_servicos_editor'), {
            toolbar: [ 'heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo' ]
        })
        .catch(error => {
            console.error(error);
        });
</script>

<style>
    /* Ajuste para o CKEditor ficar mais alto por padrão e matching com o Design System */
    .ck-editor__editable {
        min-height: 300px;
        font-family: 'Inter', sans-serif;
    }
    
    /* Restaurar marcadores de lista dentro do editor de texto que foram resetados no style.css global */
    .ck-content ul, .ck-editor__editable ul {
        list-style-type: disc !important;
        margin-left: 20px;
    }
    .ck-content ol, .ck-editor__editable ol {
        list-style-type: decimal !important;
        margin-left: 20px;
    }
    .ck-content li, .ck-editor__editable li {
        display: list-item;
    }
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
