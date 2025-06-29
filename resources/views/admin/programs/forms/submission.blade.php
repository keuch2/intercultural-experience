@extends('layouts.admin')

@section('title', 'Respuesta - ' . $submission->user->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-alt"></i> Respuesta del Formulario
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.show', $program) }}">{{ $program->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.index', $program) }}">Formularios</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.show', [$program, $form]) }}">{{ $form->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.submissions', [$program, $form]) }}">Respuestas</a></li>
                    <li class="breadcrumb-item active">{{ $submission->user->name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.programs.forms.submissions', [$program, $form]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Respuesta de {{ $submission->user->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Usuario:</strong> {{ $submission->user->name }}<br>
                        <strong>Email:</strong> {{ $submission->user->email }}<br>
                        <strong>Fecha:</strong> {{ $submission->created_at->format('d/m/Y H:i') }}<br>
                        <strong>Estado:</strong> 
                        @switch($submission->status)
                            @case('submitted')
                                <span class="badge bg-warning">Enviado</span>
                                @break
                            @case('approved')
                                <span class="badge bg-success">Aprobado</span>
                                @break
                            @case('rejected')
                                <span class="badge bg-danger">Rechazado</span>
                                @break
                            @default
                                <span class="badge bg-secondary">{{ ucfirst($submission->status) }}</span>
                        @endswitch
                    </div>

                    @if($submission->form_data)
                        <h6>Datos del Formulario:</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                @foreach($submission->form_data as $field => $value)
                                <tr>
                                    <th width="30%">{{ ucfirst(str_replace('_', ' ', $field)) }}</th>
                                    <td>
                                        @if(is_array($value))
                                            {{ implode(', ', $value) }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($submission->status === 'submitted')
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="{{ route('admin.programs.forms.submission.review', [$program, $form, $submission]) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check"></i> Aprobar
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger w-100" 
                                data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times"></i> Rechazar
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Rechazo -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rechazar Respuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.programs.forms.submission.review', [$program, $form, $submission]) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="review_notes" class="form-label">Motivo del rechazo</label>
                        <textarea class="form-control" id="review_notes" name="review_notes" 
                                  rows="3" placeholder="Explique el motivo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
