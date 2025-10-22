@extends('layouts.admin')

@section('title', 'Revisar Documento')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-search"></i> Revisar Documento
        </h1>
        <a href="{{ route('admin.documents.pending') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Pendientes
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <!-- Información del Documento -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información del Documento</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Tipo de Documento:</strong><br>
                        <span class="badge badge-info badge-lg mt-1">
                            {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Estado:</strong><br>
                        <span class="badge badge-{{ $document->status == 'pending' ? 'warning' : ($document->status == 'verified' ? 'success' : 'danger') }} badge-lg mt-1">
                            {{ strtoupper($document->status) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Fecha de Subida:</strong><br>
                        {{ $document->created_at->format('d/m/Y H:i') }}<br>
                        <small class="text-muted">Hace {{ $document->created_at->diffForHumans() }}</small>
                    </div>

                    @if($document->expiry_date)
                        <div class="mb-3">
                            <strong>Fecha de Vencimiento:</strong><br>
                            {{ \Carbon\Carbon::parse($document->expiry_date)->format('d/m/Y') }}
                            @if(\Carbon\Carbon::parse($document->expiry_date)->isPast())
                                <span class="badge badge-danger ml-2">Vencido</span>
                            @elseif(\Carbon\Carbon::parse($document->expiry_date)->diffInDays(now()) <= 30)
                                <span class="badge badge-warning ml-2">Vence Pronto</span>
                            @endif
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>Archivo:</strong><br>
                        {{ $document->original_filename }}<br>
                        <small class="text-muted">
                            {{ number_format($fileInfo['size'] / 1024, 2) }} KB
                        </small>
                    </div>

                    @if($document->notes)
                        <div class="mb-3">
                            <strong>Notas del Participante:</strong><br>
                            <p class="text-muted">{{ $document->notes }}</p>
                        </div>
                    @endif

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.documents.download', $document->id) }}" 
                           class="btn btn-info btn-block mb-2">
                            <i class="fas fa-download"></i> Descargar Documento
                        </a>

                        @if($document->status == 'pending')
                            <form action="{{ route('admin.documents.verify', $document->id) }}" 
                                  method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block"
                                        onclick="return confirm('¿Aprobar este documento?')">
                                    <i class="fas fa-check"></i> Aprobar Documento
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger btn-block" 
                                    onclick="$('#rejectModal').modal('show')">
                                <i class="fas fa-times"></i> Rechazar Documento
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información del Participante -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Participante</h6>
                </div>
                <div class="card-body text-center">
                    <img class="img-profile rounded-circle mb-3" 
                         src="https://ui-avatars.com/api/?name={{ urlencode($document->application->user->name) }}&background=4e73df&color=ffffff&size=100" 
                         width="100" height="100">
                    <h5>{{ $document->application->user->name }}</h5>
                    <p class="text-muted">{{ $document->application->user->email }}</p>
                    <a href="{{ route('admin.participants.show', $document->application->user->id) }}" 
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-user"></i> Ver Perfil Completo
                    </a>
                </div>
            </div>

            <!-- Información del Programa -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Programa</h6>
                </div>
                <div class="card-body">
                    <h5>{{ $document->application->program->name }}</h5>
                    <p class="mb-1">
                        <span class="badge badge-{{ $document->application->program->main_category == 'IE' ? 'primary' : 'success' }}">
                            {{ $document->application->program->main_category }}
                        </span>
                    </p>
                    <p class="text-muted mb-0">
                        {{ $document->application->program->subcategory }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Visor del Documento -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Vista Previa del Documento</h6>
                </div>
                <div class="card-body">
                    @if(Str::endsWith($document->file_path, '.pdf'))
                        <div class="embed-responsive" style="height: 800px;">
                            <iframe class="embed-responsive-item" 
                                    src="{{ route('admin.documents.download', $document->id) }}"
                                    type="application/pdf">
                            </iframe>
                        </div>
                    @elseif(in_array(pathinfo($document->file_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                        <div class="text-center">
                            <img src="{{ route('admin.documents.download', $document->id) }}" 
                                 class="img-fluid" 
                                 style="max-height: 800px;"
                                 alt="Documento">
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No se puede mostrar vista previa de este tipo de archivo.
                            <a href="{{ route('admin.documents.download', $document->id) }}" class="btn btn-sm btn-primary ml-3">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Checklist de Validación -->
            @if($document->status == 'pending')
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-warning">
                        <h6 class="m-0 font-weight-bold text-white">Checklist de Validación</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="check1">
                            <label class="form-check-label" for="check1">
                                El documento es legible y está completo
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="check2">
                            <label class="form-check-label" for="check2">
                                La información coincide con los datos del participante
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="check3">
                            <label class="form-check-label" for="check3">
                                El documento no está vencido (si aplica)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="check4">
                            <label class="form-check-label" for="check4">
                                El formato y calidad son adecuados
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="check5">
                            <label class="form-check-label" for="check5">
                                Cumple con todos los requisitos del programa
                            </label>
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-lightbulb"></i>
                            <strong>Tip:</strong> Verifica todos los puntos antes de aprobar el documento.
                        </div>
                    </div>
                </div>
            @endif

            <!-- Historial de Revisión -->
            @if($document->reviewed_at)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Historial de Revisión</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Revisado el:</strong> {{ $document->reviewed_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Revisado por:</strong> {{ $document->reviewer->name ?? 'N/A' }}</p>
                        @if($document->rejection_reason)
                            <p><strong>Motivo de rechazo:</strong></p>
                            <div class="alert alert-danger">{{ $document->rejection_reason }}</div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para Rechazar -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.documents.reject', $document->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Rechazar Documento</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        El participante recibirá una notificación con el motivo del rechazo.
                    </div>
                    
                    <div class="form-group">
                        <label>Motivo del Rechazo *</label>
                        <textarea name="rejection_reason" class="form-control" rows="5" required
                                  placeholder="Explica claramente por qué se rechaza este documento y qué debe corregir el participante..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Sugerencias comunes:</label>
                        <div class="btn-group-vertical btn-block">
                            <button type="button" class="btn btn-sm btn-outline-secondary text-left" 
                                    onclick="setReason('Documento ilegible o de mala calidad')">
                                Documento ilegible o de mala calidad
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-left" 
                                    onclick="setReason('Documento vencido')">
                                Documento vencido
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-left" 
                                    onclick="setReason('Información incompleta')">
                                Información incompleta
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-left" 
                                    onclick="setReason('No coincide con los datos del perfil')">
                                No coincide con los datos del perfil
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Rechazar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function setReason(reason) {
    $('textarea[name="rejection_reason"]').val(reason);
}
</script>
@endsection

@section('styles')
<style>
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endsection
