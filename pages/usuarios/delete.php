<?php
require_once __DIR__ . '/../../includes/auth.php';
verifica_login(); verifica_admin();
require_once __DIR__ . '/../../config/conexao.php';
if ($id = $_GET['id'] ?? null) { 
    if ($id == $_SESSION['usuario_id']) die("Não pode se excluir.");
    try { $pdo->prepare("DELETE FROM usuarios WHERE id=?")->execute([$id]); header("Location: index.php"); } catch(PDOException $e) {} 
}
?>
