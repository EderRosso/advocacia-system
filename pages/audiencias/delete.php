<?php
require_once __DIR__ . '/../../includes/auth.php';
verifica_login();
require_once __DIR__ . '/../../config/conexao.php';

if ($id = $_GET['id'] ?? null) {
    try {
        $pdo->prepare("DELETE FROM audiencias WHERE id=?")->execute([$id]);
        header("Location: index.php?msg=deleted");
    } catch(PDOException $e) { echo "Erro: " . $e->getMessage(); }
}
?>
