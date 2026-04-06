<?php
// arquivar.php
require_once __DIR__ . '/../../includes/auth.php';
verifica_login();
require_once __DIR__ . '/../../config/conexao.php';

if ($id = $_GET['id'] ?? null) {
    try {
        $pdo->prepare("UPDATE tarefas SET arquivado = 1 WHERE id = ?")->execute([$id]);
        header("Location: index.php");
        exit;
    } catch(PDOException $e) {}
}
header("Location: index.php");
?>
