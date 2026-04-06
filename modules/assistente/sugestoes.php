<?php
/**
 * Módulo Sugestões Inteligentes - LexFlow
 * Arquivo preparado para expandir as sugestões consultando IA externa (Ex: OpenAI).
 */

require_once __DIR__ . '/regras.php';

function lexflow_get_sugestoes($pdo, $processo_id, $tipo_acao) {
    // 1. Coletar sugestoes basicas do banco
    $chave_tipo = lexflow_normalizar_tipo($tipo_acao);
    $stmt_sug = $pdo->prepare("SELECT sugestao FROM sugestoes_juridicas WHERE tipo_processo = ? OR tipo_processo = 'geral'");
    $stmt_sug->execute([$chave_tipo]);
    $sugestoes_banco = $stmt_sug->fetchAll(PDO::FETCH_COLUMN);
    
    // Futuro: Chamada à API (ex: POST /v1/chat/completions)
    return $sugestoes_banco;
}
?>
