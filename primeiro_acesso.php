<?php
require_once __DIR__ . '/includes/auth.php';
verifica_login(); // Garante que o usuário está logado na sessão

// Se não for primeiro acesso, expulsa para o dashboard (evita entrar de abelhudo)
if (empty($_SESSION['primeiro_acesso'])) {
    header("Location: dashboard.php");
    exit();
}

$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nova_senha = $_POST['nova_senha'];
    $confirma_senha = $_POST['confirma_senha'];

    if (empty($nova_senha) || empty($confirma_senha)) {
        $erro = "Por favor, preencha as duas senhas.";
    } elseif (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $nova_senha)) {
        $erro = "A nova senha deve ter pelo menos 8 caracteres, contendo letra Maiúscula, minúscula, número e um símbolo (Ex: @#$%!).";
    } elseif ($nova_senha !== $confirma_senha) {
        $erro = "As senhas não coincidem. Tente novamente.";
    } else {
        // Sucesso: Atualizar no banco e registrar que o primeiro acesso passou
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = ?, primeiro_acesso = 0 WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['usuario_id']]);

            // Atualiza a sessão e libera o acesso ao dashboard
            $_SESSION['primeiro_acesso'] = 0;
            header("Location: dashboard.php?sucesso_senha=1");
            exit();
        } catch (PDOException $e) {
            $erro = "Erro no servidor ao salvar a senha: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trava de Segurança - Advocacia System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .warning-text {
            color: #d9534f;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-logo">
            <i class="fas fa-shield-alt" style="color: #f39c12"></i>
            <h2>Segurança</h2>
            <p>Primeiro Acesso ao Sistema</p>
        </div>
        
        <div class="login-box">
            <div style="text-align: center; margin-bottom: 20px;">
                <h3 style="margin-bottom: 5px;">Olá, <?php echo explode(' ', $_SESSION['usuario_nome'])[0]; ?>! 👋</h3>
                <p class="warning-text">
                    Por motivos de segurança, você não pode continuar com a senha provisória recebida por e-mail.<br><br>
                    <strong>Por favor, crie uma senha pessoal definitiva agora.</strong>
                </p>
            </div>
            
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger" style="margin-top:20px; margin-bottom:20px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <form action="primeiro_acesso.php" method="POST" class="form-login">
                <div class="form-group">
                    <label for="nova_senha">Nova Senha Definitiva</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="nova_senha" id="nova_senha" class="form-control" placeholder="Min. 8 carac. Maiúsculas e Símbolos" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirma_senha">Confirme a Nova Senha</label>
                    <div class="input-icon">
                        <i class="fas fa-check-double"></i>
                        <input type="password" name="confirma_senha" id="confirma_senha" class="form-control" placeholder="Repita a senha" required>
                    </div>
                    <div style="margin-top: 10px; text-align: right;">
                        <label style="font-weight: normal; font-size: 13px; color: #555; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <input type="checkbox" id="mostrar-senha" onclick="toggleSenha()"> Mostrar senhas
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" style="background-color: #f39c12; border-color: #e67e22;">
                    Cadastrar e Entrar no Dashboard <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </form>
            
            <div class="login-footer" style="margin-top:20px;">
                <p><a href="logout.php" style="color:#d9534f; text-decoration:none;"><i class="fas fa-sign-out-alt"></i> Cancelar e Sair</a></p>
            </div>
        </div>
    </div>
    
    <script>
        function toggleSenha() {
            const inputNova = document.getElementById("nova_senha");
            const inputConfirma = document.getElementById("confirma_senha");
            if (inputNova.type === "password") {
                inputNova.type = "text";
                inputConfirma.type = "text";
            } else {
                inputNova.type = "password";
                inputConfirma.type = "password";
            }
        }
    </script>
</body>
</html>
