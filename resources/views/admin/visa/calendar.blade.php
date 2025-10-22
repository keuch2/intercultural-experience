@extends('layouts.admin')

@section('title', 'Calendario de Citas Consulares')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-alt"></i> Calendario de Citas Consulares
        </h1>
        <div>
            <a href="{{ route('admin.visa.dashboard') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
#calendar {
    max-width: 100%;
    margin: 0 auto;
}

.fc-event {
    cursor: pointer;
}

.fc-event:hover {
    opacity: 0.8;
}
</style>
@endsection

@section('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        },
        events: @json($appointments),
        eventClick: function(info) {
            if (info.event.url) {
                window.open(info.event.url, '_blank');
                return false;
            }
        },
        eventDidMount: function(info) {
            // Agregar tooltip
            info.el.setAttribute('title', info.event.title);
            info.el.setAttribute('data-toggle', 'tooltip');
            info.el.setAttribute('data-placement', 'top');
        },
        height: 'auto',
        contentHeight: 650,
        aspectRatio: 1.8,
        eventColor: '#4e73df',
        eventTextColor: '#ffffff',
    });
    
    calendar.render();
    
    // Inicializar tooltips de Bootstrap
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endsection
