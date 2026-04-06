<?php
$page_title = 'Cadastro de Honorários';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';

$id = $_GET['id'] ?? null; $registro = null; $erro = ''; $sucesso = '';
$clientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC")->fetchAll();
$processos = $pdo->query("SELECT id, numero_processo FROM processos WHERE status='ativo'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null; $id_cliente = $_POST['id_cliente']; $id_processo = !empty($_POST['id_processo']) ? $_POST['id_processo'] : null;
    $tipo_honorario = trim($_POST['tipo_honorario']); 
    
    // Tratamento robusto para não multiplicar zeros em valores com casas decimais ou pontos
    $valor_bruto = trim($_POST['valor']);
    if (strpos($valor_bruto, ',') !== false) {
        // Formato brasileiro (1.000,50)
        $valor = str_replace('.', '', $valor_bruto);
        $valor = (float)str_replace(',', '.', $valor);
    } else {
        // Já no padrão americano ou inteiro sem milhares (1000.50 ou 1000)
        $valor = (float)$valor_bruto;
    }

    $data_vencimento = !empty($_POST['data_vencimento']) ? $_POST['data_vencimento'] : null;
    $data_pagamento = !empty($_POST['data_pagamento']) ? $_POST['data_pagamento'] : null;
    $forma_pagamento = trim($_POST['forma_pagamento']); $status = $_POST['status']; $observacoes = trim($_POST['observacoes']);

    if (empty($id_cliente) || empty($valor)) { $erro = 'Cliente e valor são obrigatórios.'; } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE honorarios SET id_cliente=?, id_processo=?, tipo_honorario=?, valor=?, data_vencimento=?, data_pagamento=?, forma_pagamento=?, status=?, observacoes=? WHERE id=?");
                $stmt->execute([$id_cliente, $id_processo, $tipo_honorario, $valor, $data_vencimento, $data_pagamento, $forma_pagamento, $status, $observacoes, $id]);
                $sucesso = 'Atualizado.';
            } else {
                $parcelas = isset($_POST['parcelas']) ? (int)$_POST['parcelas'] : 1;
                if ($parcelas > 1) {
                    $valor_parcela = $valor / $parcelas;
                    $data_venc = $data_vencimento ? new DateTime($data_vencimento) : new DateTime();
                    
                    for ($i = 1; $i <= $parcelas; $i++) {
                        $tipo_parc = $tipo_honorario . " (Parcela $i/$parcelas)";
                        $venc_parc = $data_venc->format('Y-m-d');
                        
                        $stmt = $pdo->prepare("INSERT INTO honorarios (id_cliente, id_processo, tipo_honorario, valor, data_vencimento, data_pagamento, forma_pagamento, status, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$id_cliente, $id_processo, $tipo_parc, $valor_parcela, $venc_parc, $data_pagamento, $forma_pagamento, $status, $observacoes]);
                        
                        // Incrementa um mês para a próxima parcela
                        $data_venc->modify('+1 month');
                    }
                    $sucesso = "$parcelas parcelas geradas com sucesso!";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO honorarios (id_cliente, id_processo, tipo_honorario, valor, data_vencimento, data_pagamento, forma_pagamento, status, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id_cliente, $id_processo, $tipo_honorario, $valor, $data_vencimento, $data_pagamento, $forma_pagamento, $status, $observacoes]);
                    $sucesso = 'Cadastrado.'; $id = $pdo->lastInsertId();
                }
            }
        } catch(PDOException $e) { $erro = "Erro: " . $e->getMessage(); }
    }
}
if ($id) { $stmt = $pdo->prepare("SELECT * FROM honorarios WHERE id = ?"); $stmt->execute([$id]); $registro = $stmt->fetch(); }
?>
<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-file-invoice-dollar"></i> Detalhes do Honorário</h3><a href="index.php" class="btn btn-sm btn-blue">Voltar</a></div>
    <div class="panel-body">
        <?php if($erro): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
        <?php if($sucesso): ?><div class="alert alert-success"><?php echo $sucesso; ?></div><?php endif; ?>
        <form action="form.php<?php echo $id ? "?id=$id" : ""; ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-grid">
                <div class="form-group"><label>Cliente *</label><select name="id_cliente" class="form-control" required><option value="">-- Selecione --</option><?php foreach($clientes as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo (isset($registro['id_cliente']) && $registro['id_cliente'] == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['nome']); ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Processo (Opcional)</label><select name="id_processo" class="form-control"><option value="">-- Nenhum --</option><?php foreach($processos as $p): ?><option value="<?php echo $p['id']; ?>" <?php echo (isset($registro['id_processo']) && $registro['id_processo'] == $p['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['numero_processo']); ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Tipo de Honorário</label><input type="text" name="tipo_honorario" class="form-control" placeholder="Contratual, Sucumbência..." value="<?php echo htmlspecialchars($registro['tipo_honorario'] ?? ''); ?>"></div>
                
                <div class="form-group">
                    <label>Valor Total (R$) *</label>
                    <input type="text" name="valor" class="form-control" placeholder="0.00" required value="<?php echo htmlspecialchars($registro['valor'] ?? ''); ?>">
                    <?php if (!$id): ?>
                        <small style="color: #666; font-size: 11px;">Se parcelado, este valor será dividido de forma igual pelo nº de parcelas.</small>
                    <?php endif; ?>
                </div>

                <?php if (!$id): ?>
                <div class="form-group" style="background: #f1f8ff; padding: 10px; border-radius: 4px; border: 1px solid #cce5ff;">
                    <label style="color: var(--primary-color); font-weight: 600;"><i class="fas fa-layer-group"></i> Parcelamento</label>
                    <select name="parcelas" class="form-control">
                        <option value="1">Pagamento Único</option>
                        <?php for($i=2; $i<=48; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> Parcelas (Mensais)</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group"><label>Data de Vencimento <small>(Da 1ª Parcela)</small></label><input type="date" name="data_vencimento" class="form-control" value="<?php echo $registro['data_vencimento'] ?? ''; ?>"></div>
                <div class="form-group"><label>Data de Pagamento</label><input type="date" name="data_pagamento" class="form-control" value="<?php echo $registro['data_pagamento'] ?? ''; ?>"></div>
                <div class="form-group"><label>Forma de Pagamento</label><input type="text" name="forma_pagamento" class="form-control" placeholder="Boleto, PIX, Dinheiro..." value="<?php echo htmlspecialchars($registro['forma_pagamento'] ?? ''); ?>"></div>
                <div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="pendente" <?php echo (isset($registro['status']) && $registro['status'] == 'pendente') ? 'selected' : ''; ?>>Pendente</option><option value="pago" <?php echo (isset($registro['status']) && $registro['status'] == 'pago') ? 'selected' : ''; ?>>Pago</option><option value="cancelado" <?php echo (isset($registro['status']) && $registro['status'] == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option></select></div>
                <div class="form-group full-width"><label>Observações</label><textarea name="observacoes" class="form-control"><?php echo htmlspecialchars($registro['observacoes'] ?? ''); ?></textarea></div>
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
