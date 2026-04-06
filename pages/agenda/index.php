<?php
$page_title = 'Agenda Legal';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';

$audiencias = [];
if (tem_permissao('audiencias')) {
    $stmtAudi = $pdo->query("SELECT a.id, a.data_audiencia, a.hora_audiencia, c.nome as cliente_nome, p.numero_processo, a.local_audiencia 
                             FROM audiencias a 
                             JOIN clientes c ON a.id_cliente = c.id 
                             JOIN processos p ON a.id_processo = p.id 
                             WHERE a.status = 'agendada'");
    $audiencias = $stmtAudi->fetchAll(PDO::FETCH_ASSOC);
}

$prazos = [];
if (tem_permissao('prazos')) {
    $stmtPrazos = $pdo->query("SELECT pr.id, pr.data_limite, pr.descricao_prazo, p.numero_processo 
                               FROM prazos pr 
                               JOIN processos p ON pr.id_processo = p.id 
                               WHERE pr.status = 'pendente'");
    $prazos = $stmtPrazos->fetchAll(PDO::FETCH_ASSOC);
}

// Montar os eventos para o calendário
$eventos_js = [];

foreach($audiencias as $a) {
    // Configura datas para o Google Calendar
    $data_inicio_sys = date('Ymd\THis', strtotime($a['data_audiencia'] . ' ' . $a['hora_audiencia']));
    $data_fim_sys = date('Ymd\THis', strtotime($a['data_audiencia'] . ' ' . $a['hora_audiencia'] . ' +1 hour'));
    
    $titulo_google = urlencode("Audiência: " . $a['cliente_nome']);
    $desc_google = urlencode("Processo: " . $a['numero_processo'] . "\nCliente: " . $a['cliente_nome']);
    $loc_google = urlencode($a['local_audiencia']);
    
    $link_google = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$titulo_google}&dates={$data_inicio_sys}/{$data_fim_sys}&details={$desc_google}&location={$loc_google}";
    
    $eventos_js[] = [
        'title' => 'Audiência: ' . $a['cliente_nome'],
        'start' => $a['data_audiencia'] . 'T' . $a['hora_audiencia'],
        'color' => '#f39c12',
        'extendedProps' => [
            'tipo' => 'Audiência',
            'processo' => $a['numero_processo'],
            'google_link' => $link_google
        ]
    ];
}

foreach($prazos as $pr) {
    $data_inicio_sys = date('Ymd', strtotime($pr['data_limite']));
    $data_fim_sys = date('Ymd', strtotime($pr['data_limite'] . ' +1 day'));
    
    $titulo_google = urlencode("Prazo Limite: " . $pr['descricao_prazo']);
    $desc_google = urlencode("Processo: " . $pr['numero_processo'] . "\nPrazo: " . $pr['descricao_prazo']);
    
    $link_google = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$titulo_google}&dates={$data_inicio_sys}/{$data_fim_sys}&details={$desc_google}";
    
    $eventos_js[] = [
        'title' => 'Prazo: ' . $pr['descricao_prazo'],
        'start' => $pr['data_limite'],
        'color' => '#d9534f',
        'allDay' => true,
        'extendedProps' => [
            'tipo' => 'Prazo',
            'processo' => $pr['numero_processo'],
            'google_link' => $link_google
        ]
    ];
}
?>

<!-- Importar biblioteca FullCalendar -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/pt-br.global.min.js'></script>

<style>
    /* Estilização Premium do Calendário */
    #calendar {
        max-width: 100% !important;
        margin: 0 auto;
        font-family: 'Inter', sans-serif;
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
    }
    
    .fc-toolbar {
        margin-bottom: 1.5em !important;
    }

    .fc-toolbar-title {
        font-size: 1.4em !important;
        font-weight: 700;
        color: #2c3e50;
        text-transform: capitalize;
    }

    /* Botões do calendário */
    .fc-button-primary {
        background-color: #f8f9fa !important;
        border-color: #e9ecef !important;
        color: #495057 !important;
        font-weight: 600 !important;
        text-transform: capitalize;
        border-radius: 6px !important;
        box-shadow: none !important;
        transition: all 0.2s ease;
        padding: 8px 16px !important;
    }

    .fc-button-primary:hover, 
    .fc-button-primary:not(:disabled):active, 
    .fc-button-primary:not(:disabled).fc-button-active {
        background-color: #e2e6ea !important;
        border-color: #dae0e5 !important;
        color: #212529 !important;
        transform: translateY(-1px);
    }

    .fc-button-primary:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 138, 188, 0.25) !important;
    }

    /* Cabeçalho dos dias da semana */
    .fc-theme-standard th {
        padding: 12px 0;
        background-color: #f8f9fa;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85em;
        letter-spacing: 0.5px;
        border: none !important;
    }
    
    .fc-scrollgrid {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }

    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #e9ecef;
    }

    /* Números dos dias */
    .fc-daygrid-day-number {
        font-weight: 600;
        color: #495057;
        padding: 8px !important;
        text-decoration: none !important;
    }

    /* Destaque para o dia de hoje */
    .fc-day-today {
        background-color: rgba(13, 138, 188, 0.05) !important;
    }
    
    .fc-day-today .fc-daygrid-day-number {
        background: #0D8ABC;
        color: #fff;
        border-radius: 50%;
        min-width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 4px;
        padding: 0 !important;
        box-shadow: 0 2px 6px rgba(13, 138, 188, 0.4);
    }

    /* Estilo dos Eventos */
    .fc-event {
        cursor: pointer;
        border-radius: 6px !important;
        border: none !important;
        padding: 4px 8px !important;
        font-size: 0.85em !important;
        font-weight: 500;
        margin-bottom: 4px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .fc-event:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 5;
    }

    .fc-h-event .fc-event-main {
        color: white;
    }

    /* Modal de evento mais moderno */
    .evento-modal {
        display: none; 
        position: fixed; z-index: 9999; left: 0; top: 0;
        width: 100%; height: 100%; overflow: auto; 
        background-color: rgba(0,0,0,0.4);
        backdrop-filter: blur(4px);
    }
    
    .evento-modal-content {
        background-color: #ffffff;
        margin: 8% auto;
        padding: 30px;
        border: none;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        animation: modalFadeIn 0.3s ease-out;
    }
    
    @keyframes modalFadeIn {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .evento-fechar {
        color: #adb5bd;
        float: right;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.2s;
    }
    
    .evento-fechar:hover {
        color: #dc3545;
        text-decoration: none;
    }
    
    .modal-detail-row {
        margin-bottom: 12px;
        font-size: 15px;
        color: #495057;
    }
    
    .modal-detail-row strong {
        color: #212529;
        display: inline-block;
        width: 90px;
    }

    .btn-google {
        background-color: #4285F4;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        margin-top: 25px;
        width: 100%;
        transition: all 0.2s;
        box-shadow: 0 4px 10px rgba(66, 133, 244, 0.3);
    }
    
    .btn-google:hover {
        background-color: #357AE8;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(66, 133, 244, 0.4);
    }
    
    /* Responsividade Premium para Mobile */
    @media (max-width: 768px) {
        .panel-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 12px;
        }
        
        .fc-toolbar {
            flex-direction: column;
            gap: 15px;
        }
        
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        
        .fc-toolbar-title {
            font-size: 1.2em !important;
            text-align: center;
        }
        
        #calendar {
            padding: 10px;
        }
        
        .fc-button-primary {
            padding: 6px 12px !important;
            font-size: 13px !important;
        }
    }
</style>

<div class="panel">
    <div class="panel-header" style="justify-content: space-between;">
        <h3><i class="fas fa-calendar-alt text-blue"></i> Calendário Jurídico</h3>
        <span class="badge badge-success" style="font-size: 13px;"><i class="fab fa-google"></i> Integrado ao Google</span>
    </div>
    <div class="panel-body">
        <p class="text-muted" style="margin-bottom: 20px;">
            Dica: Clique no evento da sua agenda para visualizar detalhes ou adicioná-lo diretamente à sua conta do Google Agenda (Google Calendar).
        </p>
        <div id='calendar'></div>
    </div>
</div>

<!-- Modal para Detalhes do Evento -->
<div id="modal-evento" class="evento-modal">
  <div class="evento-modal-content">
    <span class="evento-fechar" onclick="fecharModal()">&times;</span>
    <h3 id="modal-titulo" style="margin-top:0; margin-bottom: 20px; color:#2c3e50; font-weight: 700; font-size: 1.3em;">Detalhes do Evento</h3>
    <div style="background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 20px; border: 1px solid #e9ecef;">
        <div class="modal-detail-row">
            <strong><i class="fas fa-tag text-muted"></i> Tipo:</strong>
            <span id="modal-tipo" style="font-weight: 500;"></span>
        </div>
        <div class="modal-detail-row" style="margin-bottom: 0;">
            <strong><i class="fas fa-folder-open text-muted"></i> Processo:</strong>
            <span id="modal-processo" style="color: var(--primary-color); font-weight: 600;"></span>
        </div>
    </div>
    <div style="text-align: right;">
        <a href="#" id="modal-google-btn" target="_blank" class="btn-google">
            <i class="fab fa-google"></i> Adicionar ao Google Agenda
        </a>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var eventosData = <?php echo json_encode($eventos_js, JSON_UNESCAPED_UNICODE); ?>;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth',
        locale: 'pt-br',
        height: 'auto', /* Ajuste de altura */
        contentHeight: 600, /* Limita a altura para não ficar tão "grande" */
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: window.innerWidth < 768 ? 'listMonth,dayGridMonth' : 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            list: 'Lista'
        },
        events: eventosData,
        eventClick: function(info) {
            info.jsEvent.preventDefault(); // don't let the browser navigate
            abrirModal(info.event);
        }
    });

    calendar.render();
});

function abrirModal(evento) {
    document.getElementById('modal-titulo').innerText = evento.title;
    document.getElementById('modal-tipo').innerText = evento.extendedProps.tipo;
    document.getElementById('modal-processo').innerText = evento.extendedProps.processo;
    document.getElementById('modal-google-btn').href = evento.extendedProps.google_link;
    
    document.getElementById('modal-evento').style.display = 'block';
}

function fecharModal() {
    document.getElementById('modal-evento').style.display = 'none';
}

// Fechar modal ao clicar fora dele
window.onclick = function(event) {
    var modal = document.getElementById('modal-evento');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
