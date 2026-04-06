<?php
$page_title = 'Cadastro de Audiência';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';

$id = $_GET['id'] ?? null;
$registro = null;
$erro = ''; $sucesso = '';

$processos = $pdo->query("SELECT p.id, p.numero_processo, c.nome as cliente_nome, p.id_cliente FROM processos p JOIN clientes c ON p.id_cliente = c.id WHERE p.status = 'ativo'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $id_processo_cliente = explode('|', $_POST['id_processo_cliente']); // "id_processo|id_cliente"
    $id_processo = $id_processo_cliente[0] ?? null;
    $id_cliente = $id_processo_cliente[1] ?? null;
    $data_audiencia = $_POST['data_audiencia'];
    $hora_audiencia = $_POST['hora_audiencia'];
    $local_audiencia = trim($_POST['local_audiencia']);
    $tipo_audiencia = trim($_POST['tipo_audiencia']);
    $observacoes = trim($_POST['observacoes']);
    $status = $_POST['status'];

    if (empty($id_processo) || empty($data_audiencia) || empty($hora_audiencia)) {
        $erro = 'Processo, data e hora são obrigatórios.';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE audiencias SET id_processo=?, id_cliente=?, data_audiencia=?, hora_audiencia=?, local_audiencia=?, tipo_audiencia=?, observacoes=?, status=? WHERE id=?");
                $stmt->execute([$id_processo, $id_cliente, $data_audiencia, $hora_audiencia, $local_audiencia, $tipo_audiencia, $observacoes, $status, $id]);
                $sucesso = 'Audiência atualizada com sucesso.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO audiencias (id_processo, id_cliente, data_audiencia, hora_audiencia, local_audiencia, tipo_audiencia, observacoes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_processo, $id_cliente, $data_audiencia, $hora_audiencia, $local_audiencia, $tipo_audiencia, $observacoes, $status]);
                $sucesso = 'Audiência cadastrada.';
                $id = $pdo->lastInsertId();
            }
        } catch(PDOException $e) { $erro = "Erro: " . $e->getMessage(); }
    }
}

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM audiencias WHERE id = ?"); $stmt->execute([$id]); $registro = $stmt->fetch();
}
?>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-gavel"></i> Detalhes da Audiência</h3><a href="index.php" class="btn btn-sm btn-blue">Voltar</a></div>
    <div class="panel-body">
        <?php if($erro): ?> <div class="alert alert-danger"><?php echo $erro; ?></div> <?php endif; ?>
        <?php if($sucesso): ?> <div class="alert alert-success"><?php echo $sucesso; ?></div> <?php endif; ?>

        <form action="form.php<?php echo $id ? "?id=$id" : ""; ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Processo / Cliente *</label>
                    <select name="id_processo_cliente" class="form-control" required>
                        <option value="">-- Selecione --</option>
                        <?php foreach($processos as $p): ?>
                            <?php $val = $p['id'] . '|' . $p['id_cliente']; ?>
                            <option value="<?php echo $val; ?>" <?php echo (isset($registro['id_processo']) && $registro['id_processo'] == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['numero_processo'] . ' - ' . $p['cliente_nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Data *</label><input type="date" name="data_audiencia" class="form-control" required value="<?php echo $registro['data_audiencia'] ?? ''; ?>"></div>
                <div class="form-group"><label>Hora *</label><input type="time" name="hora_audiencia" class="form-control" required value="<?php echo $registro['hora_audiencia'] ?? ''; ?>"></div>
                <div class="form-group"><label>Local</label><input type="text" name="local_audiencia" class="form-control" value="<?php echo htmlspecialchars($registro['local_audiencia'] ?? ''); ?>"></div>
                <div class="form-group"><label>Tipo de Audiência</label><input type="text" name="tipo_audiencia" class="form-control" placeholder="Conciliação, Instrução..." value="<?php echo htmlspecialchars($registro['tipo_audiencia'] ?? ''); ?>"></div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="agendada" <?php echo (isset($registro['status']) && $registro['status'] == 'agendada') ? 'selected' : ''; ?>>Agendada</option>
                        <option value="realizada" <?php echo (isset($registro['status']) && $registro['status'] == 'realizada') ? 'selected' : ''; ?>>Realizada</option>
                        <option value="cancelada" <?php echo (isset($registro['status']) && $registro['status'] == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                </div>
                <div class="form-group full-width"><label>Observações</label><textarea name="observacoes" class="form-control"><?php echo htmlspecialchars($registro['observacoes'] ?? ''); ?></textarea></div>
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button></div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
