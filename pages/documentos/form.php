<?php
$page_title = 'Cadastro de Documento';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';

$id = $_GET['id'] ?? null; $registro = null; $erro = ''; $sucesso = '';
$clientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC")->fetchAll();
$processos = $pdo->query("SELECT id, numero_processo FROM processos")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null; $titulo = trim($_POST['titulo']); 
    $id_cliente = !empty($_POST['id_cliente']) ? $_POST['id_cliente'] : null;
    $id_processo = !empty($_POST['id_processo']) ? $_POST['id_processo'] : null;
    $tipo_documento = trim($_POST['tipo_documento']); $descricao = trim($_POST['descricao']);

    if (empty($titulo)) { $erro = 'O título é obrigatório.'; } else {
        try {
            // Lógica de Upload do Arquivo
            $caminho_final = null; // Inicialmente sem arquivo novo
            
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                // Configurar o diretório absoluto de uploads
                $dir_uploads = __DIR__ . '/../../uploads/documentos/';
                if (!is_dir($dir_uploads)) { @mkdir($dir_uploads, 0755, true); } // Cria pasta se não existir
                
                $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
                $arquivos_permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'rar']; // Filtro de segurança
                
                if (in_array($ext, $arquivos_permitidos)) {
                    $novoNome = uniqid('doc_') . '_' . time() . '.' . $ext; // Evita arquivos com mesmo nome substituírem outros
                    if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $dir_uploads . $novoNome)) {
                        $caminho_final = 'uploads/documentos/' . $novoNome; // Salva o caminho relativo no BD
                    } else {
                        $erro = 'Proteção de Servidor: Falha ao alocar o arquivo físico no disco.';
                    }
                } else {
                    $erro = 'Formato Recusado! Envie apenas PDF, Imagens(JPG/PNG), Word(DOCX) ou ZIP.';
                }
            }

            if (empty($erro)) { // Se não houve erro de arquivo
                if ($id) {
                    // Update: se enviou arquivo novo, subscreve. Se não, ignora o update do file
                    if ($caminho_final) {
                        $stmt = $pdo->prepare("UPDATE documentos SET titulo=?, id_cliente=?, id_processo=?, tipo_documento=?, descricao=?, caminho_arquivo=? WHERE id=?");
                        $stmt->execute([$titulo, $id_cliente, $id_processo, $tipo_documento, $descricao, $caminho_final, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE documentos SET titulo=?, id_cliente=?, id_processo=?, tipo_documento=?, descricao=? WHERE id=?");
                        $stmt->execute([$titulo, $id_cliente, $id_processo, $tipo_documento, $descricao, $id]);
                    }
                    $sucesso = 'Documento atualizado com sucesso.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO documentos (titulo, id_cliente, id_processo, tipo_documento, descricao, caminho_arquivo) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$titulo, $id_cliente, $id_processo, $tipo_documento, $descricao, $caminho_final]);
                    $sucesso = '☁️ Ficha e Arquivo armazenados na nuvem com segurança.'; 
                    $id = $pdo->lastInsertId();
                }
            }
        } catch(PDOException $e) { $erro = "Erro de Banco: " . $e->getMessage(); }
    }
}
if ($id) { 
    $stmt = $pdo->prepare("SELECT * FROM documentos WHERE id = ?"); 
    $stmt->execute([$id]); 
    $registro = $stmt->fetch(); 
} else {
    $registro['titulo'] = $_GET['titulo'] ?? '';
    $registro['tipo_documento'] = $_GET['titulo'] ?? ''; // Can use same for type
    $registro['id_processo'] = $_GET['id_processo'] ?? '';
    $registro['id_cliente'] = $_GET['id_cliente'] ?? '';
}
?>
<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-file-alt"></i> Detalhes do Documento</h3><a href="index.php" class="btn btn-sm btn-blue">Voltar</a></div>
    <div class="panel-body">
        <?php if($erro): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
        <?php if($sucesso): ?><div class="alert alert-success"><?php echo $sucesso; ?></div><?php endif; ?>
        <form action="form.php<?php echo $id ? "?id=$id" : ""; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-grid">
                <div class="form-group full-width"><label>Título do Documento *</label><input type="text" name="titulo" class="form-control" required value="<?php echo htmlspecialchars($registro['titulo'] ?? ''); ?>"></div>
                <div class="form-group"><label>Cliente Vinculado</label><select name="id_cliente" class="form-control"><option value="">-- Nenhum --</option><?php foreach($clientes as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo (isset($registro['id_cliente']) && $registro['id_cliente'] == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['nome']); ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Processo Vinculado</label><select name="id_processo" class="form-control"><option value="">-- Nenhum --</option><?php foreach($processos as $p): ?><option value="<?php echo $p['id']; ?>" <?php echo (isset($registro['id_processo']) && $registro['id_processo'] == $p['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['numero_processo']); ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Tipo de Documento</label><input type="text" name="tipo_documento" class="form-control" placeholder="Procuração, Petição..." value="<?php echo htmlspecialchars($registro['tipo_documento'] ?? ''); ?>"></div>
                <div class="form-group full-width" style="background:#e3f2fd; padding:15px; border-radius:5px; border-left:4px solid var(--primary-color);">
                    <label style="color:var(--primary-color);"><i class="fas fa-cloud-upload-alt"></i> Armazenamento (Upload Privado)</label>
                    <input type="file" name="arquivo" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.rar">
                    <small style="color:#555;">Arquivos aceitos: PDF, Word, Imagens e ZIP. Ficará protegido nos servidores.</small>
                    
                    <?php if(!empty($registro['caminho_arquivo'])): ?>
                        <div style="margin-top: 15px; background: #fff; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                            <i class="fas fa-check-circle" style="color:#28a745;"></i> Existe um arquivo anexado no banco para este registro.<br>
                            <a href="<?php echo BASE_URL . htmlspecialchars($registro['caminho_arquivo']); ?>" target="_blank" class="btn btn-sm btn-green" style="margin-top:5px;"><i class="fas fa-download"></i> Baixar Arquivo em Nuvem</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group full-width"><label>Descrição / Observações</label><textarea name="descricao" class="form-control"><?php echo htmlspecialchars($registro['descricao'] ?? ''); ?></textarea></div>
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Registro</button></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
