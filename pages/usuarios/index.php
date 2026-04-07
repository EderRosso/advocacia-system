<?php
$page_title = 'Gerenciar Usuários';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_admin();

$busca = $_GET['busca'] ?? '';

try {
    $sql = "SELECT * FROM usuarios ";
    if (!empty($busca)) $sql .= "WHERE nome LIKE :busca OR email LIKE :busca ";
    $sql .= "ORDER BY nome ASC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    if (!empty($busca)) { $termo = "%$busca%"; $stmt->bindParam(':busca', $termo); }
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
} catch(PDOException $e) { echo "Erro: " . $e->getMessage(); }
?>
<div class="page-header">
    <form action="" method="GET" class="search-bar">
        <input type="text" name="busca" placeholder="Nome, email..." value="<?php echo htmlspecialchars($busca); ?>">
        <button type="submit" class="btn btn-blue"><i class="fas fa-search"></i> Buscar</button>
    </form>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Usuário</a>
</div>

<div class="panel">
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($usuarios) > 0): ?>
                    <?php foreach($usuarios as $u): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($u['nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="badge badge-warning"><?php echo ucfirst(htmlspecialchars($u['perfil'])); ?></span></td>
                        <td>
                            <?php $sClass = $u['status'] == 'ativo' ? 'badge-success' : 'badge-danger'; ?>
                            <span class="badge <?php echo $sClass; ?>"><?php echo ucfirst(htmlspecialchars($u['status'])); ?></span>
                        </td>
                        <td class="btn-actions">
                            <a href="form.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-blue"><i class="fas fa-edit"></i></a>
                            <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                            <a href="delete.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-red" onclick="return confirmDialog(event, this.href, 'Excluir usuário?');"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Nenhum usuário encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
