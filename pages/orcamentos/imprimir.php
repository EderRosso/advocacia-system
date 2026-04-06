<?php
require_once __DIR__ . '/../../config/conexao.php';

if (!isset($_GET['id'])) {
    die("Acesso inválido.");
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT o.*, c.nome, c.email, c.telefone, c.cpf_cnpj, c.endereco, c.cidade, c.estado 
                      FROM orcamentos o 
                      JOIN clientes c ON o.id_cliente = c.id 
                      WHERE o.id = ?");
$stmt->execute([$id]);
$orc = $stmt->fetch();

if (!$orc) {
    die("Orçamento não encontrado.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta Comercial - <?php echo htmlspecialchars($orc['titulo']); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 40px;
            background: #f4f6f8;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 50px;
            border: 1px solid #eee;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            font-size: 14px;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0D8ABC;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header img {
            max-height: 80px;
            max-width: 250px;
        }
        .header .no-logo {
            font-size: 24px;
            font-weight: 700;
            color: #0D8ABC;
            text-transform: uppercase;
        }
        .header-right {
            text-align: right;
            font-size: 13px;
            color: #666;
        }
        .title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #222;
        }
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            background: #fcfcfc;
            padding: 20px;
            border-left: 4px solid #0D8ABC;
        }
        .info-block strong {
            display: block;
            margin-bottom: 5px;
            color: #444;
        }
        .content {
            margin-bottom: 40px;
            font-size: 15px;
            text-align: justify;
        }
        .content-box {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 150px;
            white-space: pre-wrap;
        }
        .pricing {
            margin-top: 40px;
            border-top: 2px solid #eee;
            padding-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .pricing-box {
            background: #f1f8ff;
            padding: 20px 40px;
            text-align: right;
            border-radius: 4px;
            border: 1px solid #cce5ff;
        }
        .pricing-box span {
            display: block;
            font-size: 13px;
            color: #666;
        }
        .pricing-box h2 {
            margin: 5px 0 0 0;
            color: #0D8ABC;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .signature {
            margin-top: 80px;
            display: flex;
            justify-content: space-around;
        }
        .signature-line {
            width: 250px;
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 10px;
            font-weight: 600;
        }
        
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-box { box-shadow: none; border: none; padding: 0; }
            .print-btn { display: none !important; }
        }
        
        .print-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 12px;
            text-align: center;
            background: #0D8ABC;
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .print-btn:hover {
            background: #09688d;
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn">🖨️ IMPRIMIR / SALVAR PDF</button>

    <div class="invoice-box">
        <div class="header">
            <div>
                <?php if(!empty($orc['logo_advogado']) && file_exists(__DIR__ . '/../../' . $orc['logo_advogado'])): ?>
                    <img src="../../<?php echo htmlspecialchars($orc['logo_advogado']); ?>" alt="Logo Advogado">
                <?php else: ?>
                    <div class="no-logo">ADVOCACIA & CONSULTORIA</div>
                <?php endif; ?>
            </div>
            <div class="header-right">
                <strong>Data da Emissão:</strong> <?php echo date('d/m/Y', strtotime($orc['data_criacao'])); ?><br>
                <strong>Proposta Nº:</strong> <?php echo str_pad($orc['id'], 5, '0', STR_PAD_LEFT); ?><br>
                <strong>Validade:</strong> <?php echo $orc['validade_dias']; ?> dias
            </div>
        </div>

        <div class="title">
            <?php echo htmlspecialchars($orc['titulo']); ?>
        </div>

        <div class="info-grid">
            <div class="info-block">
                <strong>Apresentado à:</strong>
                <?php echo htmlspecialchars($orc['nome']); ?><br>
                CNPJ/CPF: <?php echo htmlspecialchars($orc['cpf_cnpj']); ?><br>
                E-mail: <?php echo htmlspecialchars($orc['email']); ?><br>
                Telefone: <?php echo htmlspecialchars($orc['telefone']); ?>
            </div>
            <div class="info-block" style="text-align: right;">
                <strong>Endereço do Cliente:</strong>
                <?php echo htmlspecialchars($orc['endereco']); ?><br>
                <?php echo htmlspecialchars($orc['cidade'] . ' - ' . $orc['estado']); ?>
            </div>
        </div>

        <div class="content">
            <h3 style="color: #0D8ABC; margin-bottom: 10px;">Escopo dos Serviços</h3>
            <div class="content-box">
<?php echo htmlspecialchars($orc['descricao_servicos']); ?>
            </div>
        </div>

        <div class="pricing">
            <div class="pricing-box">
                <span>Investimento Total (Honorários)</span>
                <h2>R$ <?php echo number_format($orc['valor'], 2, ',', '.'); ?></h2>
            </div>
        </div>

        <div class="signature">
            <div class="signature-line">
                De Cordo (Assinatura do Cliente)
            </div>
            <div class="signature-line">
                Advogado / Escritório
            </div>
        </div>

        <div class="footer">
            Este documento é estritamente confidencial e seu conteúdo tem validade de <?php echo $orc['validade_dias']; ?> dias a partir de sua emissão.<br>
            A prestação do serviço condiciona-se à assinatura de Contrato de Honorários formal.
        </div>
    </div>

</body>
</html>
