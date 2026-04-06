<?php
require_once __DIR__ . '/../../includes/auth.php';
verifica_login();
verifica_admin();
require_once __DIR__ . '/../../config/conexao.php';
$id = $_GET['id'] ?? null;
if ($id) { 
    try { 
        $pdo->prepare("DELETE FROM kanban_colunas WHERE id=?")->execute([$id]); 
        header("Location: quadros.php"); 
    } catch(PDOException $e) {} 
}
?>
