<?php
require_once __DIR__ . '/../../config/conexao.php';

if (!isset($_GET['id'])) { die("Acesso inválido."); }

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT o.*, c.nome, c.email, c.telefone, c.cpf, c.endereco, c.cidade, c.estado 
                      FROM orcamentos o 
                      JOIN clientes c ON o.id_cliente = c.id 
                      WHERE o.id = ?");
$stmt->execute([$id]);
$orc = $stmt->fetch();
if (!$orc) { die("Orçamento não encontrado."); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta Comercial - <?php echo htmlspecialchars($orc['titulo']); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body { font-family: 'Inter', sans-serif; color: #111; line-height: 1.6; margin: 0; padding: 40px; background: #eaebed; }
        
        .page-border {
            max-width: 800px;
            margin: auto;
            border: 6px solid #000;
            padding: 40px 50px;
            background: #fff;
            min-height: 1050px;
            box-sizing: border-box;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }
        .header img {
            max-height: 120px;
            max-width: 200px;
            object-fit: contain;
        }
        .no-logo-text { font-size: 14px; font-weight: bold; color: #555; text-transform: uppercase; width: 200px; text-align: center;}

        .client-info {
            font-size: 15px; margin-bottom: 30px;
        }
        .client-info span { font-weight: 700; color: #000; display: block; margin-bottom: 10px; }

        .service-content {
            font-size: 14px;
            color: #222;
            margin-bottom: 50px;
        }
        
        /* Ajustes baseados na injeção do HTML do CKEditor para visual PDF */
        .service-content ul, .service-content ol { padding-left: 20px; }
        .service-content p { margin-bottom: 10px; }

        .bottom-info { font-size: 14px; margin-top: 50px; margin-bottom: 80px; font-weight: 400; }
        .validade { font-weight: 700; margin-top: 25px; text-transform: uppercase; }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }
        .sign-block { text-align: center; font-size: 14px; font-weight: 700; width: 45%; }
        .sign-line { border-bottom: 1px solid #000; margin-bottom: 10px; }
        
        @media print {
            body { background: transparent; padding: 0; }
            .page-border { box-shadow: none; min-height: 100vh; border: 6px solid #000; }
            .print-btn { display: none !important; }
        }
        
        .print-btn {
            display: block; width: 220px; margin: 0 auto 30px auto; padding: 12px; text-align: center;
            background: #28a745; color: #fff; border-radius: 4px; font-weight: bold; cursor: pointer; border: none; font-size: 16px;
        }
        .print-btn:hover { background: #218838; }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn"><i class="fas fa-print"></i> IMPRIMIR / SALVAR PDF</button>

    <div class="page-border">
        
        <!-- Header Logos -->
        <?php
        $tem_logo1 = !empty($orc['logo_1']) && file_exists(__DIR__ . '/../../' . $orc['logo_1']);
        $tem_logo2 = !empty($orc['logo_2']) && file_exists(__DIR__ . '/../../' . $orc['logo_2']);
        $justificativa = ($tem_logo1 && $tem_logo2) ? 'space-between' : 'center';
        ?>
        <div class="header" style="justify-content: <?php echo $justificativa; ?>;">
            <?php if($tem_logo1): ?>
                <div><img src="../../<?php echo htmlspecialchars($orc['logo_1']); ?>" alt="Logo 1"></div>
            <?php endif; ?>
            
            <?php if($tem_logo2): ?>
                <div><img src="../../<?php echo htmlspecialchars($orc['logo_2']); ?>" alt="Logo 2"></div>
            <?php endif; ?>
            
            <?php if(!$tem_logo1 && !$tem_logo2): ?>
                <div><div class="no-logo-text">[Espaço para Logo]</div></div>
            <?php endif; ?>
        </div>

        <!-- Dados Iniciais -->
        <div class="client-info">
            <span>Cliente: <?php echo htmlspecialchars($orc['nome']); ?></span>
            <span>Atendimento: <?php echo date('d/m/Y', strtotime($orc['data_criacao'])); ?></span>
        </div>

        <!-- Descrição Central Livre (Editor HTML) -->
        <div class="service-content"><?php echo $orc['descricao_servicos']; ?></div>

        <!-- Rodapé Local/Validade -->
        <div class="bottom-info">
            Sapucaia do Sul, <?php echo date('d', strtotime($orc['data_criacao'])); ?> de <?php 
                $meses = ['01'=>'janeiro','02'=>'fevereiro','03'=>'março','04'=>'abril','05'=>'maio','06'=>'junho','07'=>'julho','08'=>'agosto','09'=>'setembro','10'=>'outubro','11'=>'novembro','12'=>'dezembro'];
                echo $meses[date('m', strtotime($orc['data_criacao']))]; 
            ?> de <?php echo date('Y', strtotime($orc['data_criacao'])); ?>.
            
            <div class="validade">
                VALIDADE DA PROPOSTA <?php echo (int)$orc['validade_dias']; ?> DIAS CORRIDOS.
            </div>
        </div>

        <!-- Linhas de Assinatura -->
        <?php
        $tem_ass1 = !empty(trim($orc['assinatura_1_nome']));
        $tem_ass2 = !empty(trim($orc['assinatura_2_nome']));
        $just_ass = ($tem_ass1 && $tem_ass2) ? 'space-between' : 'center';
        ?>
        <div class="signatures" style="justify-content: <?php echo $just_ass; ?>;">
            <?php if($tem_ass1): ?>
            <div class="sign-block" <?php if(!$tem_ass2 && $tem_ass1) echo 'style="width: 60%;"'; ?>>
                <div class="sign-line"></div>
                <?php echo htmlspecialchars($orc['assinatura_1_nome']); ?><br>
                <?php echo htmlspecialchars($orc['assinatura_1_oab']); ?>
            </div>
            <?php endif; ?>
            
            <?php if($tem_ass2): ?>
            <div class="sign-block" <?php if(!$tem_ass1 && $tem_ass2) echo 'style="width: 60%;"'; ?>>
                <div class="sign-line"></div>
                <?php echo htmlspecialchars($orc['assinatura_2_nome']); ?><br>
                <?php echo htmlspecialchars($orc['assinatura_2_oab']); ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
