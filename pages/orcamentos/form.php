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
    
    $caminho_logo = null;
    if ($id) {
        // Pega a logo original caso edição
        $stmt_l = $pdo->prepare("SELECT logo_advogado FROM orcamentos WHERE id=?");
        $stmt_l->execute([$id]);
        $caminho_logo = $stmt_l->fetchColumn();
    }

    // Tratamento Upload de Logo (se houver)
    if (isset($_FILES['logo_advogado']) && $_FILES['logo_advogado']['error'] === UPLOAD_ERR_OK) {
        $dir_uploads = __DIR__ . '/../../uploads/logos/';
        if (!is_dir($dir_uploads)) { @mkdir($dir_uploads, 0755, true); }
        $ext = strtolower(pathinfo($_FILES['logo_advogado']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $novoNome = uniqid('logo_') . '.' . $ext;
            if (move_uploaded_file($_FILES['logo_advogado']['tmp_name'], $dir_uploads . $novoNome)) {
                // Delete a antiga
                if ($caminho_logo && file_exists(__DIR__ . '/../../' . $caminho_logo)) {
                    @unlink(__DIR__ . '/../../' . $caminho_logo);
                }
                $caminho_logo = 'uploads/logos/' . $novoNome;
            }
        } else {
            $erro = "Formato de logo inválido! Apenas PNG e JPG.";
        }
    }

    if (empty($id_cliente) || empty($titulo) || empty($valor)) {
        $erro = 'Cliente, título e valor são obrigatórios.';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE orcamentos SET id_cliente=?, titulo=?, valor=?, descricao_servicos=?, logo_advogado=?, validade_dias=? WHERE id=?");
                $stmt->execute([$id_cliente, $titulo, $valor, $descricao_servicos, $caminho_logo, $validade_dias, $id]);
                $sucesso = 'Orçamento/Proposta atualizada com sucesso.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO orcamentos (id_cliente, titulo, valor, descricao_servicos, logo_advogado, validade_dias) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_cliente, $titulo, $valor, $descricao_servicos, $caminho_logo, $validade_dias]);
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
                
                <div class="form-group" style="background:#f9f9f9; padding: 10px; border-radius:4px; border:1px dashed #ccc;">
                    <label><i class="fas fa-image"></i> Logo do Advogado / Escritório</label>
                    <?php if(!empty($registro['logo_advogado'])): ?>
                        <div style="margin-bottom:10px;">
                            <img src="../../<?php echo $registro['logo_advogado']; ?>" height="40" alt="Logo atual">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo_advogado" class="form-control" accept=".png,.jpg,.jpeg">
                    <small class="text-muted">Será impresso no topo da proposta em PDF.</small>
                </div>

                <div class="form-group full-width">
                    <label>Descrição Detalhada do Serviço / Cláusulas</label>
                    <textarea name="descricao_servicos" class="form-control" rows="12" placeholder="Descreva aqui o escopo do serviço da forma mais detalhada possível. Ele formará o corpo da proposta comercial."><?php echo htmlspecialchars($registro['descricao_servicos'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="form-actions" style="display:flex; justify-content:space-between;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Proposta</button>
                <?php if($id): ?>
                    <a href="imprimir.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-print"></i> Gerar Visualização / PDF</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
