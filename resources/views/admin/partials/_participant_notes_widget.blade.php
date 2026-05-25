{{-- Notas legacy (sin application_id) — visibles solo si existen notas huérfanas pre-rediseño.
     Fase 2: la creación de notas ahora se hace dentro de cada tarjeta de Aplicación. --}}
@if($notes->isNotEmpty())
<div class="card shadow-sm mt-3 border-warning border-2">
    <div class="card-body py-3">
        <h6 class="card-title small text-warning text-uppercase mb-2">
            <i class="fas fa-archive me-1"></i> Notas anteriores
        </h6>
        <p class="text-muted small mb-2" style="font-size:0.7rem;">
            Notas registradas antes del rediseño (sin aplicación específica).
        </p>

        @foreach($notes as $note)
            <div class="border-bottom pb-2 mb-2">
                <div class="small text-muted mb-1" style="font-size: 0.7rem;">
                    <i class="fas fa-user me-1"></i>{{ $note->admin->name ?? '—' }}
                    &middot; {{ $note->created_at->format('d/m/Y H:i') }}
                    <form method="POST" action="{{ route('admin.participants.notes.delete', ['user' => $user->id, 'note' => $note->id]) }}" class="d-inline ms-1" onsubmit="return confirm('¿Eliminar nota?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-link btn-sm p-0 text-danger" style="font-size: 0.65rem;"><i class="fas fa-times"></i></button>
                    </form>
                </div>
                <div class="small" style="white-space: pre-wrap; word-break: break-word;">{{ $note->content }}</div>
            </div>
        @endforeach
    </div>
</div>
@endif
