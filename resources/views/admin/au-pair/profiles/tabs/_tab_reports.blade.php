{{-- Tab: Informes (F) --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-chart-bar text-primary me-2"></i> F. Informes del Perfil
        </h5>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-4">Timeline de actividad y datos del participante para este perfil.</p>

        {{-- Timeline de actividad --}}
        <h6 class="text-uppercase text-muted small fw-bold mb-3">Timeline de Actividad</h6>
        
        <div class="position-relative ms-3" style="border-left: 2px solid #dee2e6;">
            @php
                $events = collect();
                if ($application) {
                    $events->push(['date' => $application->created_at, 'label' => 'Solicitud creada', 'icon' => 'fa-file-alt', 'color' => 'primary']);
                }
                if ($profile) {
                    $events->push(['date' => $profile->created_at, 'label' => 'Perfil Au Pair creado', 'icon' => 'fa-user-circle', 'color' => 'info']);
                }
                if ($application) {
                    foreach ($application->payments->where('status', 'verified') as $payment) {
                        $events->push(['date' => $payment->verified_at ?? $payment->created_at, 'label' => 'Pago verificado: ' . ($payment->concept ?? 'N/A'), 'icon' => 'fa-check-circle', 'color' => 'success']);
                    }
                    foreach ($application->documents->where('status', 'verified') as $doc) {
                        $events->push(['date' => $doc->verified_at ?? $doc->created_at, 'label' => 'Documento aprobado: ' . ($doc->name ?? $doc->document_type), 'icon' => 'fa-file-check', 'color' => 'success']);
                    }
                }
                foreach ($user->englishEvaluations as $eval) {
                    $events->push(['date' => $eval->evaluated_at ?? $eval->created_at, 'label' => "Test inglés #$eval->attempt_number: $eval->cefr_level ($eval->score pts)", 'icon' => 'fa-language', 'color' => 'warning']);
                }
                $events = $events->sortByDesc('date');
            @endphp

            @forelse($events as $event)
            <div class="d-flex align-items-start mb-3 ms-3">
                <div class="position-absolute" style="left: -8px;">
                    <span class="bg-{{ $event['color'] }} rounded-circle d-inline-block" style="width: 14px; height: 14px;"></span>
                </div>
                <div class="ms-3">
                    <div class="small fw-semibold">{{ $event['label'] }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        {{ $event['date'] ? \Carbon\Carbon::parse($event['date'])->format('d/m/Y H:i') : '-' }}
                        &middot; {{ $event['date'] ? \Carbon\Carbon::parse($event['date'])->diffForHumans() : '' }}
                    </div>
                </div>
            </div>
            @empty
            <div class="ms-3 py-3">
                <p class="text-muted small">No hay actividad registrada para este perfil.</p>
            </div>
            @endforelse
        </div>

        <hr class="my-4">

        {{-- Datos del participante para reporte --}}
        <h6 class="text-uppercase text-muted small fw-bold mb-3">Datos para Reporte</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <tbody>
                    <tr><td class="text-muted" style="width: 200px;">Nombre completo</td><td class="fw-semibold">{{ $user->name }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $user->email }}</td></tr>
                    <tr><td class="text-muted">Fecha de inscripción</td><td>{{ $application ? $application->created_at->format('d/m/Y') : '-' }}</td></tr>
                    <tr><td class="text-muted">Edad</td><td>{{ $user->age ?? '-' }}</td></tr>
                    <tr><td class="text-muted">País</td><td>{{ $user->country ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Nivel de escolaridad</td><td>{{ $user->academic_level ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Nivel de inglés</td><td>{{ $stages['_meta']['english_level'] ?? 'Sin evaluar' }}</td></tr>
                    <tr><td class="text-muted">Pagos realizados</td><td>{{ ($stages['_meta']['payment1_verified'] ? 1 : 0) + ($stages['_meta']['payment2_verified'] ? 1 : 0) }} / 2</td></tr>
                    <tr><td class="text-muted">Documentos aprobados</td><td>{{ $application ? $application->documents->where('status', 'verified')->count() : 0 }}</td></tr>
                    <tr>
                        <td class="text-muted">Estado del perfil</td>
                        <td>
                            @if($profile)
                                {{ ucfirst($profile->profile_status) }}
                            @else
                                Sin perfil
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
