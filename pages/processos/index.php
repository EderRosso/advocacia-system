<?php
$page_title = 'Gerenciar Processos';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('processos');

$busca = $_GET['busca'] ?? '';

try {
    $sql = "SELECT p.*, c.nome as cliente_nome, u.nome as advogado_nome 
            FROM processos p 
            JOIN clientes c ON p.id_cliente = c.id 
            LEFT JOIN usuarios u ON p.id_advogado = u.id ";
            
    if (!empty($busca)) {
        $sql .= "WHERE p.numero_processo LIKE :busca OR c.nome LIKE :cliente OR p.status = :status ";
    }
    $sql .= "ORDER BY p.data_distribuicao DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    
    if (!empty($busca)) {
        $termo = "%$busca%";
        $stmt->bindParam(':busca', $termo);
        $stmt->bindParam(':cliente', $termo);
        $stmt->bindParam(':status', $busca);
    }
    $stmt->execute();
    $processos = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>

<div class="page-header action-buttons">
    <form action="" method="GET" class="search-bar">
        <input type="text" name="busca" placeholder="Nº Processo, Cliente, Status..." value="<?php echo htmlspecialchars($busca); ?>">
        <button type="submit" class="btn btn-blue"><i class="fas fa-search"></i> Buscar</button>
    </form>
    <div>
        <a href="ociosos.php" class="btn" style="background-color: var(--warning); color: #fff;"><i class="fas fa-exclamation-circle"></i> Monitorar Ociosidade</a>
        <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Processo</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nº Processo</th>
                    <th>Cliente</th>
                    <th>Tipo de Ação</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($processos) > 0): ?>
                    <?php foreach($processos as $proc): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($proc['numero_processo']); ?></strong></td>
                        <td><?php echo htmlspecialchars($proc['cliente_nome']); ?></td>
                        <td><?php echo htmlspecialchars($proc['tipo_acao']); ?></td>
                        <td>
                            <?php 
                                $statusClass = $proc['status'] == 'ativo' ? 'badge-success' : ($proc['status'] == 'arquivado' ? 'badge-warning' : 'badge-danger');
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst(htmlspecialchars($proc['status'])); ?></span>
                        </td>
                        <td class="btn-actions">
                            <a href="view.php?id=<?php echo $proc['id']; ?>&tab=lexflow" class="btn btn-sm btn-green" title="Assistente Jurídico"><i class="fas fa-robot"></i></a>
                            <a href="form.php?id=<?php echo $proc['id']; ?>" class="btn btn-sm btn-blue" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="delete.php?id=<?php echo $proc['id']; ?>" class="btn btn-sm btn-red" title="Excluir" onclick="return confirmDialog(event, this.href, 'Tem certeza que deseja excluir o processo?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Nenhum processo encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
