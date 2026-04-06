<?php
$page_title = 'Gerenciar Clientes';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('clientes');

$busca = $_GET['busca'] ?? '';

try {
    if (!empty($busca)) {
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE nome LIKE :busca OR cpf LIKE :cpf OR email LIKE :email ORDER BY nome ASC");
        $termo = "%$busca%";
        $stmt->bindParam(':busca', $termo);
        $stmt->bindParam(':cpf', $termo);
        $stmt->bindParam(':email', $termo);
        $stmt->execute();
    } else {
        $stmt = $pdo->query("SELECT * FROM clientes ORDER BY nome ASC LIMIT 50");
    }
    $clientes = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>

<div class="page-header">
    <form action="" method="GET" class="search-bar">
        <input type="text" name="busca" placeholder="Nome, CPF ou E-mail..." value="<?php echo htmlspecialchars($busca); ?>">
        <button type="submit" class="btn btn-blue"><i class="fas fa-search"></i> Buscar</button>
    </form>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Cliente</a>
</div>

<div class="panel">
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($clientes) > 0): ?>
                    <?php foreach($clientes as $cli): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($cli['nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($cli['cpf']); ?></td>
                        <td><?php echo htmlspecialchars($cli['telefone']); ?></td>
                        <td><?php echo htmlspecialchars($cli['email']); ?></td>
                        <td class="btn-actions">
                            <a href="form.php?id=<?php echo $cli['id']; ?>" class="btn btn-sm btn-blue" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="delete.php?id=<?php echo $cli['id']; ?>" class="btn btn-sm btn-red" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este cliente e todos os dados associados?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Nenhum cliente encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
