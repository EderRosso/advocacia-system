<?php
$page_title = 'Cadastro de Prazo';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';

$id = $_GET['id'] ?? null;
$registro = null; $erro = ''; $sucesso = '';

$processos = $pdo->query("SELECT id, numero_processo FROM processos WHERE status = 'ativo'")->fetchAll();
$usuarios = $pdo->query("SELECT id, nome FROM usuarios WHERE status = 'ativo'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $id_processo = $_POST['id_processo'];
    $descricao_prazo = trim($_POST['descricao_prazo']);
    $data_limite = $_POST['data_limite'];
    $id_responsavel = !empty($_POST['id_responsavel']) ? $_POST['id_responsavel'] : null;
    $status = $_POST['status'];
    $observacoes = trim($_POST['observacoes']);

    if (empty($id_processo) || empty($descricao_prazo) || empty($data_limite)) {
        $erro = 'Processo, descrição e data limite são obrigatórios.';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE prazos SET id_processo=?, descricao_prazo=?, data_limite=?, id_responsavel=?, status=?, observacoes=? WHERE id=?");
                $stmt->execute([$id_processo, $descricao_prazo, $data_limite, $id_responsavel, $status, $observacoes, $id]);
                $sucesso = 'Prazo atualizado.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO prazos (id_processo, descricao_prazo, data_limite, id_responsavel, status, observacoes) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_processo, $descricao_prazo, $data_limite, $id_responsavel, $status, $observacoes]);
                $sucesso = 'Prazo cadastrado.';
                $id = $pdo->lastInsertId();
            }
        } catch(PDOException $e) { $erro = "Erro: " . $e->getMessage(); }
    }
}

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM prazos WHERE id = ?"); $stmt->execute([$id]); $registro = $stmt->fetch();
}
?>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-calendar-alt"></i> Detalhes do Prazo</h3><a href="index.php" class="btn btn-sm btn-blue">Voltar</a></div>
    <div class="panel-body">
        <?php if($erro): ?> <div class="alert alert-danger"><?php echo $erro; ?></div> <?php endif; ?>
        <?php if($sucesso): ?> <div class="alert alert-success"><?php echo $sucesso; ?></div> <?php endif; ?>

        <form action="form.php<?php echo $id ? "?id=$id" : ""; ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Processo *</label>
                    <select name="id_processo" class="form-control" required>
                        <option value="">-- Selecione --</option>
                        <?php foreach($processos as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo (isset($registro['id_processo']) && $registro['id_processo'] == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['numero_processo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Descrição do Prazo *</label><input type="text" name="descricao_prazo" class="form-control" required value="<?php echo htmlspecialchars($registro['descricao_prazo'] ?? ''); ?>"></div>
                <div class="form-group"><label>Data Limite *</label><input type="date" name="data_limite" class="form-control" required value="<?php echo $registro['data_limite'] ?? ''; ?>"></div>
                <div class="form-group">
                    <label>Responsável</label>
                    <select name="id_responsavel" class="form-control">
                        <option value="">-- Selecione --</option>
                        <?php foreach($usuarios as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo (isset($registro['id_responsavel']) && $registro['id_responsavel'] == $u['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="pendente" <?php echo (isset($registro['status']) && $registro['status'] == 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                        <option value="cumprido" <?php echo (isset($registro['status']) && $registro['status'] == 'cumprido') ? 'selected' : ''; ?>>Cumprido</option>
                        <option value="vencido" <?php echo (isset($registro['status']) && $registro['status'] == 'vencido') ? 'selected' : ''; ?>>Vencido</option>
                    </select>
                </div>
                <div class="form-group full-width"><label>Observações</label><textarea name="observacoes" class="form-control"><?php echo htmlspecialchars($registro['observacoes'] ?? ''); ?></textarea></div>
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button></div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
