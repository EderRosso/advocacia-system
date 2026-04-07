<?php
require_once __DIR__ . '/auth.php';
verifica_login();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advocacia System</title>
    <!-- PWA e Ícones Mobile -->
    <link rel="manifest" href="<?= BASE_URL ?>manifest.php">
    <meta name="theme-color" content="#0D8ABC">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>assets/img/icon-192x192.png">
    <!-- Fim PWA -->
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?php echo time(); ?>">
    
    <!-- SweetAlert2 & Tema Global -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Aplica o tema dark do localStorage antes da página renderizar (Evita Flash Branco)
        const savedTheme = localStorage.getItem('advTheme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
    <div class="layout-wrapper">
        <!-- Sidebar Menu -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <?php include __DIR__ . '/menu.php'; ?>

        <div class="main-content">
            <!-- Headerbarra superior -->
            <header class="top-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h2>
                </div>
                <div class="header-right" style="display:flex; align-items:center; gap: 20px;">
                    <button id="themeToggleBtn" style="background:transparent; border:none; cursor:pointer; font-size:18px; color:var(--text-color);" title="Modo Noturno">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>
                    <div class="user-info">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['usuario_nome']); ?>&background=0D8ABC&color=fff" alt="Avatar" class="avatar">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                        <span class="user-role"><?php echo ucfirst(htmlspecialchars($_SESSION['usuario_perfil'])); ?></span>
                    </div>
                </div>
            </header>
            
            <main class="content-container">
