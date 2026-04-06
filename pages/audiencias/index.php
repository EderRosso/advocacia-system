<?php
$page_title = 'Gerenciar Audiências';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('audiencias');

$busca = $_GET['busca'] ?? '';

try {
    $sql = "SELECT a.*, c.nome as cliente_nome, p.numero_processo 
            FROM audiencias a 
            JOIN clientes c ON a.id_cliente = c.id 
            JOIN processos p ON a.id_processo = p.id ";
            
    if (!empty($busca)) {
        $sql .= "WHERE p.numero_processo LIKE :busca OR c.nome LIKE :cliente OR a.status = :status ";
    }
    $sql .= "ORDER BY a.data_audiencia DESC, a.hora_audiencia DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    
    if (!empty($busca)) {
        $termo = "%$busca%";
        $stmt->bindParam(':busca', $termo);
        $stmt->bindParam(':cliente', $termo);
        $stmt->bindParam(':status', $busca);
    }
    $stmt->execute();
    $audiencias = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>

<div class="page-header">
    <form action="" method="GET" class="search-bar">
        <input type="text" name="busca" placeholder="Nº Processo, Cliente, Status..." value="<?php echo htmlspecialchars($busca); ?>">
        <button type="submit" class="btn btn-blue"><i class="fas fa-search"></i> Buscar</button>
    </form>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nova Audiência</a>
</div>

<div class="panel">
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Processo</th>
                    <th>Cliente</th>
                    <th>Local</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($audiencias) > 0): ?>
                    <?php foreach($audiencias as $a): ?>
                    <tr>
                        <td><strong><?php echo date('d/m/Y', strtotime($a['data_audiencia'])) . ' ' . $a['hora_audiencia']; ?></strong></td>
                        <td><?php echo htmlspecialchars($a['numero_processo']); ?></td>
                        <td><?php echo htmlspecialchars($a['cliente_nome']); ?></td>
                        <td><?php echo htmlspecialchars($a['local_audiencia']); ?></td>
                        <td>
                            <?php 
                                $statusClass = $a['status'] == 'realizada' ? 'badge-success' : ($a['status'] == 'agendada' ? 'badge-warning' : 'badge-danger');
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst(htmlspecialchars($a['status'])); ?></span>
                        </td>
                        <td class="btn-actions">
                            <?php 
                                $data_in = date('Ymd\THis', strtotime($a['data_audiencia'] . ' ' . $a['hora_audiencia']));
                                $data_fim = date('Ymd\THis', strtotime($a['data_audiencia'] . ' ' . $a['hora_audiencia'] . ' +1 hour'));
                                $tit = urlencode("Audiência: " . $a['cliente_nome']);
                                $desc = urlencode("Processo: " . $a['numero_processo'] . "\nCliente: " . $a['cliente_nome']);
                                $loc = urlencode($a['local_audiencia'] ?? 'Não informado');
                                $link_google = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$tit}&dates={$data_in}/{$data_fim}&details={$desc}&location={$loc}";
                            ?>
                            <a href="<?php echo $link_google; ?>" target="_blank" class="btn btn-sm" style="background-color: #4285F4; color: white;" title="Adicionar ao Google Agenda"><i class="fab fa-google"></i></a>
                            <a href="form.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-blue" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="delete.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-red" title="Excluir" onclick="return confirm('Tem certeza?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Nenhuma audiência encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
