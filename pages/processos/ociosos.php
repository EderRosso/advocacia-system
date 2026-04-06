<?php
$page_title = 'Monitor de Processos Ociosos';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('processos');

try {
    // Busca os processos "ativos", calcula a última data do timeline ou distribuição, e calcula os dias
    $sql = "SELECT p.id, p.numero_processo, c.nome as cliente_nome, u.nome as advogado_nome, 
                   COALESCE(MAX(t.data_evento), p.data_distribuicao) as ultima_data,
                   DATEDIFF(CURDATE(), COALESCE(MAX(t.data_evento), p.data_distribuicao, CURDATE())) as dias_inativo 
            FROM processos p
            JOIN clientes c ON p.id_cliente = c.id
            LEFT JOIN usuarios u ON p.id_advogado = u.id
            LEFT JOIN timeline t ON p.id = t.processo_id
            WHERE p.status = 'ativo'
            GROUP BY p.id
            ORDER BY dias_inativo DESC";
    
    $stmt = $pdo->query($sql);
    $processos = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>

<div class="page-header action-buttons" style="justify-content: flex-start; gap: 15px;">
    <a href="index.php" class="btn btn-sm" style="background-color:#7f8c8d; color:white;"><i class="fas fa-arrow-left"></i> Voltar</a>
</div>

<div class="panel">
    <div class="panel-header" style="background: #f8f9fa;">
        <div>
            <h3 style="margin-bottom: 5px;"><i class="fas fa-exclamation-triangle text-warning"></i> Alerta de Estagnação Processual</h3>
            <p style="font-size: 13px; color: #666; font-weight: normal;">Esta lista cruza a <strong>Linha do Tempo</strong> com os processos ativos, alertando o advogado sobre prazos de inatividade.</p>
        </div>
    </div>
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Processo</th>
                    <th>Cliente / Advogado</th>
                    <th>Última Movimentação</th>
                    <th>Classificação (Inatividade)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($processos)): ?>
                    <?php foreach($processos as $proc): 
                        // Regra de cores
                        $dias = (int)$proc['dias_inativo'];
                        $cor_bg = ''; $cor_txt = ''; $icon = '';
                        if ($dias > 60) {
                            $cor_bg = '#f8d7da'; $cor_txt = '#721c24'; $icon = '<i class="fas fa-skull-crossbones"></i> Crítico';
                        } elseif ($dias >= 30) {
                            $cor_bg = '#fff3cd'; $cor_txt = '#856404'; $icon = '<i class="fas fa-exclamation-circle"></i> Atenção';
                        } else {
                            $cor_bg = '#d4edda'; $cor_txt = '#155724'; $icon = '<i class="fas fa-check-circle"></i> Normal';
                        }
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($proc['numero_processo']); ?></strong></td>
                        <td>
                            <div style="font-size: 13px; line-height: 1.4;">
                                <i class="fas fa-user-tie text-blue"></i> <?php echo htmlspecialchars($proc['cliente_nome']); ?><br>
                                <span style="font-size:11px; color:#777;"><i class="fas fa-briefcase"></i> Resposável: <?php echo htmlspecialchars($proc['advogado_nome'] ?? 'Não Definido'); ?></span>
                            </div>
                        </td>
                        <td><?php echo $proc['ultima_data'] ? date('d/m/Y', strtotime($proc['ultima_data'])) : 'Recém Cadastrado'; ?></td>
                        <td>
                            <div style="background: <?php echo $cor_bg; ?>; color: <?php echo $cor_txt; ?>; padding: 5px 10px; border-radius: 4px; display:inline-block; font-size:13px; font-weight:600;">
                                <?php echo $icon; ?> - <?php echo $dias; ?> dias parado
                            </div>
                        </td>
                        <td class="btn-actions">
                            <a href="view.php?id=<?php echo $proc['id']; ?>&tab=lexflow" class="btn btn-sm btn-green" title="Movimentar Processo"><i class="fas fa-arrow-right"></i> Atualizar Agora</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Nenhum processo ativo encontrado na base de dados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
