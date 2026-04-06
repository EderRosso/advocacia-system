<?php
/**
 * Engine de Alertas LexFlow
 */

function lexflow_gerar_html_alertas($prazos) {
    if (count($prazos) == 0) return '<p class="text-muted"><i class="fas fa-check"></i> Sem alertas críticos.</p>';
    
    $html = '<ul style="list-style: none; padding: 0;">';
    foreach($prazos as $pr) {
        if($pr['status'] == 'cumprido') continue;
        
        $dias = (int)$pr['dias_restantes'];
        $bg = '#d4edda'; $cor = '#155724'; $icon = 'check-circle'; // Seguro
        
        if ($dias < 0) { $bg = '#f8d7da'; $cor = '#721c24'; $icon = 'skull-crossbones'; } // Vencido
        elseif ($dias <= 1) { $bg = '#f8d7da'; $cor = '#721c24'; $icon = 'exclamation-circle'; } // Crítico
        elseif ($dias <= 3) { $bg = '#fff3cd'; $cor = '#856404'; $icon = 'exclamation-triangle'; } // Atenção
        
        $msg_dias = $dias >= 0 ? "Faltam $dias dias" : "VENCIDO!";
        $data_formatada = date('d/m', strtotime($pr['data_limite']));
        
        $html .= "<li style='background: {$bg}; color: {$cor}; padding: 10px; border-radius: 4px; margin-bottom: 8px; font-size: 13px; font-weight: 500;'>";
        $html .= "<i class='fas fa-{$icon}'></i> {$pr['desc_prazo']} - Vence dia {$data_formatada} ({$msg_dias})";
        $html .= "</li>";
    }
    $html .= '</ul>';
    
    return $html;
}
?>
