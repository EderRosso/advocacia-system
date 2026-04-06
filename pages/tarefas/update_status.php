<?php
require_once __DIR__ . '/../../includes/auth.php';
verifica_login();
require_once __DIR__ . '/../../config/conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $id_quadro = $_POST['id_quadro'] ?? null;
    
    if ($id && $id_quadro) {
        try {
            // Verificar se o quadro existe
            $stmt_chk = $pdo->prepare("SELECT id FROM kanban_colunas WHERE id = ?");
            $stmt_chk->execute([$id_quadro]);
            if($stmt_chk->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE tarefas SET id_quadro = ? WHERE id = ?");
                if ($stmt->execute([$id_quadro, $id])) {
                    echo json_encode(['success' => true]);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Quadro inválido']);
                exit;
            }
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
