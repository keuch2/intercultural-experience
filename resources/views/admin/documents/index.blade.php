@extends('layouts.admin')

@section('title', 'Gestión de Documentos')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Documentos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Documentos</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filtros
        </div>
        <div class="card-body">
            <form action="{{ route('admin.documents.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verificado</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="application_id" class="form-label">ID de Solicitud</label>
                            <input type="text" class="form-control" id="application_id" name="application_id" value="{{ request('application_id') }}" placeholder="ID de la solicitud">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="document_type" class="form-label">Tipo de Documento</label>
                            <select class="form-select" id="document_type" name="document_type">
                                <option value="">Todos</option>
                                <option value="passport" {{ request('document_type') == 'passport' ? 'selected' : '' }}>Pasaporte</option>
                                <option value="id_card" {{ request('document_type') == 'id_card' ? 'selected' : '' }}>Documento de Identidad</option>
                                <option value="cv" {{ request('document_type') == 'cv' ? 'selected' : '' }}>Currículum Vitae</option>
                                <option value="motivation_letter" {{ request('document_type') == 'motivation_letter' ? 'selected' : '' }}>Carta de Motivación</option>
                                <option value="recommendation_letter" {{ request('document_type') == 'recommendation_letter' ? 'selected' : '' }}>Carta de Recomendación</option>
                                <option value="certificate" {{ request('document_type') == 'certificate' ? 'selected' : '' }}>Certificado</option>
                                <option value="other" {{ request('document_type') == 'other' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('admin.documents.index') }}" class="btn btn-secondary">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-alt me-1"></i>
            Documentos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Solicitud</th>
                            <th>Usuario</th>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Fecha de Subida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                            <tr>
                                <td>{{ $document->id }}</td>
                                <td>
                                    <a href="{{ route('admin.applications.show', $document->application_id) }}">
                                        #{{ $document->application_id }}
                                    </a>
                                </td>
                                <td>{{ $document->application->user->name }}</td>
                                <td>
                                    @switch($document->document_type)
                                        @case('passport')
                                            Pasaporte
                                            @break
                                        @case('id_card')
                                            Documento de Identidad
                                            @break
                                        @case('cv')
                                            Currículum Vitae
                                            @break
                                        @case('motivation_letter')
                                            Carta de Motivación
                                            @break
                                        @case('recommendation_letter')
                                            Carta de Recomendación
                                            @break
                                        @case('certificate')
                                            Certificado
                                            @break
                                        @default
                                            {{ $document->document_type }}
                                    @endswitch
                                </td>
                                <td>{{ $document->file_name }}</td>
                                <td>
                                    @if($document->status == 'pending')
                                        <span class="badge bg-warning">Pendiente</span>
                                    @elseif($document->status == 'verified')
                                        <span class="badge bg-success">Verificado</span>
                                    @elseif($document->status == 'rejected')
                                        <span class="badge bg-danger">Rechazado</span>
                                    @endif
                                </td>
                                <td>{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ asset('storage/' . $document->file_path) }}" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($document->status == 'pending')
                                            <form action="{{ route('admin.documents.verify', $document->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('admin.documents.reject', $document->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay documentos disponibles.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-3">
                {{ $documents->links() }}
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Documentos por Estado
                </div>
                <div class="card-body">
                    <canvas id="documentsByStatusChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Documentos por Tipo
                </div>
                <div class="card-body">
                    <canvas id="documentsByTypeChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de documentos por estado
        var statusCtx = document.getElementById('documentsByStatusChart');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Pendientes', 'Verificados', 'Rechazados'],
                datasets: [{
                    data: [
                        {{ $documents->where('status', 'pending')->count() }},
                        {{ $documents->where('status', 'verified')->count() }},
                        {{ $documents->where('status', 'rejected')->count() }}
                    ],
                    backgroundColor: ['#ffc107', '#198754', '#dc3545'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Documentos por Estado'
                    }
                }
            }
        });
        
        // Gráfico de documentos por tipo
        var typeCtx = document.getElementById('documentsByTypeChart');
        new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: ['Pasaporte', 'Documento de Identidad', 'Currículum Vitae', 'Carta de Motivación', 'Carta de Recomendación', 'Certificado', 'Otro'],
                datasets: [{
                    data: [
                        {{ $documents->where('document_type', 'passport')->count() }},
                        {{ $documents->where('document_type', 'id_card')->count() }},
                        {{ $documents->where('document_type', 'cv')->count() }},
                        {{ $documents->where('document_type', 'motivation_letter')->count() }},
                        {{ $documents->where('document_type', 'recommendation_letter')->count() }},
                        {{ $documents->where('document_type', 'certificate')->count() }},
                        {{ $documents->where('document_type', 'other')->count() }}
                    ],
                    backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#fd7e14', '#20c997', '#adb5bd'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Documentos por Tipo'
                    }
                }
            }
        });
    });
</script>
@endsection
