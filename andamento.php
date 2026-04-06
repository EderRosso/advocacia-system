<?php
// andamento.php - Portal Mágico do Cliente
// Essa página NÃO exige login local, apenas um token seguro!

require_once __DIR__ . '/config/conexao.php';

$token = $_GET['token'] ?? '';
$erro = '';
$processo = null;
$linha_tempo = [];

if (empty($token)) {
    $erro = "Link de acesso inválido ou expirado.";
} else {
    // Buscar Processo Atrelado + Nome do Cliente
    $stmt = $pdo->prepare("SELECT p.id, p.numero_processo, p.vara_juizo, p.comarca, c.nome as cliente_nome 
                           FROM processos p 
                           JOIN clientes c ON p.id_cliente = c.id 
                           WHERE p.token_acesso = ? LIMIT 1");
    $stmt->execute([$token]);
    $processo = $stmt->fetch();

    if (!$processo) {
        $erro = "Nenhum processo encontrado com essa credencial de rastreio.";
    } else {
        // Buscar SOMENTE a Timeline Visível para o Cliente !!!
        $stmt_tl = $pdo->prepare("SELECT descricao, data_evento FROM timeline WHERE processo_id = ? AND visibilidade = 'cliente' ORDER BY data_evento DESC");
        $stmt_tl->execute([$processo['id']]);
        $linha_tempo = $stmt_tl->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acompanhamento de Processo</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0D8ABC;
            --bg: #f4f7f6;
            --text: #333;
            --card-bg: #fff;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0; padding: 0;
            display: flex; flex-direction: column; align-items: center;
        }
        .header {
            background-color: var(--primary);
            color: white;
            width: 100%;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 { margin: 0; font-size: 20px; letter-spacing: 1px; }
        
        .container {
            max-width: 600px;
            width: 90%;
            margin: 30px auto;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .aviso { text-align: center; color: red; font-weight: bold; }
        
        .proc-info {
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .proc-info h2 { margin: 0 0 10px 0; color: var(--primary); font-size: 22px; }
        .proc-info p { margin: 5px 0; font-size: 14px; color: #555; }
        
        .timeline { border-left: 3px solid var(--primary); padding-left: 20px; position: relative; margin-top: 10px;}
        .tl-item { margin-bottom: 25px; position: relative; }
        .tl-dot {
            position: absolute;
            left: -29px; top: 0;
            width: 16px; height: 16px;
            background: var(--primary);
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px var(--primary);
        }
        .tl-date { font-size: 12px; color: #777; font-weight: 600; margin-bottom: 5px; }
        .tl-content {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            line-height: 1.5;
            color: #444;
            border-left: 4px solid var(--primary);
        }
        
        .empty-state { text-align: center; padding: 30px 0; color: #888; }
        
        @media (max-width: 600px) {
            .container { width: 95%; padding: 20px; }
            .timeline { padding-left: 15px; }
            .tl-dot { left: -24px; width: 14px; height: 14px; }
        }
    </style>
</head>
<body>

    <header class="header">
        <h1><i class="fas fa-balance-scale"></i> Advocacia Tracker</h1>
    </header>

    <div class="container">
        <?php if (!empty($erro)): ?>
            <div class="aviso">
                <i class="fas fa-exclamation-triangle fa-3x" style="color:#ffc107; margin-bottom:15px;"></i><br>
                <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php else: ?>
        
            <div class="proc-info">
                <h2>Olá, <?php echo htmlspecialchars(explode(' ', $processo['cliente_nome'])[0]); ?>.</h2>
                <p><strong><i class="fas fa-gavel"></i> Processo Nº:</strong> <?php echo htmlspecialchars($processo['numero_processo']); ?></p>
                <p><strong><i class="fas fa-building"></i> Local:</strong> <?php echo htmlspecialchars($processo['vara_juizo'] . ' - ' . $processo['comarca']); ?></p>
            </div>

            <h3 style="color:#333; margin-bottom: 20px;"><i class="fas fa-route"></i> Andamento Oficial</h3>

            <?php if (count($linha_tempo) > 0): ?>
                <div class="timeline">
                    <?php foreach($linha_tempo as $ev): ?>
                    <div class="tl-item">
                        <div class="tl-dot"></div>
                        <div class="tl-date"><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y - H:i', strtotime($ev['data_evento'])); ?></div>
                        <div class="tl-content">
                            <?php echo nl2br(htmlspecialchars($ev['descricao'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle fa-2x" style="color:#ddd; margin-bottom:10px;"></i>
                    <p>Nenhuma movimentação atualizada recentemente.</p>
                    <small>O escritório o avisará assim que houver novos andamentos neste canal!</small>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

</body>
</html>
