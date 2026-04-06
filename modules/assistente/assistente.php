<?php
/**
 * FUNÇÕES CENTRAIS DO ASSISTENTE JURÍDICO
 */
require_once __DIR__ . '/regras.php';

function lexflow_gerar_checklist_se_vazio($pdo, $processo_id, $tipo_acao) {
    global $lexflow_regras;
    
    // Verifica se já existe checklist
    $chk = $pdo->prepare("SELECT COUNT(*) FROM checklists WHERE processo_id = ?");
    $chk->execute([$processo_id]);
    if ($chk->fetchColumn() > 0) return; // já tem
    
    $chave = lexflow_normalizar_tipo($tipo_acao);
    if (isset($lexflow_regras[$chave]['checklist'])) {
        foreach($lexflow_regras[$chave]['checklist'] as $item) {
            $ins = $pdo->prepare("INSERT INTO checklists (processo_id, item) VALUES (?, ?)");
            $ins->execute([$processo_id, $item]);
        }
    } else {
        // Checklist genérico
        $pdo->prepare("INSERT INTO checklists (processo_id, item) VALUES (?, 'Procuração')")->execute([$processo_id]);
        $pdo->prepare("INSERT INTO checklists (processo_id, item) VALUES (?, 'Documentos pessoais')")->execute([$processo_id]);
    }
}

function lexflow_registrar_timeline($pdo, $processo_id, $descricao) {
    if(!$processo_id) return;
    $ins = $pdo->prepare("INSERT INTO timeline (processo_id, descricao) VALUES (?, ?)");
    $ins->execute([$processo_id, $descricao]);
}

function lexflow_gerar_prazos_automaticos($pdo, $processo_id, $tipo_acao) {
    global $lexflow_regras;
    
    // Verifica se já existem prazos propostos por LexFlow (evitar duplicatas no cadastro do processo)
    $chk = $pdo->prepare("SELECT COUNT(*) FROM prazos WHERE id_processo = ? AND descricao_prazo LIKE 'LexFlow: %'");
    $chk->execute([$processo_id]);
    if ($chk->fetchColumn() > 0) return;
    
    $chave = lexflow_normalizar_tipo($tipo_acao);
    if (isset($lexflow_regras[$chave]['prazos'])) {
        foreach($lexflow_regras[$chave]['prazos'] as $nome_prazo => $dias) {
            $data_limite = date('Y-m-d', strtotime("+$dias days"));
            $titulo = "LexFlow: " . ucfirst(str_replace('_', ' ', $nome_prazo));
            
            $ins = $pdo->prepare("INSERT INTO prazos (id_processo, descricao_prazo, data_limite, status) VALUES (?, ?, ?, 'pendente')");
            $ins->execute([$processo_id, $titulo, $data_limite]);
            
            lexflow_registrar_timeline($pdo, $processo_id, "Prazo automático gerado: $titulo ($dias dias).");
        }
    }
}
?>
