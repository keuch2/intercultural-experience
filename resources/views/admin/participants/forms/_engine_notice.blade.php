{{-- Aviso para programas gestionados por el motor: los datos específicos ya no se cargan aquí --}}
<div class="alert alert-info mb-0">
    <i class="fas fa-info-circle me-1"></i>
    <strong>{{ $program->name ?? 'Este programa' }}</strong> se gestiona con el flujo de trabajo del programa
    (etapas, documentos, checklist, pagos, test de inglés, visa, ofertas). No requiere datos específicos en este formulario.
    Guardá los cambios y continuá el proceso del participante desde
    @if(!empty($hubUrl))
        <a href="{{ $hubUrl }}" class="alert-link">Programas &gt; {{ $program->name }} &gt; Participantes</a>.
    @else
        el menú <strong>Programas</strong> del panel.
    @endif
</div>
