<?php
$page_title = 'Cadastro de Coluna do Kanban';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('tarefas');
verifica_admin();

$id = $_GET['id'] ?? null; $registro = null; $erro = ''; $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null; 
    $titulo = trim($_POST['titulo']);
    $cor = trim($_POST['cor']);
    $ordem = (int)$_POST['ordem'];

    if (empty($titulo)) { $erro = 'Título é obrigatório.'; } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE kanban_colunas SET titulo=?, cor=?, ordem=? WHERE id=?");
                $stmt->execute([$titulo, $cor, $ordem, $id]);
                $sucesso = 'Coluna atualizada.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO kanban_colunas (titulo, cor, ordem) VALUES (?, ?, ?)");
                $stmt->execute([$titulo, $cor, $ordem]);
                $sucesso = 'Coluna cadastrada.'; $id = $pdo->lastInsertId();
            }
        } catch(PDOException $e) { $erro = "Erro: " . $e->getMessage(); }
    }
}
if ($id) { $stmt = $pdo->prepare("SELECT * FROM kanban_colunas WHERE id = ?"); $stmt->execute([$id]); $registro = $stmt->fetch(); }
?>
<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-columns"></i> Detalhes da Coluna</h3><a href="quadros.php" class="btn btn-sm btn-blue">Voltar</a></div>
    <div class="panel-body">
        <?php if($erro): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
        <?php if($sucesso): ?><div class="alert alert-success"><?php echo $sucesso; ?></div><?php endif; ?>
        <form action="quadro_form.php<?php echo $id ? "?id=$id" : ""; ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Título da Coluna *</label>
                    <input type="text" name="titulo" class="form-control" required value="<?php echo htmlspecialchars($registro['titulo'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Cor Identificadora</label>
                    <input type="color" name="cor" class="form-control" style="height: 42px;" value="<?php echo htmlspecialchars($registro['cor'] ?? '#1F6E8C'); ?>">
                </div>
                <div class="form-group">
                    <label>Ordem (Número de exibição da esquerda p/ direita)</label>
                    <input type="number" name="ordem" class="form-control" value="<?php echo htmlspecialchars($registro['ordem'] ?? '1'); ?>">
                </div>
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Coluna</button></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
