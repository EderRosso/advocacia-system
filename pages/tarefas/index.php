<?php
$page_title = 'Gerenciar Tarefas (Kanban)';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('tarefas');

$busca = $_GET['busca'] ?? '';

$colunas = [];
$kanban = [];

try {
    // Buscar todas as colunas ordenadas
    $stmt_colunas = $pdo->query("SELECT * FROM kanban_colunas ORDER BY ordem ASC");
    $colunas = $stmt_colunas->fetchAll();
    
    // Buscar tarefas não arquivadas
    $sql = "SELECT t.*, u.nome as responsavel_nome, c.nome as cliente_nome, p.numero_processo 
            FROM tarefas t 
            LEFT JOIN usuarios u ON t.id_responsavel = u.id 
            LEFT JOIN clientes c ON t.id_cliente = c.id 
            LEFT JOIN processos p ON t.id_processo = p.id 
            WHERE t.arquivado = 0 ";
    if (!empty($busca)) $sql .= "AND t.titulo LIKE :busca ";
    $sql .= "ORDER BY t.prioridade DESC, t.data_final ASC LIMIT 200";
    
    $stmt = $pdo->prepare($sql);
    if (!empty($busca)) {
        $termo = "%$busca%"; $stmt->bindParam(':busca', $termo);
    }
    $stmt->execute();
    $todas_tarefas = $stmt->fetchAll();
    
    // Agrupar tarefas por id_quadro
    foreach($colunas as $c) {
        $kanban[$c['id']] = [];
    }
    
    foreach($todas_tarefas as $t) {
        $idq = $t['id_quadro'];
        if (isset($kanban[$idq])) {
            $kanban[$idq][] = $t;
        }
    }
    
} catch(PDOException $e) { 
    echo "<div class='alert alert-danger'>Erro no banco de dados: " . $e->getMessage() . "</div>"; 
}
?>

<!-- Importar SortableJS via CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<div class="page-header">
    <form action="" method="GET" class="search-bar">
        <input type="text" name="busca" placeholder="Buscar tarefa..." value="<?php echo htmlspecialchars($busca); ?>">
        <button type="submit" class="btn btn-blue"><i class="fas fa-search"></i> Buscar</button>
    </form>
    <div class="action-buttons">
        <a href="arquivadas.php" class="btn" style="background-color:#7f8c8d; color:white;"><i class="fas fa-archive"></i> Ver Arquivadas</a>
        <?php if ($_SESSION['usuario_perfil'] === 'administrador'): ?>
        <a href="quadros.php" class="btn btn-blue"><i class="fas fa-columns"></i> Gerenciar Quadros</a>
        <?php endif; ?>
        <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nova Tarefa</a>
    </div>
</div>

<div class="kanban-board">
    <?php foreach($colunas as $col): ?>
    <div class="kanban-col">
        <div class="kanban-header kanban-dynamic" style="border-top: 4px solid <?php echo htmlspecialchars($col['cor']); ?>;">
            <h4><i class="fas fa-list-ul"></i> <?php echo htmlspecialchars($col['titulo']); ?> (<span id="count-<?php echo $col['id']; ?>"><?php echo count($kanban[$col['id']]); ?></span>)</h4>
        </div>
        <div class="kanban-body column-droppable" id="col-<?php echo $col['id']; ?>" data-id="<?php echo $col['id']; ?>">
            <?php foreach($kanban[$col['id']] as $t): ?>
                <?php $priClass = $t['prioridade'] == 'alta' ? 'p-alta' : ($t['prioridade'] == 'media' ? 'p-media' : 'p-baixa'); ?>
                <div class="kanban-card <?php echo $priClass; ?>" data-id="<?php echo $t['id']; ?>">
                    <div class="k-title" style="margin-bottom: 5px;"><?php echo htmlspecialchars($t['titulo']); ?></div>
                    
                    <?php if(!empty($t['cliente_nome']) || !empty($t['numero_processo'])): ?>
                    <div style="font-size: 11px; color: #444; background: #eef2f5; padding: 4px 6px; border-radius: 4px; border: 1px solid #dcdcdc; margin-bottom: 8px;">
                        <?php if(!empty($t['cliente_nome'])): ?>
                            <div style="margin-bottom: 2px;"><i class="fas fa-user-tie text-blue"></i> <?php echo htmlspecialchars($t['cliente_nome']); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($t['numero_processo'])): ?>
                            <div><i class="fas fa-gavel text-gold"></i> Proc: <?php echo htmlspecialchars($t['numero_processo']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="k-resp">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($t['responsavel_nome'] ?? 'Sem responsável'); ?>
                    </div>
                    <div class="k-date">
                        <i class="fas fa-calendar-alt"></i> <?php echo $t['data_final'] ? date('d/m/Y', strtotime($t['data_final'])) : 'Sem prazo'; ?>
                    </div>
                    <div class="k-actions">
                        <a href="arquivar.php?id=<?php echo $t['id']; ?>" class="text-gray" title="Arquivar Tarefa" onclick="return confirm('Mover para o Arquivo Morto? Nenhuma informação será perdida.');" style="color: #7f8c8d; margin-right:5px;"><i class="fas fa-box-open"></i></a>
                        <a href="form.php?id=<?php echo $t['id']; ?>" title="Editar"><i class="fas fa-edit"></i></a>
                        <a href="delete.php?id=<?php echo $t['id']; ?>" class="text-red" title="Excluir" onclick="return confirm('Excluir tarefa permanentemente?');"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colunas = document.querySelectorAll('.column-droppable');
    
    colunas.forEach(el => {
        new Sortable(el, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'kanban-ghost',
            onEnd: function (evt) {
                const itemEl = evt.item;
                const toList = evt.to;
                
                const taskId = itemEl.getAttribute('data-id');
                const newQuadroId = toList.getAttribute('data-id');
                
                fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + taskId + '&id_quadro=' + newQuadroId
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        updateCounters();
                    } else {
                        alert('Erro ao mover tarefa: ' + data.message);
                        evt.from.appendChild(itemEl);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    evt.from.appendChild(itemEl);
                });
            },
        });
    });

    function updateCounters() {
        document.querySelectorAll('.column-droppable').forEach(col => {
            const id = col.getAttribute('data-id');
            const num = col.children.length;
            const span = document.getElementById('count-' + id);
            if(span) span.textContent = num;
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
