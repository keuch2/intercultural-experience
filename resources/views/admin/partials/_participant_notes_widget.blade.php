{{-- Reusable participant notes widget. Expects: $user (User), $notes (collection of ParticipantNote). --}}
<div class="card shadow-sm mt-3">
    <div class="card-body py-3">
        <h6 class="card-title small text-muted text-uppercase mb-2">Notas</h6>

        @forelse($notes as $note)
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
        @empty
            <p class="text-muted small mb-2">Sin notas.</p>
        @endforelse

        <form method="POST" action="{{ route('admin.participants.notes.store', ['user' => $user->id]) }}">
            @csrf
            <textarea name="content" class="form-control form-control-sm mb-2" rows="3" placeholder="Agregar observaciones importantes..." maxlength="2000" required></textarea>
            <button type="submit" class="btn btn-sm btn-primary w-100">Guardar Nota</button>
        </form>
    </div>
</div>
