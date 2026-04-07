<?php
$page_title = 'Cadastro de Usuário';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_admin();

$id = $_GET['id'] ?? null; $registro = null; $erro = ''; $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null; $nome = trim($_POST['nome']); $email = trim($_POST['email']);
    $senha = $_POST['senha']; $perfil = $_POST['perfil']; $status = $_POST['status'];
    $acessos = isset($_POST['acessos']) ? json_encode($_POST['acessos']) : '[]';

    if (empty($nome) || empty($email)) { $erro = 'Nome e email são obrigatórios.'; } else {
        try {
            if ($id) {
                if (!empty($senha)) {
                    if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $senha)) {
                        $erro = "A nova senha deve ter pelo menos 8 caracteres, contendo letra Maiúscula, minúscula, número e um símbolo.";
                    } else {
                        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, email=?, senha=?, perfil=?, status=?, acessos=? WHERE id=?");
                        $stmt->execute([$nome, $email, $senhaHash, $perfil, $status, $acessos, $id]);
                        $sucesso = 'Atualizado com nova senha robusta.';
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, email=?, perfil=?, status=?, acessos=? WHERE id=?");
                    $stmt->execute([$nome, $email, $perfil, $status, $acessos, $id]);
                    $sucesso = 'Atualizado (Senha mantida).';
                }
            } else {
                if (empty($senha)) { $erro = 'Senha é obrigatória para novo usuário.'; }
                elseif (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $senha)) {
                    $erro = "A senha deve ter pelo menos 8 caracteres, contendo letra Maiúscula, minúscula, número e um símbolo.";
                }
                else {
                    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, perfil, status, acessos) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nome, $email, $senhaHash, $perfil, $status, $acessos]);
                    $sucesso = 'Usuário cadastrado com sucesso.'; 
                    $id = $pdo->lastInsertId();
                    
                    // Disparo do Email de Boas Vindas
                    require_once __DIR__ . '/../../includes/mailer.php';
                    $res_email = enviar_email_boas_vindas($email, $nome, $senha);
                    
                    if ($res_email === true) {
                        $sucesso .= ' As credenciais (senha) foram enviadas para o e-mail do usuário!';
                    } else {
                        $erro_alerta = 'Usuário salvo no sistema, mas o envio do e-mail falhou (O servidor SMTP não está configurado): ' . htmlspecialchars($res_email);
                    }
                }
            }
        } catch(PDOException $e) { 
            if($e->getCode() == 23000) { $erro = "Email já cadastrado."; }
            else { $erro = "Erro: " . $e->getMessage(); }
        }
    }
}
if ($id) { $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?"); $stmt->execute([$id]); $registro = $stmt->fetch(); }
?>
<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-user-shield"></i> Detalhes do Usuário</h3><a href="index.php" class="btn btn-sm btn-blue">Voltar</a></div>
    <div class="panel-body">
        <?php if($erro): ?><div class="alert alert-danger" style="margin-bottom:15px;"><?php echo $erro; ?></div><?php endif; ?>
        <?php if(!empty($erro_alerta)): ?><div class="alert alert-warning" style="margin-bottom:15px;"><i class="fas fa-exclamation-triangle"></i> <?php echo $erro_alerta; ?></div><?php endif; ?>
        <?php if($sucesso): ?><div class="alert alert-success" style="margin-bottom:15px;"><i class="fas fa-check-circle"></i> <?php echo $sucesso; ?></div><?php endif; ?>
        <form action="form.php<?php echo $id ? "?id=$id" : ""; ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-grid">
                <div class="form-group"><label>Nome *</label><input type="text" name="nome" class="form-control" required value="<?php echo htmlspecialchars($registro['nome'] ?? ''); ?>"></div>
                <div class="form-group"><label>E-mail *</label><input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($registro['email'] ?? ''); ?>"></div>
                <div class="form-group"><label>Senha <?php echo $id ? '(Deixe em branco para manter)' : '*'; ?></label><input type="password" name="senha" class="form-control" placeholder="Min 8. carac. (Maiúsculas e Símbolos)"></div>
                <div class="form-group"><label>Perfil</label><select name="perfil" class="form-control" id="perfil_select"><option value="usuario" <?php echo (isset($registro['perfil']) && $registro['perfil'] == 'usuario') ? 'selected' : ''; ?>>Usuário Comum</option><option value="administrador" <?php echo (isset($registro['perfil']) && $registro['perfil'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option></select></div>
                <div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="ativo" <?php echo (isset($registro['status']) && $registro['status'] == 'ativo') ? 'selected' : ''; ?>>Ativo</option><option value="inativo" <?php echo (isset($registro['status']) && $registro['status'] == 'inativo') ? 'selected' : ''; ?>>Inativo</option></select></div>
                
                <?php 
                $modulos = [
                    'clientes' => 'Clientes', 'processos' => 'Processos', 'audiencias' => 'Audiências',
                    'prazos' => 'Prazos', 'tarefas' => 'Tarefas', 'honorarios' => 'Honorários', 'documentos' => 'Documentos'
                ];
                $acessos_usuario = isset($registro['acessos']) ? (json_decode($registro['acessos'], true) ?? []) : [];
                $esconder_acesso = (isset($registro['perfil']) && $registro['perfil'] == 'administrador') ? 'display:none;' : '';
                ?>
                <div class="form-group full-width" id="box-acessos" style="<?php echo $esconder_acesso; ?>">
                    <label>Permissões Específicas (quais módulos ele pode ver e editar)</label>
                    <div style="display:flex; flex-wrap:wrap; gap:15px; margin-top:10px; background:#f9f9f9; padding:15px; border-radius:5px; border:1px solid #ddd;">
                        <?php foreach($modulos as $chave => $nome_modulo): ?>
                            <label style="font-weight:normal; display:flex; align-items:center; gap:5px; cursor:pointer; color: #333333 !important;">
                                <input type="checkbox" name="acessos[]" value="<?php echo $chave; ?>" <?php echo in_array($chave, $acessos_usuario) ? 'checked' : ''; ?>>
                                <?php echo $nome_modulo; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button></div>
        </form>
    </div>
</div>

<script>
document.getElementById('perfil_select').addEventListener('change', function() {
    document.getElementById('box-acessos').style.display = this.value === 'administrador' ? 'none' : 'block';
});
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
