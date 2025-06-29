@extends('layouts.admin')

@section('title', 'Respuestas - ' . $form->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-clipboard-list"></i> Respuestas del Formulario
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.show', $program) }}">{{ $program->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.index', $program) }}">Formularios</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.show', [$program, $form]) }}">{{ $form->name }}</a></li>
                    <li class="breadcrumb-item active">Respuestas</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.programs.forms.show', [$program, $form]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Respuestas del Formulario: {{ $form->name }}</h5>
        </div>
        <div class="card-body">
            @if($submissions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submissions as $submission)
                            <tr>
                                <td>{{ $submission->user->name }}</td>
                                <td>{{ $submission->user->email }}</td>
                                <td>
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
                                </td>
                                <td>{{ $submission->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.programs.forms.submission', [$program, $form, $submission]) }}" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{ $submissions->links() }}
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Sin respuestas</h5>
                    <p class="text-muted">Este formulario no ha recibido respuestas aún.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
