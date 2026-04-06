<?php
$page_title = 'Gerenciar Honorários';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('honorarios');

$busca = $_GET['busca'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01'); // Padrão: Primeiro dia do mês atual
$data_fim = $_GET['data_fim'] ?? date('Y-m-t'); // Padrão: Último dia do mês atual

try {
    $sql = "SELECT h.*, c.nome as cliente_nome, p.numero_processo 
            FROM honorarios h 
            JOIN clientes c ON h.id_cliente = c.id 
            LEFT JOIN processos p ON h.id_processo = p.id 
            WHERE 1=1 ";
            
    $params = [];

    if (!empty($busca)) {
        $sql .= "AND (c.nome LIKE :busca OR h.tipo_honorario LIKE :busca OR h.status = :status) ";
        $params[':busca'] = "%$busca%";
        $params[':status'] = $busca;
    }
    
    if (!empty($data_inicio)) {
        $sql .= "AND h.data_vencimento >= :data_inicio ";
        $params[':data_inicio'] = $data_inicio;
    }
    
    if (!empty($data_fim)) {
        $sql .= "AND h.data_vencimento <= :data_fim ";
        $params[':data_fim'] = $data_fim;
    }
    
    $sql .= "ORDER BY h.data_vencimento ASC LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $honorarios = $stmt->fetchAll();
} catch(PDOException $e) { echo "Erro: " . $e->getMessage(); }
?>
<div class="page-header" style="flex-direction: column; align-items: stretch; gap: 15px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 18px; color: var(--primary-color);">Filtro de Honorários</h3>
        <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Honorário</a>
    </div>
    
    <div class="panel" style="margin: 0;">
        <div class="panel-body" style="padding: 15px;">
            <form action="" method="GET" class="search-bar" style="margin: 0; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="font-size: 12px; font-weight: 600; color: #555; display:block; margin-bottom: 5px;">Buscar por texto:</label>
                    <input type="text" name="busca" class="form-control" placeholder="Cliente, tipo, status..." value="<?php echo htmlspecialchars($busca); ?>" style="width: 100%;">
                </div>
                <div style="width: 160px;">
                    <label style="font-size: 12px; font-weight: 600; color: #555; display:block; margin-bottom: 5px;">Data Inicial:</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?php echo htmlspecialchars($data_inicio); ?>" style="width: 100%;">
                </div>
                <div style="width: 160px;">
                    <label style="font-size: 12px; font-weight: 600; color: #555; display:block; margin-bottom: 5px;">Data Final:</label>
                    <input type="date" name="data_fim" class="form-control" value="<?php echo htmlspecialchars($data_fim); ?>" style="width: 100%;">
                </div>
                <button type="submit" class="btn btn-blue" style="height: 45px;"><i class="fas fa-search"></i> Filtrar</button>
                <a href="index.php?busca=&data_inicio=&data_fim=" class="btn" style="height: 45px; background: #eee; color: #333;"><i class="fas fa-times"></i> Limpar</a>
            </form>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Processo</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($honorarios) > 0): ?>
                    <?php foreach($honorarios as $h): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($h['cliente_nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($h['numero_processo'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($h['tipo_honorario']); ?></td>
                        <td><?php echo moeda_br($h['valor']); ?></td>
                        <td><?php echo $h['data_vencimento'] ? date('d/m/Y', strtotime($h['data_vencimento'])) : '-'; ?></td>
                        <td>
                            <?php $sClass = $h['status'] == 'pago' ? 'badge-success' : ($h['status'] == 'pendente' ? 'badge-warning' : 'badge-danger'); ?>
                            <span class="badge <?php echo $sClass; ?>"><?php echo ucfirst(htmlspecialchars($h['status'])); ?></span>
                        </td>
                        <td class="btn-actions">
                            <a href="form.php?id=<?php echo $h['id']; ?>" class="btn btn-sm btn-blue"><i class="fas fa-edit"></i></a>
                            <a href="delete.php?id=<?php echo $h['id']; ?>" class="btn btn-sm btn-red" onclick="return confirm('Excluir?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">Nenhum honorário encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
