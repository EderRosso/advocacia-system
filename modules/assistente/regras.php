<?php
/**
 * REGRAS DO ASSISTENTE JURÍDICO (LexFlow Inteligente)
 * Central de inteligência do sistema baseada em regras (Rule-based).
 */

$lexflow_regras = [
    "alimentos" => [
        "prazos" => [
            "contestacao" => 15, // dias úteis ou corridos dependendo da lógica do escritório
            "documentacao" => 5
        ],
        "sugestoes" => [
            "Pedido de tutela de urgência (alimentos provisórios)", 
            "Analisar e anexar comprovantes de renda"
        ],
        "checklist" => [
            "Procuração assinada",
            "Cópia de RG e CPF",
            "Certidão de Nascimento dos menores",
            "Comprovante de residência",
            "Comprovante de despesas (escola, plano de saúde)"
        ]
    ],
    "execucao" => [
        "prazos" => [
            "embargos" => 15,
            "pagamento_voluntario" => 15
        ],
        "sugestoes" => [
            "Solicitar SISBAJUD", 
            "Pesquisar bens no RENAJUD/INFOJUD"
        ],
        "checklist" => [
            "Título executivo (Sentença ou Documento Particular)",
            "Cálculo atualizado do débito",
            "Documentos pessoais do Exequente",
            "Indicação inicial de bens à penhora"
        ]
    ],
    "trabalhista" => [
        "prazos" => [
            "contestacao" => 15,
            "recurso_ordinario" => 8
        ],
        "sugestoes" => [
            "Solicitar PPP", 
            "Verificar depósitos do FGTS",
            "Solicitar folhas de ponto"
        ],
        "checklist" => [
            "Carteira de Trabalho (CTPS)",
            "Termo de Rescisão",
            "Extrato analítico do FGTS",
            "Holerites do último ano"
        ]
    ],
    "divorcio" => [
        "prazos" => [
            "contestacao" => 15
        ],
        "sugestoes" => [
            "Levantamento de bens comuns para partilha",
            "Definição preliminar sobre guarda dos filhos"
        ],
        "checklist" => [
            "Certidão de Casamento",
            "Documentos dos bens (Cerveja/Imóveis/Carros)",
            "Certidão de Nascimento dos filhos"
        ]
    ]
];

/**
 * Normaliza strings para facilitar a busca de "tipo_acao" (ex: "Ação de Alimentos" -> "alimentos")
 */
function lexflow_normalizar_tipo($tipo) {
    if (!$tipo) return "geral";
    $tipo = strtolower(trim($tipo));
    if (strpos($tipo, 'alimento') !== false) return 'alimentos';
    if (strpos($tipo, 'execu') !== false) return 'execucao';
    if (strpos($tipo, 'trabalhist') !== false) return 'trabalhista';
    if (strpos($tipo, 'divorcio') !== false || strpos($tipo, 'divórcio') !== false) return 'divorcio';
    return "geral";
}

/**
 * Lê observações em busca de Keywords e retorna sugestões extras
 */
function lexflow_analisar_observacoes($texto) {
    $texto = strtolower($texto);
    $sugestoes = [];
    if (strpos($texto, 'réu não localizado') !== false || strpos($texto, 'reu nao localizado') !== false) {
        $sugestoes[] = "🔍 Identificada dificuldade de citação: Avaliar pedido de Citação por Edital ou Pesquisa SISBAJUD/INFOJUD de endereços.";
    }
    if (strpos($texto, 'inadimplência') !== false || strpos($texto, 'nao pagou') !== false) {
        $sugestoes[] = "⚠️ Inadimplência detectada: Sugerido iniciar fase de Cumprimento de Sentença (Execução).";
    }
    if (strpos($texto, 'urgente') !== false || strpos($texto, 'risco de vida') !== false) {
        $sugestoes[] = "🚨 Prioridade Máxima Identificada: Protocolizar Pedido de Tutela de Urgência Antecipada com máxima celeridade.";
    }
    return $sugestoes;
}
?>
