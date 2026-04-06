<?php
$page_title = 'Orçamentos / Propostas';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';

// Deletar Orçamento
if (isset($_GET['delete'])) {
    if (!tem_permissao('honorarios')) { echo "Sem permissão."; exit; }
    $id_del = (int)$_GET['delete'];
    
    // Deletar logo caso exista
    $stmt_log = $pdo->prepare("SELECT logo_advogado FROM orcamentos WHERE id=?");
    $stmt_log->execute([$id_del]);
    $orc = $stmt_log->fetch();
    if($orc && !empty($orc['logo_advogado']) && file_exists(__DIR__ . '/../../' . $orc['logo_advogado'])) {
        @unlink(__DIR__ . '/../../' . $orc['logo_advogado']);
    }

    $stmt_del = $pdo->prepare("DELETE FROM orcamentos WHERE id = ?");
    $stmt_del->execute([$id_del]);
    $sucesso = "Orçamento excluído com sucesso!";
}

// Mudar Status
if (isset($_GET['status']) && isset($_GET['id'])) {
    if (!tem_permissao('honorarios')) { echo "Sem permissão."; exit; }
    $id_st = (int)$_GET['id'];
    $novo_st = $_GET['status'];
    $data_aprov = ($novo_st === 'aprovado') ? date('Y-m-d H:i:s') : null;
    $stmt_st = $pdo->prepare("UPDATE orcamentos SET status = ?, data_aprovacao = ? WHERE id = ?");
    $stmt_st->execute([$novo_st, $data_aprov, $id_st]);
    $sucesso = "Status atualizado!";
}

// Busca principal
$search = $_GET['q'] ?? '';
$where = "1=1";
$params = [];
if ($search) {
    $where .= " AND (o.titulo LIKE ? OR c.nome LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}

$sql = "SELECT o.*, c.nome as cliente_nome
        FROM orcamentos o
        JOIN clientes c ON o.id_cliente = c.id
        WHERE $where 
        ORDER BY o.data_criacao DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orcamentos = $stmt->fetchAll();
?>

<div class="panel">
    <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3><i class="fas fa-file-signature text-primary"></i> Orçamentos e Propostas</h3>
        <div style="display: flex; gap: 10px;">
            <form action="" method="GET" style="display:flex; gap:5px;">
                <input type="text" name="q" class="form-control" placeholder="Buscar proposta..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
            </form>
            <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Orçamento</a>
        </div>
    </div>
    <div class="panel-body table-responsive">
        <?php if(isset($sucesso)): ?><div class="alert alert-success"><?php echo $sucesso; ?></div><?php endif; ?>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Título da Proposta</th>
                    <th>Cliente</th>
                    <th>Valor</th>
                    <th>Validade</th>
                    <th>Status</th>
                    <th style="width: 180px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($orcamentos) > 0): ?>
                    <?php foreach($orcamentos as $o): 
                        $badge_class = 'badge-warning';
                        if($o['status'] == 'aprovado') $badge_class = 'badge-success';
                        if($o['status'] == 'rejeitado') $badge_class = 'badge-danger';
                        
                        $validade_dt = date('Y-m-d', strtotime($o['data_criacao'] . " +{$o['validade_dias']} days"));
                        $vencido = (strtotime($validade_dt) < strtotime(date('Y-m-d')) && $o['status'] == 'pendente');
                    ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($o['data_criacao'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($o['titulo']); ?></strong></td>
                        <td><?php echo htmlspecialchars($o['cliente_nome']); ?></td>
                        <td style="color:#28a745; font-weight:600;">R$ <?php echo number_format($o['valor'], 2, ',', '.'); ?></td>
                        <td>
                            <?php if($o['status'] == 'pendente'): ?>
                                <?php if($vencido): ?>
                                    <span class="text-danger" title="Proposta Vencida"><i class="fas fa-exclamation-circle"></i> Vencido</span>
                                <?php else: ?>
                                    <?php echo date('d/m/Y', strtotime($validade_dt)); ?>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                        <td>
                            <a href="imprimir.php?id=<?php echo $o['id']; ?>" target="_blank" class="btn btn-sm btn-secondary" title="Imprimir/PDF"><i class="fas fa-print"></i></a>
                            <a href="form.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-blue" title="Editar"><i class="fas fa-edit"></i></a>
                            
                            <?php if($o['status'] == 'pendente'): ?>
                                <a href="index.php?id=<?php echo $o['id']; ?>&status=aprovado" class="btn btn-sm" style="background:#28a745; color:white;" title="Aprovar"><i class="fas fa-check"></i></a>
                                <a href="index.php?id=<?php echo $o['id']; ?>&status=rejeitado" class="btn btn-sm" style="background:#dc3545; color:white;" title="Rejeitar"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                            
                            <a href="index.php?delete=<?php echo $o['id']; ?>" onclick="return confirm('Excluir este orçamento permanentemente?');" class="btn btn-sm" style="background:#cc0000; color:white;" title="Excluir"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">Nenhum orçamento encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
