<?php
require_once __DIR__ . '/../../includes/auth.php';
verifica_login();
require_once __DIR__ . '/../../config/conexao.php';
$id = $_GET['id'] ?? null;
if ($id) { try { $pdo->prepare("DELETE FROM tarefas WHERE id=?")->execute([$id]); header("Location: index.php"); } catch(PDOException $e) {} }
?>
