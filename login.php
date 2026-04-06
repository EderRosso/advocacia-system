<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/config/conexao.php';

// Segurança: Proteção contra Força Bruta (Rate Limiting)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

$lockout_time = 300; // 5 minutos
$max_attempts = 5;
$erro = '';

if ($_SESSION['login_attempts'] >= $max_attempts) {
    if (time() - $_SESSION['last_attempt_time'] < $lockout_time) {
        $minutos_restantes = ceil(($lockout_time - (time() - $_SESSION['last_attempt_time'])) / 60);
        $erro = "Acesso temporariamente bloqueado por excesso de tentativas falhas. Tente novamente em {$minutos_restantes} minuto(s).";
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($erro)) {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    }
    else {
        $stmt = $pdo->prepare("SELECT id, nome, senha, perfil, status, acessos, primeiro_acesso FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            if ($user['status'] == 'inativo') {
                $erro = "Usuário inativo. Contate o administrador.";
            }
            elseif (password_verify($senha, $user['senha'])) {
                // Sucesso: Prevenção contra Fixação de Sessão e reset das tentativas
                session_regenerate_id(true);
                $_SESSION['login_attempts'] = 0;
                
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['usuario_perfil'] = $user['perfil'];
                $_SESSION['usuario_acessos'] = json_decode($user['acessos'] ?? '[]', true) ?? [];
                $_SESSION['primeiro_acesso'] = $user['primeiro_acesso'];

                if ($user['primeiro_acesso']) {
                    header("Location: primeiro_acesso.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            }
            else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $erro = "Email ou senha incorretos.";
            }
        }
        else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $erro = "Email ou senha incorretos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AdvSystem</title>
    <!-- PWA -->
    <link rel="manifest" href="manifest.php">
    <meta name="theme-color" content="#0D8ABC">
    <link rel="apple-touch-icon" href="assets/img/icon-192x192.png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-logo">
            <i class="fas fa-balance-scale"></i>
            <h2>AdvSystem</h2>
            <p>Gerenciamento Jurídico</p>
        </div>
        
        <div class="login-box">
            <h3>Acesso Restrito</h3>
            <p class="text-muted">Entre com suas credenciais para continuar</p>
            
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger" style="margin-top:20px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php
endif; ?>

            <form action="login.php" method="POST" class="form-login">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email" class="form-control" placeholder="admin@advocacia.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="senha" id="senha" class="form-control" placeholder="Sua senha" required>
                    </div>
                    <div style="margin-top: 10px; text-align: right;">
                        <label style="font-weight: normal; font-size: 13px; color: #555; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <input type="checkbox" id="mostrar-senha" onclick="toggleSenha()"> Mostrar a senha
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Entrar no Sistema <i class="fas fa-sign-in-alt ml-2"></i>
                </button>
            </form>
            
            <button id="pwa-install-btn" class="btn btn-block" style="display: none; margin-top: 15px; background-color: #000; color: #fff; border: none; padding: 12px; font-weight: 600; cursor: pointer; border-radius: 4px;">
                <i class="fas fa-mobile-alt"></i> Instalar App no Dispositivo
            </button>
            
            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> Advocacia System. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Lógica de Mostrar Senha
        function toggleSenha() {
            const inputSenha = document.getElementById("senha");
            if (inputSenha.type === "password") {
                inputSenha.type = "text";
            } else {
                inputSenha.type = "password";
            }
        }

        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                .then(reg => console.log('SW registrado', reg))
                .catch(err => console.log('SW falhou', err));
            });
        }

        // Lógica do Botão de Instalação (PWA)
        let deferredPrompt;
        const installBtn = document.getElementById('pwa-install-btn');

        window.addEventListener('beforeinstallprompt', (e) => {
            // Previne o mini-infobar padrão de aparecer no celular (opcional)
            e.preventDefault();
            // Guarda o evento para acionar quando o usuário clicar no botão
            deferredPrompt = e;
            // Mostra nosso botão estilizado
            installBtn.style.display = 'block';
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                // Mostra a tela de confirmação de instalação do próprio Chrome/Celular
                deferredPrompt.prompt();
                // Espera o usuário responder
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    console.log('Usuário aceitou a instalação.');
                    installBtn.style.display = 'none'; // Some com o botão
                }
                deferredPrompt = null;
            }
        });

        // Se já instalou com sucesso, some o botão de vez
        window.addEventListener('appinstalled', () => {
            installBtn.style.display = 'none';
        });
    </script>
</body>
</html>
