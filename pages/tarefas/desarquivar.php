<?php
// desarquivar.php
require_once __DIR__ . '/../../includes/auth.php';
verifica_login();
require_once __DIR__ . '/../../config/conexao.php';

if ($id = $_GET['id'] ?? null) {
    try {
        $pdo->prepare("UPDATE tarefas SET arquivado = 0 WHERE id = ?")->execute([$id]);
        header("Location: arquivadas.php");
        exit;
    } catch(PDOException $e) {}
}
header("Location: arquivadas.php");
?>
