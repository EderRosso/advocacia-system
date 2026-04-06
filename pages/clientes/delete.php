<?php
require_once __DIR__ . '/../../includes/auth.php';
verifica_login();
require_once __DIR__ . '/../../config/conexao.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=deleted");
    } catch(PDOException $e) {
        echo "Erro ao excluir: " . $e->getMessage();
    }
}
?>
