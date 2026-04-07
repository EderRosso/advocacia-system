<?php
$page_title = 'Orçamentos / Propostas';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../includes/mailer.php';

// Enviar Email
if(isset($_GET['enviar_email']) && isset($_GET['id'])) {
    if (!tem_permissao('honorarios')) { echo "Sem permissão."; exit; }
    $id_envio = (int)$_GET['id'];
    
    // Obter dados do orçamento e cliente
    $stmt_env = $pdo->prepare("SELECT o.titulo, c.nome, c.email FROM orcamentos o JOIN clientes c ON o.id_cliente = c.id WHERE o.id = ?");
    $stmt_env->execute([$id_envio]);
    $dados_env = $stmt_env->fetch();
    
    if($dados_env && !empty($dados_env['email'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $link_orc = $protocol . $_SERVER['HTTP_HOST'] . BASE_URL . "pages/orcamentos/imprimir.php?id=" . $id_envio;
        
        $res = enviar_email_orcamento($dados_env['email'], $dados_env['nome'], $dados_env['titulo'], $link_orc);
        if($res === true) {
            $sucesso = "Orçamento enviado por e-mail com sucesso para " . $dados_env['email'] . "!";
        } else {
            $erro_msg = "Erro ao enviar e-mail: " . $res;
        }
    } else {
        $erro_msg = "Cliente não possui e-mail cadastrado ou orçamento não encontrado.";
    }
}

// Deletar Orçamento
if (isset($_GET['delete'])) {
    if (!tem_permissao('honorarios')) { echo "Sem permissão."; exit; }
    $id_del = (int)$_GET['delete'];
    
    // Deletar logos caso existam
    $stmt_log = $pdo->prepare("SELECT logo_1, logo_2 FROM orcamentos WHERE id=?");
    $stmt_log->execute([$id_del]);
    $orc_log = $stmt_log->fetch();
    
    if($orc_log) {
        if(!empty($orc_log['logo_1']) && file_exists(__DIR__ . '/../../' . $orc_log['logo_1'])) {
            @unlink(__DIR__ . '/../../' . $orc_log['logo_1']);
        }
        if(!empty($orc_log['logo_2']) && file_exists(__DIR__ . '/../../' . $orc_log['logo_2'])) {
            @unlink(__DIR__ . '/../../' . $orc_log['logo_2']);
        }
    }

    $stmt_del = $pdo->prepare("DELETE FROM orcamentos WHERE id = ?");
    $stmt_del->execute([$id_del]);
    $sucesso = "Orçamento excluído com sucesso!";
}

// Mudar Status
if (isset($_GET['status']) && isset($_GET['id'])) {
    if (!tem_permissao('honorarios')) { echo "Sem permissão."; exit; }
    $id_st = (int)$_GET['id'];
    $novo_st = $_GET['status'];
    $data_aprov = ($novo_st === 'aprovado') ? date('Y-m-d H:i:s') : null;
    $stmt_st = $pdo->prepare("UPDATE orcamentos SET status = ?, data_aprovacao = ? WHERE id = ?");
    $stmt_st->execute([$novo_st, $data_aprov, $id_st]);
    $sucesso = "Status atualizado!";
}

// Busca principal
$search = $_GET['q'] ?? '';
$where = "1=1";
$params = [];
if ($search) {
    $where .= " AND (o.titulo LIKE ? OR c.nome LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}

$sql = "SELECT o.*, c.nome as cliente_nome, c.email as cliente_email, c.telefone as cliente_telefone
        FROM orcamentos o
        JOIN clientes c ON o.id_cliente = c.id
        WHERE $where 
        ORDER BY o.data_criacao DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orcamentos = $stmt->fetchAll();
?>

<div class="page-header">
    <form action="" method="GET" class="search-bar">
        <input type="text" name="q" placeholder="Buscar proposta..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-blue"><i class="fas fa-search"></i> Buscar</button>
    </form>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Orçamento</a>
</div>

<div class="panel">
    <div class="panel-body table-responsive">
        <?php if(isset($sucesso)): ?><div class="alert alert-success"><?php echo $sucesso; ?></div><?php endif; ?>
        <?php if(isset($erro_msg)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($erro_msg); ?></div><?php endif; ?>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Título da Proposta</th>
                    <th>Cliente</th>
                    <th>Valor</th>
                    <th>Validade</th>
                    <th>Status</th>
                    <th style="width: 180px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($orcamentos) > 0): ?>
                    <?php foreach($orcamentos as $o): 
                        $badge_class = 'badge-warning';
                        if($o['status'] == 'aprovado') $badge_class = 'badge-success';
                        if($o['status'] == 'rejeitado') $badge_class = 'badge-danger';
                        
                        $validade_dt = date('Y-m-d', strtotime($o['data_criacao'] . " +{$o['validade_dias']} days"));
                        $vencido = (strtotime($validade_dt) < strtotime(date('Y-m-d')) && $o['status'] == 'pendente');
                    ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($o['data_criacao'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($o['titulo']); ?></strong></td>
                        <td><?php echo htmlspecialchars($o['cliente_nome']); ?></td>
                        <td style="color:#28a745; font-weight:600;">R$ <?php echo number_format($o['valor'], 2, ',', '.'); ?></td>
                        <td>
                            <?php if($o['status'] == 'pendente'): ?>
                                <?php if($vencido): ?>
                                    <span class="text-danger" title="Proposta Vencida"><i class="fas fa-exclamation-circle"></i> Vencido</span>
                                <?php else: ?>
                                    <?php echo date('d/m/Y', strtotime($validade_dt)); ?>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                        <td>
                            <?php 
                                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                                $link_imprimir = $protocol . $_SERVER['HTTP_HOST'] . BASE_URL . "pages/orcamentos/imprimir.php?id=" . $o['id']; 
                                $whatsapp_phone = preg_replace('/[^0-9]/', '', $o['cliente_telefone']);
                                $whatsapp_text = urlencode("Olá *{$o['cliente_nome']}*, enviamos a proposta comercial *{$o['titulo']}*. Para visualizar os detalhes e baixar o PDF, acesse: {$link_imprimir}");
                                $whatsapp_link = "https://api.whatsapp.com/send?phone=55{$whatsapp_phone}&text={$whatsapp_text}";
                            ?>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-start; align-items: center;">
                                
                                <!-- Bloco Envio e PDF -->
                                <div style="display: flex; gap: 2px; background: #f0f2f5; padding: 3px; border-radius: 6px; border: 1px solid #dce0e4;">
                                    <span style="font-size: 10px; color: #6c757d; font-weight: 600; padding: 0 4px; display: flex; align-items: center; border-right: 1px solid #dce0e4; margin-right: 2px;">ENVIAR</span>
                                    <a href="<?php echo htmlspecialchars($whatsapp_link); ?>" target="_blank" class="btn btn-sm" style="background:#25D366; color:white; padding: 5px 8px;" title="Enviar por WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                    <a href="index.php?enviar_email=1&id=<?php echo $o['id']; ?>" class="btn btn-sm" style="background:#f39c12; color:white; padding: 5px 8px;" title="Enviar por E-mail" onclick="return confirmDialog(event, this.href, 'Deseja enviar a proposta por e-mail para <?php echo htmlspecialchars($o['cliente_email'] ?? 'cliente sem e-mail'); ?>?');"><i class="fas fa-envelope"></i></a>
                                    <a href="imprimir.php?id=<?php echo $o['id']; ?>" target="_blank" class="btn btn-sm" style="background:#6c757d; color:white; padding: 5px 8px;" title="Imprimir / PDF"><i class="fas fa-print"></i></a>
                                </div>

                                <!-- Bloco de Controle de Status -->
                                <?php if($o['status'] == 'pendente'): ?>
                                <div style="display: flex; gap: 2px; background: #fffcf2; padding: 3px; border-radius: 6px; border: 1px solid #fcebb6;">
                                    <a href="index.php?id=<?php echo $o['id']; ?>&status=aprovado" class="btn btn-sm" style="background:#28a745; color:white; padding: 5px 8px;" title="Aprovar Proposta"><i class="fas fa-check"></i></a>
                                    <a href="index.php?id=<?php echo $o['id']; ?>&status=rejeitado" class="btn btn-sm" style="background:#dc3545; color:white; padding: 5px 8px;" title="Rejeitar Proposta"><i class="fas fa-times"></i></a>
                                </div>
                                <?php endif; ?>

                                <!-- Bloco Gestão (Editar/Excluir) -->
                                <div style="display: flex; gap: 2px; background: #f0f2f5; padding: 3px; border-radius: 6px; border: 1px solid #dce0e4;">
                                    <a href="form.php?id=<?php echo $o['id']; ?>" class="btn btn-sm" style="background:#1F6E8C; color:white; padding: 5px 8px;" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="index.php?delete=<?php echo $o['id']; ?>" onclick="return confirmDialog(event, this.href, 'Excluir este orçamento permanentemente?');" class="btn btn-sm" style="background:#cc0000; color:white; padding: 5px 8px;" title="Excluir"><i class="fas fa-trash"></i></a>
                                </div>
                                
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">Nenhum orçamento encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
