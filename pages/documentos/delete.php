<?php
require_once __DIR__ . '/../../includes/auth.php';
verifica_login();
require_once __DIR__ . '/../../config/conexao.php';
if ($id = $_GET['id'] ?? null) { 
    try { 
        // 1. Localiza a ficha para saber onde está o arquivo
        $stmt = $pdo->prepare("SELECT caminho_arquivo FROM documentos WHERE id=?");
        $stmt->execute([$id]);
        $doc = $stmt->fetch();
        
        // 2. Apaga fisicamente do Servidor se existir
        if ($doc && !empty($doc['caminho_arquivo'])) {
            $caminho_absoluto = __DIR__ . '/../../' . $doc['caminho_arquivo'];
            if(file_exists($caminho_absoluto)) { @unlink($caminho_absoluto); }
        }
        
        // 3. Exclui a ficha
        $pdo->prepare("DELETE FROM documentos WHERE id=?")->execute([$id]); 
        header("Location: index.php"); 
    } catch(PDOException $e) {} 
}
?>
