<?php
/**
 * PROCESSOS VIEW (LexFlow Inteligente)
 * Tela detalhada do processo com integração do Assistente Jurídico.
 */
$page_title = 'Visualização do Processo - LexFlow';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../modules/assistente/assistente.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<div class='alert alert-danger'>ID do Processo não informado.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Buscar o Processo
$stmt = $pdo->prepare("SELECT p.*, c.nome as cliente_nome, u.nome as advogado_nome 
                       FROM processos p 
                       JOIN clientes c ON p.id_cliente = c.id 
                       LEFT JOIN usuarios u ON p.id_advogado = u.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$processo = $stmt->fetch();

if (!$processo) {
    echo "<div class='alert alert-danger'>Processo não encontrado.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Lógica de inclusão LexFlow (Só na visualização)
lexflow_gerar_checklist_se_vazio($pdo, $id, $processo['tipo_acao']);

// Lógica Customizada: Adicionar Etapa Manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_lexflow']) && $_POST['acao_lexflow'] == 'add_checklist') {
    $nova_etapa = trim($_POST['nova_etapa']);
    $novo_item = trim($_POST['novo_item']);
    if(!empty($nova_etapa) && !empty($novo_item)) {
        $pdo->prepare("INSERT INTO checklists (processo_id, item, etapa) VALUES (?, ?, ?)")->execute([$id, $novo_item, $nova_etapa]);
        lexflow_registrar_timeline($pdo, $id, "Nova etapa/documento registrado(a): $nova_etapa -> $novo_item.");
    }
    echo "<script>window.location.href = 'view.php?id=$id&tab=lexflow';</script>";
    exit;
}

// Lógica para marcar checklist como feito
if (isset($_GET['check_id'])) {
    $cid = $_GET['check_id'];
    $st = $_GET['status'] == 'pendente' ? 'concluida' : 'pendente';
    $pdo->prepare("UPDATE checklists SET status = ? WHERE id = ? AND processo_id = ?")->execute([$st, $cid, $id]);
    
    // Auto-registrar no Timeline
    $itemInfo = $pdo->query("SELECT item FROM checklists WHERE id = $cid")->fetchColumn();
    $acao = $st == 'concluida' ? 'marcado como concluído' : 'desmarcado';
    lexflow_registrar_timeline($pdo, $id, "Checklist: Item '$itemInfo' foi $acao.");
    
    echo "<script>window.location.href = 'view.php?id=$id&tab=lexflow';</script>";
    exit;
}

// Analisador do texto das observações e campo parte_contraria
$analise_texto = $processo['observacoes'] . " " . $processo['parte_contraria'];
$sugestoes_obs = lexflow_analisar_observacoes($analise_texto);

// Coletar sugestoes de banco
$chave_tipo = lexflow_normalizar_tipo($processo['tipo_acao']);
$stmt_sug = $pdo->prepare("SELECT sugestao FROM sugestoes_juridicas WHERE tipo_processo = ? OR tipo_processo = 'geral'");
$stmt_sug->execute([$chave_tipo]);
$sugestoes_banco = $stmt_sug->fetchAll(PDO::FETCH_COLUMN);

// Coletar Prazos (Alertas)
$stmt_prazos = $pdo->prepare("SELECT descricao_prazo as desc_prazo, data_limite, status, DATEDIFF(data_limite, CURDATE()) as dias_restantes FROM prazos WHERE id_processo = ? ORDER BY data_limite ASC");
$stmt_prazos->execute([$id]);
$prazos = $stmt_prazos->fetchAll();

// Coletar Checklist
$stmt_chk = $pdo->prepare("SELECT * FROM checklists WHERE processo_id = ? ORDER BY id ASC");
$stmt_chk->execute([$id]);
$checklists = $stmt_chk->fetchAll();

// Coletar Timeline
$stmt_tl = $pdo->prepare("SELECT * FROM timeline WHERE processo_id = ? ORDER BY data_evento DESC");
$stmt_tl->execute([$id]);
$timeline = $stmt_tl->fetchAll();
?>

<div class="panel">
    <div class="panel-header" style="flex-wrap: wrap; gap: 10px;">
        <h3 style="margin-bottom: 0;"><i class="fas fa-gavel"></i> Processo Nº <?php echo htmlspecialchars($processo['numero_processo']); ?></h3>
        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
            <a href="form.php?id=<?php echo $id; ?>" class="btn btn-sm btn-blue"><i class="fas fa-edit"></i> Editar Ficha</a>
            <a href="index.php" class="btn btn-sm btn-blue">Voltar</a>
        </div>
    </div>
    
    <!-- Tab Navigator -->
    <div style="border-bottom: 2px solid var(--border-color); display: flex; gap: 20px; padding: 0 20px;">
        <a href="#dados" style="padding: 15px 5px; font-weight: 600; color: var(--primary-color); border-bottom: 3px solid transparent;" onclick="showTab('dados', this)" id="tab-dados">Dados Principais</a>
        <a href="#lexflow" style="padding: 15px 5px; font-weight: 600; color: var(--success); border-bottom: 3px solid transparent;" onclick="showTab('lexflow', this)" id="tab-lexflow"><i class="fas fa-robot"></i> Assistente Jurídico</a>
    </div>

    <!-- TAB: Dados -->
    <div id="content-dados" class="panel-body" style="display: none;">
        <div class="form-grid">
            <div class="form-group"><strong>Cliente:</strong><br> <?php echo htmlspecialchars($processo['cliente_nome'] ?? ''); ?></div>
            <div class="form-group"><strong>Tipo de Ação:</strong><br> <?php echo htmlspecialchars($processo['tipo_acao'] ?? ''); ?></div>
            <div class="form-group"><strong>Vara/Juízo:</strong><br> <?php echo htmlspecialchars($processo['vara_juizo'] ?? ''); ?></div>
            <div class="form-group"><strong>Comarca:</strong><br> <?php echo htmlspecialchars($processo['comarca'] ?? ''); ?></div>
            <div class="form-group"><strong>Advogado Resp:</strong><br> <?php echo htmlspecialchars($processo['advogado_nome'] ?? ''); ?></div>
            <div class="form-group"><strong>Status Local:</strong><br> <?php echo htmlspecialchars($processo['status'] ?? ''); ?></div>
            <div class="form-group full-width"><strong>Observações:</strong><br> <p style="background: #f9f9f9; padding: 10px; border-radius: 4px;"><?php echo nl2br(htmlspecialchars($processo['observacoes'] ?? '')); ?></p></div>
        </div>
    </div>

    <!-- TAB: Assistente (LexFlow) -->
    <div id="content-lexflow" class="panel-body" style="display: none; background: #fafbfc;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            
            <!-- Coluna Esquerda: Alertas, Checklist e Sugestões -->
            <div>
                <!-- ALERTAS INTELIGENTES -->
                <div class="card" style="margin-bottom:20px; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background:#fff; border-left: 4px solid var(--primary-color);">
                    <h4 style="margin-bottom: 15px; color: var(--primary-color);"><i class="fas fa-bell"></i> Alertas Inteligentes</h4>
                    <?php if (count($prazos) > 0): ?>
                        <ul style="list-style: none; padding: 0;">
                        <?php foreach($prazos as $pr): 
                            if($pr['status'] == 'cumprido') continue;
                            
                            $dias = (int)$pr['dias_restantes'];
                            $bg = '#d4edda'; $cor = '#155724'; $icon = 'check-circle'; // Seguro
                            
                            if ($dias < 0) { $bg = '#f8d7da'; $cor = '#721c24'; $icon = 'skull-crossbones'; } // Vencido
                            elseif ($dias <= 1) { $bg = '#f8d7da'; $cor = '#721c24'; $icon = 'exclamation-circle'; } // Crítico
                            elseif ($dias <= 3) { $bg = '#fff3cd'; $cor = '#856404'; $icon = 'exclamation-triangle'; } // Atenção
                        ?>
                            <li style="background: <?php echo $bg; ?>; color: <?php echo $cor; ?>; padding: 10px; border-radius: 4px; margin-bottom: 8px; font-size: 13px; font-weight: 500;">
                                <i class="fas fa-<?php echo $icon; ?>"></i> 
                                <?php echo htmlspecialchars($pr['desc_prazo']); ?> - Vence dia <?php echo date('d/m', strtotime($pr['data_limite'])); ?> 
                                (<?php echo $dias >= 0 ? "Faltam $dias dias" : "VENCIDO!"; ?>)
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted"><i class="fas fa-check"></i> Sem prazos pendentes no momento.</p>
                    <?php endif; ?>
                </div>

                <!-- CHECKLIST AUTOMÁTICO E CUSTOMIZADO -->
                <div class="card" style="margin-bottom:20px; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background:#fff; border-left: 4px solid var(--success);">
                    <h4 style="margin-bottom: 15px; color: var(--primary-color);"><i class="fas fa-tasks"></i> Organização Processual por Etapas</h4>
                    
                    <?php
                    // Agrupar os checklists pela coluna Fase/Etapa
                    $checklists_por_etapa = [];
                    foreach($checklists as $c) {
                        $etp = !empty($c['etapa']) ? $c['etapa'] : 'Fase Inicial (Sugestões LexFlow)';
                        $checklists_por_etapa[$etp][] = $c;
                    }
                    ?>

                    <?php foreach($checklists_por_etapa as $nome_etapa => $lista_chk): ?>
                    <div style="margin-bottom: 15px;">
                        <h5 style="background: #eef2f5; padding: 6px 12px; border-radius: 4px; color: #444; font-size: 13px; font-weight: bold; margin-bottom: 5px;"><i class="fas fa-layer-group text-primary"></i> <?php echo htmlspecialchars($nome_etapa); ?></h5>
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach($lista_chk as $chk): ?>
                            <li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                <span style="<?php echo $chk['status'] == 'concluida' ? 'text-decoration: line-through; color: #aaa;' : 'font-weight: 500; font-size:14px;'; ?>">
                                    <?php echo htmlspecialchars($chk['item']); ?>
                                </span>
                                <div style="display: flex; gap: 5px;">
                                    <a href="<?php echo BASE_URL; ?>pages/documentos/form.php?id_processo=<?php echo $id; ?>&id_cliente=<?php echo urlencode($processo['id_cliente'] ?? ''); ?>&titulo=<?php echo urlencode($chk['item']); ?>" class="btn btn-sm btn-blue" title="Anexar e Gerar Documento" target="_blank">
                                        <i class="fas fa-upload"></i> Anexar
                                    </a>
                                    <a href="view.php?id=<?php echo $id; ?>&check_id=<?php echo $chk['id']; ?>&status=<?php echo $chk['status']; ?>" class="btn btn-sm <?php echo $chk['status'] == 'concluida' ? 'btn-red' : 'btn-green'; ?>">
                                        <?php echo $chk['status'] == 'concluida' ? '<i class="fas fa-times"></i> Reverter' : '<i class="fas fa-check"></i> Marcar'; ?>
                                    </a>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>

                    <!-- Formulário para Adicionar Novas Etapas -->
                    <div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 15px; background: #fcfcfc; padding: 10px; border-radius: 5px;">
                        <h5 style="margin-bottom: 10px; font-size: 13px; color: var(--primary-color);"><i class="fas fa-plus-circle"></i> Planejar Nova Etapa / Documento</h5>
                        <form action="view.php?id=<?php echo $id; ?>&tab=lexflow" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap; align-items:flex-end;">
                            <input type="hidden" name="acao_lexflow" value="add_checklist">
                            <div style="flex: 1; min-width: 120px;">
                                <label style="font-size: 11px; color: #888; font-weight:600;">Etapa do Processo</label>
                                <input type="text" name="nova_etapa" class="form-control" style="font-size: 13px; padding: 8px;" placeholder="Ex: Fase Recursal, Provas..." required>
                            </div>
                            <div style="flex: 2; min-width: 180px;">
                                <label style="font-size: 11px; color: #888; font-weight:600;">Qual o Documento/Tarefa?</label>
                                <input type="text" name="novo_item" class="form-control" style="font-size: 13px; padding: 8px;" placeholder="Ex: Apresentar Agravo, Testemunha..." required>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary" style="padding: 8px 15px;" title="Adicionar à Trilha"><i class="fas fa-plus"></i> Incluir</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- SUGESTÕES E RECOMENDAÇÕES -->
                <div class="card" style="margin-bottom:20px; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background:#fff; border-left: 4px solid var(--gold-color);">
                    <h4 style="margin-bottom: 15px; color: var(--primary-color);"><i class="fas fa-lightbulb"></i> LexFlow Analytics (Sugestões)</h4>
                    
                    <?php if (count($sugestoes_obs) > 0): ?>
                        <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 13px; color: #856404; font-weight: 500;">
                            <strong>IA Detectou (Análise de Texto):</strong><br>
                            <?php foreach($sugestoes_obs as $so): ?>
                                <i class="fas fa-arrow-right"></i> <?php echo htmlspecialchars($so); ?><br>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <p style="font-size:12px; color:#666; margin-bottom:10px;">Movimentações Padrão Sugeridas (Banco de Regras):</p>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach($sugestoes_banco as $sb): ?>
                        <li style="padding: 6px 0; font-size: 13px; color: #444;">
                            <i class="far fa-dot-circle text-gold"></i> <?php echo htmlspecialchars($sb); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Coluna Direita: Timeline -->
            <div>
                <div class="card" style="padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background:#fff; height: 100%; border-left: 4px solid var(--info);">
                    <h4 style="margin-bottom: 15px; color: var(--primary-color);"><i class="fas fa-stream"></i> Timeline Automática</h4>
                    <div style="position: relative; padding-left: 20px; border-left: 2px solid #e0e0e0;">
                    <?php if (count($timeline) > 0): ?>
                        <?php foreach($timeline as $tm): 
                            $is_cliente = (isset($tm['visibilidade']) && $tm['visibilidade'] == 'cliente');
                            $cor_bola = $is_cliente ? '#28a745' : 'var(--info)';
                        ?>
                        <div style="margin-bottom: 15px; position: relative;">
                            <div style="position: absolute; left: -27px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: <?php echo $cor_bola; ?>; border: 2px solid #fff;"></div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 3px; display: flex; gap: 10px;">
                                <span><i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($tm['data_evento'])); ?></span>
                                <?php if($is_cliente): ?>
                                    <span style="color: #28a745; font-weight: bold;"><i class="fas fa-envelope"></i> Enviado ao Cliente</span>
                                <?php else: ?>
                                    <span style="color: #888;"><i class="fas fa-lock"></i> Interno</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 14px; color: #333; line-height: 1.4; <?php echo $is_cliente ? 'font-weight: 500;' : ''; ?>">
                                <?php echo htmlspecialchars($tm['descricao']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted" style="margin-left: 10px;">Nenhum evento registrado no histórico.</p>
                    <?php endif; ?>
                    </div>
                </div>
            </div>

        </div> <!-- Fim Grid -->
    </div> <!-- Fim Assistente -->
</div>

<script>
function showTab(tabName, el) {
    document.getElementById('content-dados').style.display = 'none';
    document.getElementById('content-lexflow').style.display = 'none';
    
    document.getElementById('tab-dados').style.borderBottomColor = 'transparent';
    document.getElementById('tab-lexflow').style.borderBottomColor = 'transparent';
    
    document.getElementById('content-' + tabName).style.display = 'block';
    el.style.borderBottomColor = tabName === 'lexflow' ? 'var(--success)' : 'var(--primary-color)';
}

// Inicializar na tab certa
const targetTab = new URLSearchParams(window.location.search).get('tab');
if(targetTab === 'lexflow') {
    showTab('lexflow', document.getElementById('tab-lexflow'));
} else {
    showTab('dados', document.getElementById('tab-dados'));
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
