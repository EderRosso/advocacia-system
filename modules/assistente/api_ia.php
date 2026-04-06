<?php
/**
 * Ponto de Integração Futuro para API Externa (Ex: ChatGPT)
 * 
 * Atualmente essa API atuará analisando "observações e descrições" enviadas para 
 * gerar predições.
 */

function lexflow_ia_request($contexto_texto) {
    // Esse é um ponto de entrada genérico para requisições de IA.
    // Ex: send_to_openai_api($contexto_texto);
    
    // Retorno fallback baseado em keywords nativas (strpos):
    return lexflow_analisar_observacoes($contexto_texto);
}
?>
