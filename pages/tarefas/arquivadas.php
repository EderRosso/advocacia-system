<?php
$page_title = 'Arquivo Morto (Tarefas Concluídas)';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('tarefas');

try {
    $sql = "SELECT t.*, u.nome as responsavel_nome, c.nome as cliente_nome, p.numero_processo, k.titulo as quadro_nome 
            FROM tarefas t 
            LEFT JOIN usuarios u ON t.id_responsavel = u.id 
            LEFT JOIN clientes c ON t.id_cliente = c.id 
            LEFT JOIN processos p ON t.id_processo = p.id 
            LEFT JOIN kanban_colunas k ON t.id_quadro = k.id
            WHERE t.arquivado = 1 
            ORDER BY t.data_final DESC LIMIT 200";
    
    $stmt = $pdo->query($sql);
    $arquivadas = $stmt->fetchAll();
} catch(PDOException $e) { echo "Erro: " . $e->getMessage(); }
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-archive"></i> Arquivo Morto (Limbo de Tarefas)</h2>
        <p class="text-muted" style="margin-top:5px;">Tarefas arquivadas somem do Quadro Kanban para manter a visão limpa. Elas não interagem com o LexFlow e podem ser restauradas aqui.</p>
    </div>
    <a href="index.php" class="btn btn-blue"><i class="fas fa-arrow-left"></i> Voltar ao Quadro</a>
</div>

<div class="panel">
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Título da Tarefa</th>
                    <th>Quadro Original</th>
                    <th>Responsável</th>
                    <th>Cliente / Processo</th>
                    <th style="min-width: 150px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($arquivadas) > 0): ?>
                    <?php foreach($arquivadas as $t): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($t['titulo']); ?></strong></td>
                        <td><span class="badge" style="background:#bdc3c7; color:#333;"><i class="fas fa-column"></i> <?php echo htmlspecialchars($t['quadro_nome']); ?></span></td>
                        <td><?php echo htmlspecialchars($t['responsavel_nome'] ?? '-'); ?></td>
                        <td>
                            <?php 
                            if (!empty($t['cliente_nome'])) {
                                echo '<strong>' . htmlspecialchars($t['cliente_nome']) . '</strong><br>';
                            }
                            if (!empty($t['numero_processo'])) {
                                echo '<span style="font-size:11px; color:#555;"><i class="fas fa-gavel"></i> ' . htmlspecialchars($t['numero_processo']) . '</span>';
                            }
                            ?>
                        </td>
                        <td class="btn-actions">
                            <a href="desarquivar.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-green" title="Mover de volta para o Kanban" onclick="return confirm('Mover tarefa de volta para o Kanban de rotina do escritório?');"><i class="fas fa-box-open"></i> Restaurar</a>
                            <a href="delete.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-red" title="Excluir Definitivamente do Sistema" onclick="return confirm('Excluir do código fonte permanentemente? NENHUMA linha de banco de dados poderá recupar isso.');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center" style="padding: 30px;"><i class="fas fa-box-open fa-3x" style="color:#ddd; margin-bottom:15px;"></i><br>Nenhuma tarefa no arquivo morto. Seus quadros estão sujos? Tente arquivar algumas!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
