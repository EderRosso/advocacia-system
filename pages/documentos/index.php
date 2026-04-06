<?php
$page_title = 'Gerenciar Documentos';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
verifica_permissao('documentos');

$busca = $_GET['busca'] ?? '';

try {
    $sql = "SELECT d.*, c.nome as cliente_nome, p.numero_processo 
            FROM documentos d 
            LEFT JOIN clientes c ON d.id_cliente = c.id 
            LEFT JOIN processos p ON d.id_processo = p.id ";
    if (!empty($busca)) $sql .= "WHERE d.titulo LIKE :busca OR c.nome LIKE :busca ";
    $sql .= "ORDER BY d.data_cadastro DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    if (!empty($busca)) {
        $termo = "%$busca%"; $stmt->bindParam(':busca', $termo);
    }
    $stmt->execute();
    $documentos = $stmt->fetchAll();
} catch(PDOException $e) { echo "Erro: " . $e->getMessage(); }
?>
<div class="page-header">
    <form action="" method="GET" class="search-bar">
        <input type="text" name="busca" placeholder="Título, cliente..." value="<?php echo htmlspecialchars($busca); ?>">
        <button type="submit" class="btn btn-blue"><i class="fas fa-search"></i> Buscar</button>
    </form>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Documento</a>
</div>

<div class="panel">
    <div class="panel-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th>Processo</th>
                    <th>Data Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($documentos) > 0): ?>
                    <?php foreach($documentos as $d): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($d['titulo']); ?></strong></td>
                        <td><?php echo htmlspecialchars($d['tipo_documento']); ?></td>
                        <td><?php echo htmlspecialchars($d['cliente_nome'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($d['numero_processo'] ?? '-'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($d['data_cadastro'])); ?></td>
                        <td class="btn-actions">
                            <?php if(!empty($d['caminho_arquivo'])): ?>
                                <a href="<?php echo BASE_URL . htmlspecialchars($d['caminho_arquivo']); ?>" target="_blank" class="btn btn-sm btn-green" title="Visualizar / Baixar Nuvem"><i class="fas fa-cloud-download-alt"></i></a>
                            <?php endif; ?>
                            <a href="form.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-blue" title="Editar Ficha"><i class="fas fa-edit"></i></a>
                            <a href="delete.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-red" title="Excluir da Nuvem" onclick="return confirm('ATENÇÃO: Isso apagará a ficha e o arquivo armazenado no servidor permanentemente! Confirmar?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Nenhum documento encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
