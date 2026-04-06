<?php
$page_title = 'Gerenciar Prazos';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('prazos');

$busca = $_GET['busca'] ?? '';

try {
    $sql = "SELECT pr.*, p.numero_processo, u.nome as responsavel_nome 
            FROM prazos pr 
            JOIN processos p ON pr.id_processo = p.id 
            LEFT JOIN usuarios u ON pr.id_responsavel = u.id ";
            
    if (!empty($busca)) {
        $sql .= "WHERE p.numero_processo LIKE :busca OR pr.descricao_prazo LIKE :descricao OR pr.status = :status ";
    }
    $sql .= "ORDER BY pr.data_limite ASC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    
    if (!empty($busca)) {
        $termo = "%$busca%";
        $stmt->bindParam(':busca', $termo);
        $stmt->bindParam(':descricao', $termo);
        $stmt->bindParam(':status', $busca);
    }
    $stmt->execute();
    $prazos = $stmt->fetchAll();
} catch(PDOException $e) { echo "Erro: " . $e->getMessage(); }
?>

<div class="page-header">
    <form action="" method="GET" class="search-bar">
        <input type="text" name="busca" placeholder="Nº Processo, Descrição..." value="<?php echo htmlspecialchars($busca); ?>">
        <button type="submit" class="btn btn-blue"><i class="fas fa-search"></i> Buscar</button>
    </form>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Prazo</a>
</div>

<div class="panel">
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Data Limite</th>
                    <th>Processo</th>
                    <th>Descrição</th>
                    <th>Responsável</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($prazos) > 0): ?>
                    <?php foreach($prazos as $pr): ?>
                    <tr>
                        <td><strong><?php echo date('d/m/Y', strtotime($pr['data_limite'])); ?></strong></td>
                        <td><?php echo htmlspecialchars($pr['numero_processo']); ?></td>
                        <td><?php echo htmlspecialchars($pr['descricao_prazo']); ?></td>
                        <td><?php echo htmlspecialchars($pr['responsavel_nome']); ?></td>
                        <td>
                            <?php 
                                $statusClass = $pr['status'] == 'cumprido' ? 'badge-success' : ($pr['status'] == 'pendente' ? 'badge-warning' : 'badge-danger');
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst(htmlspecialchars($pr['status'])); ?></span>
                        </td>
                        <td class="btn-actions">
                            <?php 
                                $data_in = date('Ymd', strtotime($pr['data_limite']));
                                $data_fim = date('Ymd', strtotime($pr['data_limite'] . ' +1 day'));
                                $tit = urlencode("Prazo: " . $pr['descricao_prazo']);
                                $desc = urlencode("Processo: " . $pr['numero_processo'] . "\nResponsável: " . $pr['responsavel_nome']);
                                $link_google = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$tit}&dates={$data_in}/{$data_fim}&details={$desc}";
                            ?>
                            <a href="<?php echo $link_google; ?>" target="_blank" class="btn btn-sm" style="background-color: #4285F4; color: white;" title="Adicionar ao Google Agenda"><i class="fab fa-google"></i></a>
                            <a href="form.php?id=<?php echo $pr['id']; ?>" class="btn btn-sm btn-blue"><i class="fas fa-edit"></i></a>
                            <a href="delete.php?id=<?php echo $pr['id']; ?>" class="btn btn-sm btn-red" onclick="return confirm('Excluir prazo?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Nenhum prazo encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
