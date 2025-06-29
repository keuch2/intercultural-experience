@extends('layouts.admin')

@section('title', 'Vista Previa - ' . $form->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-eye"></i> Vista Previa del Formulario
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.show', $program) }}">{{ $program->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.index', $program) }}">Formularios</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.show', [$program, $form]) }}">{{ $form->name }}</a></li>
                    <li class="breadcrumb-item active">Vista Previa</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.programs.forms.edit', [$program, $form]) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.programs.forms.show', [$program, $form]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Información del Formulario -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $form->name }}</h4>
                            <small>Programa: {{ $program->name }}</small>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark">v{{ $form->version }}</span>
                            @if($form->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($form->description)
                <div class="card-body">
                    <p class="mb-0">{{ $form->description }}</p>
                </div>
                @endif
            </div>

            <!-- Formulario de Vista Previa -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt"></i> Formulario de Inscripción
                    </h5>
                    @if($form->min_age || $form->max_age)
                    <small class="text-muted">
                        Edad requerida: {{ $form->min_age ?? 'Sin mínimo' }} - {{ $form->max_age ?? 'Sin máximo' }} años
                    </small>
                    @endif
                </div>
                <div class="card-body">
                    @if(count($structure) > 0)
                        <!-- Formulario Preview -->
                        <form class="form-preview">
                            @foreach($structure as $sectionIndex => $section)
                                <div class="section-preview mb-4">
                                    @if($section['title'])
                                        <h5 class="section-title border-bottom pb-2 mb-3">
                                            {{ $section['title'] }}
                                        </h5>
                                    @endif
                                    
                                    @if($section['description'])
                                        <p class="text-muted mb-3">{{ $section['description'] }}</p>
                                    @endif

                                    <div class="row">
                                        @foreach($section['fields'] as $field)
                                            <div class="col-md-{{ $field->field_width ?? 12 }} mb-3">
                                                <label class="form-label">
                                                    {{ $field->field_label }}
                                                    @if($field->is_required)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                
                                                @if($field->description)
                                                    <small class="form-text text-muted d-block mb-2">
                                                        {{ $field->description }}
                                                    </small>
                                                @endif

                                                @switch($field->field_type)
                                                    @case('text')
                                                    @case('email')
                                                    @case('tel')
                                                    @case('url')
                                                        <input type="{{ $field->field_type }}" 
                                                               class="form-control" 
                                                               placeholder="{{ $field->placeholder }}"
                                                               {{ $field->is_required ? 'required' : '' }}
                                                               disabled>
                                                        @break

                                                    @case('textarea')
                                                        <textarea class="form-control" 
                                                                  rows="{{ $field->field_options['rows'] ?? 3 }}"
                                                                  placeholder="{{ $field->placeholder }}"
                                                                  {{ $field->is_required ? 'required' : '' }}
                                                                  disabled></textarea>
                                                        @break

                                                    @case('number')
                                                        <input type="number" 
                                                               class="form-control" 
                                                               placeholder="{{ $field->placeholder }}"
                                                               @if(isset($field->field_options['min'])) min="{{ $field->field_options['min'] }}" @endif
                                                               @if(isset($field->field_options['max'])) max="{{ $field->field_options['max'] }}" @endif
                                                               {{ $field->is_required ? 'required' : '' }}
                                                               disabled>
                                                        @break

                                                    @case('date')
                                                        <input type="date" 
                                                               class="form-control"
                                                               {{ $field->is_required ? 'required' : '' }}
                                                               disabled>
                                                        @break

                                                    @case('select')
                                                        <select class="form-select" 
                                                                {{ $field->is_required ? 'required' : '' }}
                                                                disabled>
                                                            <option value="">Seleccionar...</option>
                                                            @if($field->options)
                                                                @foreach($field->options as $option)
                                                                    <option value="{{ $option }}">{{ $option }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @break

                                                    @case('radio')
                                                        @if($field->options)
                                                            @foreach($field->options as $index => $option)
                                                                <div class="form-check">
                                                                    <input class="form-check-input" 
                                                                           type="radio" 
                                                                           name="{{ $field->field_name }}" 
                                                                           id="{{ $field->field_name }}_{{ $index }}"
                                                                           value="{{ $option }}"
                                                                           disabled>
                                                                    <label class="form-check-label" for="{{ $field->field_name }}_{{ $index }}">
                                                                        {{ $option }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                        @break

                                                    @case('checkbox')
                                                        @if($field->options)
                                                            @foreach($field->options as $index => $option)
                                                                <div class="form-check">
                                                                    <input class="form-check-input" 
                                                                           type="checkbox" 
                                                                           name="{{ $field->field_name }}[]" 
                                                                           id="{{ $field->field_name }}_{{ $index }}"
                                                                           value="{{ $option }}"
                                                                           disabled>
                                                                    <label class="form-check-label" for="{{ $field->field_name }}_{{ $index }}">
                                                                        {{ $option }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                        @break

                                                    @case('file')
                                                        <input type="file" 
                                                               class="form-control" 
                                                               {{ $field->is_required ? 'required' : '' }}
                                                               disabled>
                                                        @if(isset($field->field_options['accept']))
                                                            <small class="form-text text-muted">
                                                                Tipos de archivo permitidos: {{ $field->field_options['accept'] }}
                                                            </small>
                                                        @endif
                                                        @break

                                                    @case('signature')
                                                        <div class="signature-preview border rounded p-3 bg-light text-center">
                                                            <i class="fas fa-signature fa-2x text-muted mb-2"></i>
                                                            <p class="text-muted mb-0">Área de Firma</p>
                                                        </div>
                                                        @break

                                                    @default
                                                        <input type="text" 
                                                               class="form-control" 
                                                               placeholder="{{ $field->placeholder }}"
                                                               disabled>
                                                @endswitch
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                @if($sectionIndex < count($structure) - 1)
                                    <hr class="my-4">
                                @endif
                            @endforeach

                            <!-- Términos y Condiciones -->
                            @if($form->terms_and_conditions)
                                <hr class="my-4">
                                <div class="section-preview">
                                    <h5 class="section-title border-bottom pb-2 mb-3">
                                        Términos y Condiciones
                                    </h5>
                                    <div class="terms-preview border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                        {!! nl2br(e($form->terms_and_conditions)) !!}
                                    </div>
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" id="accept_terms" disabled>
                                        <label class="form-check-label" for="accept_terms">
                                            Acepto los términos y condiciones del programa
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <!-- Firmas -->
                            @if($form->requires_signature || $form->requires_parent_signature)
                                <hr class="my-4">
                                <div class="section-preview">
                                    <h5 class="section-title border-bottom pb-2 mb-3">
                                        Firmas Requeridas
                                    </h5>
                                    
                                    @if($form->requires_signature)
                                        <div class="mb-3">
                                            <label class="form-label">Firma del Participante <span class="text-danger">*</span></label>
                                            <div class="signature-preview border rounded p-3 bg-light text-center">
                                                <i class="fas fa-signature fa-2x text-muted mb-2"></i>
                                                <p class="text-muted mb-0">Área de Firma del Participante</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($form->requires_parent_signature)
                                        <div class="mb-3">
                                            <label class="form-label">Firma de Padre/Madre/Tutor <span class="text-danger">*</span></label>
                                            <div class="signature-preview border rounded p-3 bg-light text-center">
                                                <i class="fas fa-signature fa-2x text-muted mb-2"></i>
                                                <p class="text-muted mb-0">Área de Firma de Padre/Madre/Tutor</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Botones de Acción -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="fas fa-arrow-left"></i> Anterior
                                </button>
                                <button type="button" class="btn btn-primary" disabled>
                                    <i class="fas fa-paper-plane"></i> Enviar Formulario
                                </button>
                            </div>
                        </form>
                    @else
                        <!-- Formulario Vacío -->
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Formulario Vacío</h5>
                            <p class="text-muted">Este formulario no tiene campos configurados aún.</p>
                            <a href="{{ route('admin.programs.forms.edit', [$program, $form]) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Editar Formulario
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel Lateral con Información -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información del Formulario
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Programa</label>
                        <div class="fw-bold">{{ $program->name }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Formulario</label>
                        <div class="fw-bold">{{ $form->name }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Versión</label>
                        <div><span class="badge bg-info fs-6">v{{ $form->version }}</span></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Estado</label>
                        <div>
                            @if($form->is_active)
                                <span class="badge bg-success fs-6">Activo</span>
                            @else
                                <span class="badge bg-secondary fs-6">Inactivo</span>
                            @endif
                            
                            @if($form->is_published)
                                <span class="badge bg-primary fs-6">Publicado</span>
                            @else
                                <span class="badge bg-warning fs-6">Borrador</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Estadísticas</label>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <div class="fs-4 fw-bold text-primary">{{ $form->fields->count() }}</div>
                                    <small class="text-muted">Campos</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="fs-4 fw-bold text-success">{{ $form->submissions()->count() }}</div>
                                <small class="text-muted">Respuestas</small>
                            </div>
                        </div>
                    </div>

                    @if($form->min_age || $form->max_age)
                        <div class="mb-3">
                            <label class="form-label text-muted">Rango de Edad</label>
                            <div>{{ $form->min_age ?? 'Sin mínimo' }} - {{ $form->max_age ?? 'Sin máximo' }} años</div>
                        </div>
                    @endif

                    @if($form->requires_signature || $form->requires_parent_signature)
                        <div class="mb-3">
                            <label class="form-label text-muted">Firmas Requeridas</label>
                            <div>
                                @if($form->requires_signature)
                                    <i class="fas fa-check text-success"></i> Participante<br>
                                @endif
                                @if($form->requires_parent_signature)
                                    <i class="fas fa-check text-success"></i> Padres/Tutores
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-preview .form-control,
.form-preview .form-select {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.signature-preview {
    min-height: 100px;
}

.section-title {
    color: #495057;
    font-weight: 600;
}

.terms-preview {
    font-size: 0.875rem;
    line-height: 1.5;
}

.border-end {
    border-right: 1px solid #dee2e6 !important;
}
</style>
@endpush 