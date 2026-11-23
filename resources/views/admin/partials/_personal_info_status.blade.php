{{-- Estado del formulario de información personal de Admisión (app). Params: $application, $participantUser --}}
@php
    $piService = app(\App\Services\PersonalInfoService::class);
    $piStatus = $application && $participantUser ? $piService->status($participantUser, $application) : null;
@endphp
@if($piStatus)
    <div class="alert {{ $piStatus['confirmed'] ? 'alert-success' : ($piStatus['required'] ? 'alert-warning' : 'alert-light border') }} d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 mb-3">
        <div class="small">
            <i class="fas {{ $piStatus['confirmed'] ? 'fa-user-check' : 'fa-user-clock' }} me-1"></i>
            @if($piStatus['confirmed'])
                <strong>Información personal confirmada</strong> el {{ $application->personal_info_confirmed_at->format('d/m/Y H:i') }}
                @if($application->personal_info_confirmed_by && $application->personal_info_confirmed_by !== $application->user_id)
                    (marcada por IE)
                @endif
            @else
                <strong>Información personal sin confirmar.</strong>
                {{ $piStatus['required'] ? 'No puede subir documentos ni avanzar de Admisión hasta confirmarla desde la app.' : 'La confirmación todavía no es obligatoria (Configuración).' }}
                @if(! $piStatus['complete'])
                    <br>Faltan datos en: {{ collect($piStatus['missing_sections'])->map(fn ($k) => \App\Services\PersonalInfoService::SECTIONS[$k]['label'])->implode(', ') }}.
                @endif
            @endif
        </div>
        @if(! $piStatus['confirmed'])
            <form method="POST" action="{{ route('admin.applications.personal-info.confirm', $application) }}" onsubmit="return confirm('¿Marcar la información personal como confirmada?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-success" @disabled(! $piStatus['complete']) title="{{ $piStatus['complete'] ? '' : 'Completá los datos obligatorios en el Perfil General' }}">
                    <i class="fas fa-check me-1"></i> Marcar confirmada
                </button>
            </form>
        @endif
    </div>
@endif
