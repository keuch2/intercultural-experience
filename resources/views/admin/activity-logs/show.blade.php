@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-history me-2"></i>Detalle de Registro de Auditoría
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información Principal -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-info-circle me-2"></i>Información del Registro
                </h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar me-2 text-primary"></i>Fecha y Hora:</strong><br>
                        <span class="ms-4">{{ $activityLog->created_at->format('d/m/Y H:i:s') }}</span><br>
                        <small class="text-muted ms-4">{{ $activityLog->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-user me-2 text-primary"></i>Usuario:</strong><br>
                        @if($activityLog->causer)
                            <span class="ms-4">{{ $activityLog->causer->name }}</span><br>
                            <small class="text-muted ms-4">{{ $activityLog->causer->email }}</small>
                        @else
                            <span class="ms-4 text-muted"><i>Sistema Automático</i></span>
                        @endif
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-tag me-2 text-primary"></i>Tipo de Log:</strong><br>
                        <span class="ms-4 badge bg-secondary">{{ $activityLog->log_name ?? 'default' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-bolt me-2 text-primary"></i>Acción:</strong><br>
                        @if($activityLog->action)
                            @if(in_array($activityLog->action, ['created', 'create']))
                                <span class="ms-4 badge bg-success">{{ ucfirst($activityLog->action) }}</span>
                            @elseif(in_array($activityLog->action, ['updated', 'update']))
                                <span class="ms-4 badge bg-info">{{ ucfirst($activityLog->action) }}</span>
                            @elseif(in_array($activityLog->action, ['deleted', 'delete']))
                                <span class="ms-4 badge bg-danger">{{ ucfirst($activityLog->action) }}</span>
                            @else
                                <span class="ms-4 badge bg-secondary">{{ ucfirst($activityLog->action) }}</span>
                            @endif
                        @else
                            <span class="ms-4 text-muted">-</span>
                        @endif
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <strong><i class="fas fa-file-alt me-2 text-primary"></i>Descripción:</strong><br>
                    <div class="ms-4 mt-2 p-3 bg-light rounded">
                        {{ $activityLog->description }}
                    </div>
                </div>

                @if($activityLog->subject_type)
                <hr>
                <div class="mb-3">
                    <strong><i class="fas fa-cube me-2 text-primary"></i>Modelo Afectado:</strong><br>
                    <span class="ms-4">{{ class_basename($activityLog->subject_type) }} #{{ $activityLog->subject_id }}</span>
                    @if($activityLog->subject)
                        <br><small class="text-muted ms-4">
                            {{ $activityLog->subject->name ?? $activityLog->subject->title ?? '' }}
                        </small>
                    @endif
                </div>
                @endif

                @if($activityLog->changes && (isset($activityLog->changes['old']) || isset($activityLog->changes['attributes'])))
                <hr>
                <div class="mb-3">
                    <strong><i class="fas fa-exchange-alt me-2 text-primary"></i>Cambios Realizados:</strong><br>
                    <div class="ms-4 mt-2">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Campo</th>
                                    <th>Valor Anterior</th>
                                    <th>Nuevo Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activityLog->getChangedFields() as $field)
                                    <tr>
                                        <td><strong>{{ $field }}</strong></td>
                                        <td>
                                            <span class="text-danger">
                                                {{ $activityLog->getOldValue($field) ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success">
                                                {{ $activityLog->getNewValue($field) ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if($activityLog->properties && count($activityLog->properties) > 0)
                <hr>
                <div class="mb-3">
                    <strong><i class="fas fa-cog me-2 text-primary"></i>Propiedades Adicionales:</strong><br>
                    <div class="ms-4 mt-2">
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($activityLog->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Información Técnica -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-3">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-network-wired me-2"></i>Información Técnica
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>IP Address:</strong><br>
                    <code>{{ $activityLog->ip_address ?? '-' }}</code>
                </div>

                <div class="mb-3">
                    <strong>Método HTTP:</strong><br>
                    <span class="badge bg-secondary">{{ $activityLog->method ?? '-' }}</span>
                </div>

                <div class="mb-3">
                    <strong>URL:</strong><br>
                    <small class="text-break">{{ $activityLog->url ?? '-' }}</small>
                </div>

                <div class="mb-3">
                    <strong>User Agent:</strong><br>
                    <small class="text-muted">{{ Str::limit($activityLog->user_agent ?? '-', 100) }}</small>
                </div>

                <div class="mb-0">
                    <strong>Session ID:</strong><br>
                    <code class="small">{{ Str::limit($activityLog->session_id ?? '-', 20) }}</code>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header py-3 bg-secondary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-database me-2"></i>Metadatos
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>ID del Registro:</strong><br>
                    <code>#{{ $activityLog->id }}</code>
                </div>

                <div class="mb-2">
                    <strong>Causer Type:</strong><br>
                    <small>{{ $activityLog->causer_type ?? '-' }}</small>
                </div>

                <div class="mb-0">
                    <strong>Causer ID:</strong><br>
                    <code>{{ $activityLog->causer_id ?? '-' }}</code>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
