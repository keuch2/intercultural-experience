@extends('layouts.admin')

@section('title', 'Solicitudes de acceso')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">Solicitudes de acceso</h1>
        <small class="text-muted">El perfil del programa se crea recién al aprobar. Al aprobar o rechazar, el participante recibe un aviso en la app.</small>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ url('/admin/applications/export') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-download me-1"></i> Exportar
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

@php
    $tabs = [
        'awaiting' => ['Por revisar', $counts['awaiting'] ?? null],
        'approved' => ['Aprobadas', $counts['approved'] ?? null],
        'rejected' => ['Rechazadas (historial)', $counts['rejected'] ?? null],
        'all' => ['Todas', null],
    ];
    $baseQuery = request()->except(['status', 'page']);
@endphp
<ul class="nav nav-tabs mb-3">
    @foreach($tabs as $key => [$label, $count])
        <li class="nav-item">
            <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ url('/admin/applications').'?'.http_build_query($baseQuery + ['status' => $key]) }}">
                {{ $label }}
                @if($count !== null)<span class="badge {{ $key === 'awaiting' && $count > 0 ? 'bg-danger' : 'bg-secondary' }} ms-1">{{ $count }}</span>@endif
            </a>
        </li>
    @endforeach
</ul>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ url('/admin/applications') }}" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nombre, email o ID">
            </div>
            <div class="col-md-3">
                <label for="program_id" class="form-label">Programa</label>
                <select class="form-select" id="program_id" name="program_id">
                    <option value="">Todos los programas</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_range" class="form-label">Fecha de solicitud</label>
                <input type="text" class="form-control" id="date_range" name="date_range" value="{{ request('date_range') }}" placeholder="DD/MM/YYYY - DD/MM/YYYY">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-search"></i></button>
                <a href="{{ url('/admin/applications').'?status='.$status }}" class="btn btn-secondary flex-fill"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Participante</th>
                        <th>Programa</th>
                        <th>Estado</th>
                        <th>Solicitud</th>
                        <th>Revisión</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $application)
                        <tr>
                            <td>
                                @if($application->user)
                                    <a href="{{ route('admin.participants.show', $application->user_id) }}" class="fw-semibold text-decoration-none">{{ $application->user->name }}</a>
                                    <div class="small text-muted">{{ $application->user->email }}@if($application->user->phone) · {{ $application->user->phone }}@endif</div>
                                @else
                                    <span class="text-muted">Usuario eliminado</span>
                                @endif
                            </td>
                            <td>{{ $application->program?->name ?? '—' }}</td>
                            <td>
                                @switch($application->status)
                                    @case('pending') <span class="badge bg-warning text-dark">Pendiente</span> @break
                                    @case('in_review') <span class="badge bg-info">En revisión</span> @break
                                    @case('approved') <span class="badge bg-success">Aprobada</span> @break
                                    @case('rejected') <span class="badge bg-danger">Rechazada</span> @break
                                    @default <span class="badge bg-secondary">{{ $application->status }}</span>
                                @endswitch
                            </td>
                            <td class="small">{{ $application->applied_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="small">
                                @if($application->reviewed_at)
                                    {{ $application->reviewed_at->format('d/m/Y H:i') }}
                                    @if($application->reviewer)<div class="text-muted">por {{ $application->reviewer->name }}</div>@endif
                                    @if($application->rejection_reason)<div class="text-danger">Motivo: {{ $application->rejection_reason }}</div>@endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ url('/admin/applications/'.$application->id) }}" class="btn btn-sm btn-outline-secondary" title="Ver detalle"><i class="fas fa-eye"></i></a>
                                @if(in_array($application->status, \App\Models\Application::AWAITING_REVIEW_STATUSES, true))
                                    <form action="{{ route('admin.applications.approve', $application) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Aprobar el acceso de {{ e($application->user?->name) }} a {{ e($application->program?->name) }}? Se crea su perfil en el programa y se le envía un aviso.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i> Aprobar</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectApplicationModal"
                                            data-action="{{ route('admin.applications.reject', $application) }}"
                                            data-name="{{ $application->user?->name }}" data-program="{{ $application->program?->name }}">
                                        <i class="fas fa-times me-1"></i> Rechazar
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{ $status === 'awaiting' ? 'No hay solicitudes por revisar.' : 'No se encontraron solicitudes.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
            <div class="d-flex justify-content-center mt-4">{{ $applications->links() }}</div>
        @endif
    </div>
</div>

<div class="modal fade" id="rejectApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="rejectApplicationForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-times-circle text-danger me-1"></i> Rechazar solicitud</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Vas a rechazar la solicitud de <strong data-field="name"></strong> a <strong data-field="program"></strong>.</p>
                    <ul class="small text-muted ps-3">
                        <li>No se crea perfil en el programa (si existía uno de antes, se elimina junto con sus documentos).</li>
                        <li>La solicitud queda en el historial y la persona puede volver a postular.</li>
                        <li>Le llega el aviso «Tu postulación no fue aprobada».</li>
                    </ul>
                    <label for="rejection_reason" class="form-label">Motivo (opcional, se incluye en el aviso)</label>
                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" maxlength="1000"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('rejectApplicationModal')?.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        this.querySelector('#rejectApplicationForm').action = button.dataset.action;
        this.querySelector('[data-field="name"]').textContent = button.dataset.name || '';
        this.querySelector('[data-field="program"]').textContent = button.dataset.program || '';
        this.querySelector('#rejection_reason').value = '';
    });

    // Script para el rango de fechas
    $(document).ready(function() {
        $('#date_range').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            },
            opens: 'left',
            autoUpdateInput: false
        });

        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        });

        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
</script>
@endpush
