<?php
$page_title = 'Dashboard Central';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/conexao.php';

// Coletar estatísticas básicas
try {
    $total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    $total_processos = $pdo->query("SELECT COUNT(*) FROM processos WHERE status = 'ativo'")->fetchColumn();
    $total_tarefas = $pdo->query("SELECT COUNT(*) FROM tarefas")->fetchColumn();
    $total_documentos = $pdo->query("SELECT COUNT(*) FROM documentos")->fetchColumn();
    $total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE status = 'ativo'")->fetchColumn();
    $honorarios_pendentes = $pdo->query("SELECT COALESCE(SUM(valor), 0) FROM honorarios WHERE status = 'pendente'")->fetchColumn();
    $orcamentos_pendentes = $pdo->query("SELECT COUNT(*) FROM orcamentos WHERE status = 'pendente'")->fetchColumn();
    
    // Audiências próximas (próximos 30 dias)
    $stmt_audi = $pdo->prepare("SELECT a.data_audiencia, a.hora_audiencia, a.local_audiencia, c.nome, p.numero_processo 
                               FROM audiencias a 
                               JOIN clientes c ON a.id_cliente = c.id
                               JOIN processos p ON a.id_processo = p.id
                               WHERE a.status = 'agendada' AND a.data_audiencia >= CURDATE() AND a.data_audiencia <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                               ORDER BY a.data_audiencia ASC LIMIT 5");
    $stmt_audi->execute();
    $audiencias_prox = $stmt_audi->fetchAll();

    // Prazos próximos
    $stmt_praz = $pdo->prepare("SELECT pr.descricao_prazo, pr.data_limite, p.numero_processo 
                               FROM prazos pr
                               JOIN processos p ON pr.id_processo = p.id
                               WHERE pr.status = 'pendente' AND pr.data_limite >= CURDATE() AND pr.data_limite <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)
                               ORDER BY pr.data_limite ASC LIMIT 5");
    $stmt_praz->execute();
    $prazos_prox = $stmt_praz->fetchAll();

    // Honorários em Atraso ou Vencendo em breve (até 15 dias)
    $stmt_h_prox = $pdo->prepare("SELECT h.tipo_honorario, h.valor, h.data_vencimento, c.nome as cliente_nome 
                                FROM honorarios h
                                JOIN clientes c ON h.id_cliente = c.id
                                WHERE h.status = 'pendente' AND h.data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)
                                ORDER BY h.data_vencimento ASC LIMIT 5");
    $stmt_h_prox->execute();
    $honorarios_alerta = $stmt_h_prox->fetchAll();

    // LexFlow - Análise de Carga de Trabalho
    $stmt_carga = $pdo->query("
        SELECT u.nome, 
               (SELECT COUNT(*) FROM prazos pr JOIN processos p ON pr.id_processo=p.id WHERE pr.status='pendente' AND p.id_advogado=u.id) as total_prazos,
               (SELECT COUNT(*) FROM tarefas t WHERE t.id_responsavel=u.id AND t.id_quadro != 3) as total_tarefas,
               (SELECT COUNT(*) FROM prazos pr JOIN processos p ON pr.id_processo=p.id WHERE pr.status='pendente' AND pr.data_limite < CURDATE() AND p.id_advogado=u.id) as prazos_vencidos
        FROM usuarios u
        WHERE u.status = 'ativo'
        ORDER BY (total_prazos + total_tarefas) DESC
    ");
    $carga_trabalho = $stmt_carga->fetchAll();

} catch (PDOException $e) {
    echo "Erro na recuperação de dados: " . $e->getMessage();
}
?>

<?php if (isset($_GET['sucesso_senha']) && $_GET['sucesso_senha'] == 1): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> <strong>Tudo certo!</strong> Sua senha definitiva foi cadastrada com sucesso.
    </div>
<?php endif; ?>

<div class="dashboard-cards">
    <!-- Card 1 -->
    <?php if (tem_permissao('clientes')): ?>
    <a href="pages/clientes/index.php" class="card card-stats card-link">
        <div class="card-icon blue-bg">
            <i class="fas fa-users"></i>
        </div>
        <div class="card-info">
            <p class="text-muted">Total Clientes</p>
            <h3 class="animate-number" data-target="<?php echo $total_clientes; ?>">0</h3>
        </div>
    </a>
    <?php endif; ?>
    
    <!-- Card 2 -->
    <?php if (tem_permissao('processos')): ?>
    <a href="pages/processos/index.php" class="card card-stats card-link">
        <div class="card-icon gold-bg">
            <i class="fas fa-folder-open"></i>
        </div>
        <div class="card-info">
            <p class="text-muted">Processos Ativos</p>
            <h3 class="animate-number" data-target="<?php echo $total_processos; ?>">0</h3>
        </div>
    </a>
    <?php endif; ?>
    
    <!-- Card 3 -->
    <?php if (tem_permissao('honorarios')): ?>
    <a href="pages/honorarios/index.php" class="card card-stats card-link">
        <div class="card-icon green-bg">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="card-info" style="flex: 1;">
            <p class="text-muted">Honorários a Receber</p>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 5px;">
                <h3 id="valor-honorarios" data-valor="<?php echo moeda_br($honorarios_pendentes); ?>" style="margin-top: 0;">R$ *****</h3>
                <span onclick="toggleValor(event)" style="cursor: pointer; padding: 5px; color: #aaa; font-size: 16px; transition: 0.2s;" onmouseover="this.style.color='#333'" onmouseout="this.style.color='#aaa'" title="Mostrar/Ocultar Valor">
                    <i class="fas fa-eye-slash" id="eye-icon"></i>
                </span>
            </div>
        </div>
    </a>
    <?php endif; ?>

    <!-- Card 4 (Orçamentos) -->
    <?php if (tem_permissao('honorarios')): ?>
    <a href="pages/orcamentos/index.php" class="card card-stats card-link">
        <div class="card-icon" style="background-color: #1F6E8C;">
            <i class="fas fa-file-signature" style="color:#fff;"></i>
        </div>
        <div class="card-info">
            <p class="text-muted">Propostas Pendentes</p>
            <h3 class="animate-number" data-target="<?php echo $orcamentos_pendentes; ?>">0</h3>
        </div>
    </a>
    <?php endif; ?>

    <!-- Card 5 -->
    <?php if (tem_permissao('prazos') || tem_permissao('audiencias')): ?>
    <a href="pages/prazos/index.php" class="card card-stats card-link">
        <div class="card-icon red-bg">
            <i class="fas fa-calendar-times"></i>
        </div>
        <div class="card-info">
             <p class="text-muted">Prazos e Audiências</p>
            <h3 class="animate-number" data-target="<?php echo count($audiencias_prox) + count($prazos_prox); ?>">0</h3>
        </div>
    </a>
    <?php endif; ?>

    <!-- Card 5 -->
    <?php if (tem_permissao('tarefas')): ?>
    <a href="pages/tarefas/index.php" class="card card-stats card-link">
        <div class="card-icon blue-bg" style="background-color: #5C6BC0;"> <!-- Roxo Claro -->
            <i class="fas fa-tasks"></i>
        </div>
        <div class="card-info">
             <p class="text-muted">Tarefas Ativas</p>
            <h3 class="animate-number" data-target="<?php echo $total_tarefas; ?>">0</h3>
        </div>
    </a>
    <?php endif; ?>

    <!-- Card 6 -->
    <?php if (tem_permissao('documentos')): ?>
    <a href="pages/documentos/index.php" class="card card-stats card-link">
        <div class="card-icon" style="background-color: #FF9800;"> <!-- Laranja -->
            <i class="fas fa-file-alt" style="color:#fff;"></i>
        </div>
        <div class="card-info">
             <p class="text-muted">Documentos Guardados</p>
            <h3 class="animate-number" data-target="<?php echo $total_documentos; ?>">0</h3>
        </div>
    </a>
    <?php endif; ?>

    <!-- Card 7 -->
    <?php if ($_SESSION['usuario_perfil'] === 'administrador'): ?>
    <a href="pages/usuarios/index.php" class="card card-stats card-link">
        <div class="card-icon" style="background-color: #607D8B;"> <!-- Cinza Azulado -->
            <i class="fas fa-user-shield" style="color:#fff;"></i>
        </div>
        <div class="card-info">
             <p class="text-muted">Usuários Ativos</p>
            <h3 class="animate-number" data-target="<?php echo $total_usuarios; ?>">0</h3>
        </div>
    </a>
    <?php endif; ?>
</div>

<div class="dashboard-grid">
    <?php if (tem_permissao('audiencias')): ?>
    <div class="panel">
        <div class="panel-header">
            <h3><i class="fas fa-gavel text-gold"></i> Próximas Audiências</h3>
        </div>
        <div class="panel-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Cliente</th>
                        <th>Processo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($audiencias_prox) > 0): ?>
                        <?php foreach($audiencias_prox as $a): 
                            $data_in = date('Ymd\THis', strtotime($a['data_audiencia'] . ' ' . $a['hora_audiencia']));
                            $data_fim = date('Ymd\THis', strtotime($a['data_audiencia'] . ' ' . $a['hora_audiencia'] . ' +1 hour'));
                            $tit = urlencode("Audiência: " . $a['nome']);
                            $desc = urlencode("Processo: " . $a['numero_processo'] . "\nCliente: " . $a['nome']);
                            $loc = urlencode($a['local_audiencia'] ?? 'Não informado');
                            $link_google = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$tit}&dates={$data_in}/{$data_fim}&details={$desc}&location={$loc}";
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($a['data_audiencia'])) . ' ' . $a['hora_audiencia']; ?></td>
                            <td><?php echo htmlspecialchars($a['nome']); ?></td>
                            <td><?php echo htmlspecialchars($a['numero_processo']); ?></td>
                            <td>
                                <a href="<?php echo $link_google; ?>" target="_blank" class="btn btn-sm" style="background-color: #4285F4; color: white; padding: 4px 8px; border-radius: 4px;" title="Adicionar ao Google Agenda">
                                    <i class="fab fa-google"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Nenhuma audiência próxima.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (tem_permissao('prazos')): ?>
    <div class="panel">
        <div class="panel-header">
            <h3><i class="fas fa-exclamation-triangle text-red"></i> Prazos a Vencer</h3>
        </div>
        <div class="panel-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Prazo Limite</th>
                        <th>Descrição</th>
                        <th>Processo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($prazos_prox) > 0): ?>
                        <?php foreach($prazos_prox as $pr): 
                            $data_in = date('Ymd', strtotime($pr['data_limite']));
                            $data_fim = date('Ymd', strtotime($pr['data_limite'] . ' +1 day'));
                            $tit = urlencode("Prazo: " . $pr['descricao_prazo']);
                            $desc = urlencode("Processo: " . $pr['numero_processo'] . "\nPrazo: " . $pr['descricao_prazo']);
                            $link_google = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$tit}&dates={$data_in}/{$data_fim}&details={$desc}";
                        ?>
                        <tr>
                            <td>
                                <span class="badge badge-warning">
                                    <?php echo date('d/m/Y', strtotime($pr['data_limite'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($pr['descricao_prazo']); ?></td>
                            <td><?php echo htmlspecialchars($pr['numero_processo']); ?></td>
                            <td>
                                <a href="<?php echo $link_google; ?>" target="_blank" class="btn btn-sm" style="background-color: #4285F4; color: white; padding: 4px 8px; border-radius: 4px;" title="Adicionar ao Google Agenda">
                                    <i class="fab fa-google"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Nenhum prazo próximo.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (tem_permissao('honorarios')): ?>
    <div class="panel">
        <div class="panel-header">
            <h3><i class="fas fa-file-invoice-dollar text-green"></i> Faturas / Parcelas em Alerta</h3>
        </div>
        <div class="panel-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Vencimento</th>
                        <th>Cliente</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($honorarios_alerta) > 0): ?>
                        <?php foreach($honorarios_alerta as $h_alt): 
                            $vencido = (strtotime($h_alt['data_vencimento']) < strtotime(date('Y-m-d')));
                        ?>
                        <tr>
                            <td>
                                <?php if($vencido): ?>
                                    <span class="badge badge-danger" title="Atrasado!">
                                        <i class="fas fa-exclamation-circle"></i> <?php echo date('d/m/Y', strtotime($h_alt['data_vencimento'])); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">
                                        <?php echo date('d/m/Y', strtotime($h_alt['data_vencimento'])); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($h_alt['cliente_nome']); ?></strong></td>
                            <td><?php echo htmlspecialchars($h_alt['tipo_honorario']); ?></td>
                            <td><span style="color: #28a745; font-weight: bold;">R$ <?php echo number_format($h_alt['valor'], 2, ',', '.'); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">Nenhuma parcela em atraso ou vencendo em breve.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="text-align: right; padding-top: 10px;">
                <a href="pages/honorarios/index.php" class="link-honorarios">Ver todos os Honorários <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($_SESSION['usuario_perfil'] === 'administrador'): ?>
<div class="panel" style="margin-top: 25px; border-left: 4px solid var(--primary-color);">
    <div class="panel-header">
        <h3><i class="fas fa-chart-line text-blue"></i> LexFlow Analytics - Carga de Trabalho da Equipe</h3>
    </div>
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Advogado/Usuário</th>
                    <th>Prazos Pendentes</th>
                    <th>Prazos Vencidos</th>
                    <th>Tarefas em Aberto</th>
                    <th>Nível de Sobrecarga</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($carga_trabalho) > 0): ?>
                    <?php foreach($carga_trabalho as $ct): 
                        $total_pontos = $ct['total_prazos'] + $ct['total_tarefas'] + ($ct['prazos_vencidos'] * 3);
                        $bg = '#d4edda'; $cor = '#155724'; $lbl = 'Leve';
                        if ($total_pontos >= 10) { $bg = '#fff3cd'; $cor = '#856404'; $lbl = 'Moderada'; }
                        if ($total_pontos >= 20) { $bg = '#f8d7da'; $cor = '#721c24'; $lbl = 'Alta/Crítica'; }
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($ct['nome']); ?></strong></td>
                        <td><?php echo (int)$ct['total_prazos']; ?></td>
                        <td>
                            <?php if($ct['prazos_vencidos'] > 0): ?>
                                <span class="badge badge-danger"><?php echo (int)$ct['prazos_vencidos']; ?></span>
                            <?php else: ?>
                                <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo (int)$ct['total_tarefas']; ?></td>
                        <td>
                            <span style="background: <?php echo $bg; ?>; color: <?php echo $cor; ?>; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                <?php echo $lbl; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Nenhum dado de equipe.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
function toggleValor(event) {
    // Impede que o clique no ícone navegue para a página de honorários (Ação do link pai)
    event.preventDefault(); 
    event.stopPropagation();
    
    const valorEl = document.getElementById('valor-honorarios');
    const eyeIcon = document.getElementById('eye-icon');
    
    if (valorEl.innerText === 'R$ *****') {
        valorEl.innerText = valorEl.getAttribute('data-valor'); // Mostra o valor real do banco
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    } else {
        valorEl.innerText = 'R$ *****'; // Oculta o valor novamente
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
