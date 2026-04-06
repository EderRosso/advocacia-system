<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand" style="display: flex; justify-content: space-between; align-items: center; padding-right: 15px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-balance-scale"></i>
            <span>AdvSystem</span>
        </div>
        <button id="closeSidebar" class="close-sidebar-btn" title="Fechar Menu">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?= BASE_URL ?>dashboard.php">
                    <i class="fas fa-home"></i> <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>pages/agenda/index.php">
                    <i class="fas fa-calendar-check"></i> <span>Agenda Legal</span>
                </a>
            </li>
            <?php if(tem_permissao('clientes')): ?>
            <li>
                <a href="<?= BASE_URL ?>pages/clientes/index.php">
                    <i class="fas fa-users"></i> <span>Clientes</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if(tem_permissao('processos')): ?>
            <li>
                <a href="<?= BASE_URL ?>pages/processos/index.php">
                    <i class="fas fa-folder-open"></i> <span>Processos</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if(tem_permissao('audiencias')): ?>
            <li>
                <a href="<?= BASE_URL ?>pages/audiencias/index.php">
                    <i class="fas fa-gavel"></i> <span>Audiências</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if(tem_permissao('prazos')): ?>
            <li>
                <a href="<?= BASE_URL ?>pages/prazos/index.php">
                    <i class="fas fa-calendar-alt"></i> <span>Prazos</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if(tem_permissao('tarefas')): ?>
            <li>
                <a href="<?= BASE_URL ?>pages/tarefas/index.php">
                    <i class="fas fa-tasks"></i> <span>Tarefas</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if(tem_permissao('honorarios')): ?>
            <li>
                <a href="<?= BASE_URL ?>pages/honorarios/index.php">
                    <i class="fas fa-file-invoice-dollar"></i> <span>Honorários</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>pages/orcamentos/index.php">
                    <i class="fas fa-file-signature"></i> <span>Orçamentos/Propostas</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if(tem_permissao('documentos')): ?>
            <li>
                <a href="<?= BASE_URL ?>pages/documentos/index.php">
                    <i class="fas fa-file-alt"></i> <span>Documentos</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($_SESSION['usuario_perfil'] === 'administrador'): ?>
            <li>
                <a href="<?= BASE_URL ?>pages/usuarios/index.php">
                    <i class="fas fa-user-shield"></i> <span>Usuários</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> <span>Sair do Sistema</span>
        </a>
    </div>
</aside>
