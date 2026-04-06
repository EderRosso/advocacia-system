<?php
$page_title = 'Gerenciar Colunas do Kanban';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('tarefas');
verifica_admin();

try {
    $stmt = $pdo->query("SELECT * FROM kanban_colunas ORDER BY ordem ASC");
    $quadros = $stmt->fetchAll();
} catch(PDOException $e) { echo "Erro: " . $e->getMessage(); }
?>
<div class="page-header">
    <a href="index.php" class="btn btn-blue"><i class="fas fa-arrow-left"></i> Voltar ao Quadro</a>
    <a href="quadro_form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nova Coluna</a>
</div>

<div class="panel">
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Ordem de Exibição</th>
                    <th>Título do Quadro</th>
                    <th>Cor de Identificação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($quadros) > 0): ?>
                    <?php foreach($quadros as $q): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($q['ordem']); ?></strong></td>
                        <td><?php echo htmlspecialchars($q['titulo']); ?></td>
                        <td><div style="width: 25px; height: 25px; background-color: <?php echo htmlspecialchars($q['cor']); ?>; border-radius: 4px; border: 1px solid #ccc;"></div></td>
                        <td class="btn-actions">
                            <a href="quadro_form.php?id=<?php echo $q['id']; ?>" class="btn btn-sm btn-blue"><i class="fas fa-edit"></i></a>
                            <a href="quadro_delete.php?id=<?php echo $q['id']; ?>" class="btn btn-sm btn-red" onclick="return confirm('ATENÇÃO: Excluir esta coluna apagará todas as tarefas vinculadas a ela! Tem certeza disso?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Nenhum quadro encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
