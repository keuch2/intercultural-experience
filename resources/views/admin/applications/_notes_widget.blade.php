{{-- Notas por Aplicación (collapsible dentro de cada tarjeta de Application).
     Espera: $user (User), $application (Application). Las notas se acceden via $application->notes. --}}
<div class="mt-3 pt-3 border-top">
    <a class="text-decoration-none small fw-bold text-muted" data-bs-toggle="collapse"
       href="#notes-app-{{ $application->id }}" role="button"
       aria-expanded="false" aria-controls="notes-app-{{ $application->id }}">
        <i class="fas fa-sticky-note me-1"></i>
        Notas ({{ $application->notes->count() }})
        <i class="fas fa-chevron-down ms-1" style="font-size:0.7rem;"></i>
    </a>

    <div class="collapse mt-2" id="notes-app-{{ $application->id }}">
        @forelse($application->notes as $note)
            <div class="border-start border-2 border-info ps-2 mb-2">
                <div class="small text-muted" style="font-size: 0.7rem;">
                    <i class="fas fa-user me-1"></i>{{ $note->admin->name ?? '—' }}
                    &middot; {{ $note->created_at->format('d/m/Y H:i') }}
                    <form method="POST"
                          action="{{ route('admin.participants.notes.delete', ['user' => $user->id, 'note' => $note->id]) }}"
                          class="d-inline ms-1"
                          onsubmit="return confirm('¿Eliminar nota?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-link btn-sm p-0 text-danger" style="font-size: 0.65rem;">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
                <div class="small" style="white-space: pre-wrap; word-break: break-word;">{{ $note->content }}</div>
            </div>
        @empty
            <p class="text-muted small mb-2">Sin notas para esta aplicación.</p>
        @endforelse

        <form method="POST" action="{{ route('admin.participants.notes.store', ['user' => $user->id]) }}" class="mt-2">
            @csrf
            <input type="hidden" name="application_id" value="{{ $application->id }}">
            <textarea name="content" class="form-control form-control-sm mb-2" rows="2"
                      placeholder="Agregar observación sobre esta aplicación..." maxlength="2000" required></textarea>
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus me-1"></i> Guardar Nota
            </button>
        </form>
    </div>
</div>
