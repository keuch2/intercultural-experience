@extends('layouts.admin')

@section('title', 'Editar Formulario - ' . $form->name)

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit"></i> Editar Formulario
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.show', $program) }}">{{ $program->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.index', $program) }}">Formularios</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.show', [$program, $form]) }}">{{ $form->name }}</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.programs.forms.preview', [$program, $form]) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Vista Previa
            </a>
            <a href="{{ route('admin.programs.forms.show', [$program, $form]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formBuilder" action="{{ route('admin.programs.forms.update', [$program, $form]) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Panel Principal de Edición -->
            <div class="col-lg-8">
                <!-- Información Básica del Formulario -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i> Información Básica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre del Formulario <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $form->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="version" class="form-label">Versión</label>
                                    <input type="text" class="form-control" id="version" name="version" 
                                           value="{{ $form->version }}" disabled>
                                    <small class="form-text text-muted">Para cambiar la versión, clona el formulario</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="2">{{ old('description', $form->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_age" class="form-label">Edad Mínima</label>
                                    <input type="number" class="form-control" id="min_age" name="min_age" 
                                           value="{{ old('min_age', $form->min_age) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_age" class="form-label">Edad Máxima</label>
                                    <input type="number" class="form-control" id="max_age" name="max_age" 
                                           value="{{ old('max_age', $form->max_age) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Constructor de Formulario -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-hammer"></i> Constructor de Formulario
                        </h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addSection">
                                <i class="fas fa-plus"></i> Añadir Sección
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" id="previewForm">
                                <i class="fas fa-eye"></i> Vista Previa
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Área de Construcción del Formulario -->
                        <div id="formCanvas" class="form-builder-area">
                            <div class="empty-section-content" id="emptyCanvas">
                                <i class="fas fa-mouse-pointer fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Arrastra campos aquí para construir tu formulario</h5>
                                <p class="text-muted">Comienza arrastrando campos desde el panel lateral o añade una nueva sección</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Términos y Condiciones -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-contract"></i> Términos y Condiciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="terms_and_conditions" class="form-label">Términos y Condiciones del Programa</label>
                            <textarea class="form-control" id="terms_and_conditions" name="terms_and_conditions" 
                                      rows="6">{{ old('terms_and_conditions', $form->terms_and_conditions) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral de Herramientas -->
            <div class="col-lg-4">
                <!-- Campos Disponibles -->
                <div class="card mb-3 field-palette">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tools"></i> Campos Disponibles
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Campos Básicos -->
                        <div class="field-category mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-font"></i> Texto
                            </h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="field-item" data-type="text" draggable="true">
                                        <i class="fas fa-font"></i>
                                        <span>Texto</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="field-item" data-type="textarea" draggable="true">
                                        <i class="fas fa-align-left"></i>
                                        <span>Área de Texto</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="field-item" data-type="email" draggable="true">
                                        <i class="fas fa-envelope"></i>
                                        <span>Email</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="field-item" data-type="tel" draggable="true">
                                        <i class="fas fa-phone"></i>
                                        <span>Teléfono</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos de Selección -->
                        <div class="field-category mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-list"></i> Selección
                            </h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="field-item" data-type="select" draggable="true">
                                        <i class="fas fa-caret-down"></i>
                                        <span>Lista</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="field-item" data-type="radio" draggable="true">
                                        <i class="fas fa-dot-circle"></i>
                                        <span>Opción Única</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="field-item" data-type="checkbox" draggable="true">
                                        <i class="fas fa-check-square"></i>
                                        <span>Múltiples</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos Especiales -->
                        <div class="field-category mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-star"></i> Especiales
                            </h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="field-item" data-type="number" draggable="true">
                                        <i class="fas fa-hashtag"></i>
                                        <span>Número</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="field-item" data-type="date" draggable="true">
                                        <i class="fas fa-calendar"></i>
                                        <span>Fecha</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="field-item" data-type="file" draggable="true">
                                        <i class="fas fa-paperclip"></i>
                                        <span>Archivo</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="field-item" data-type="url" draggable="true">
                                        <i class="fas fa-link"></i>
                                        <span>URL</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Propiedades del Campo Seleccionado -->
                <div class="card mt-3" id="fieldProperties" style="display: none;">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog"></i> Propiedades del Campo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="fieldPropertiesContent">
                            <!-- Se llenará dinámicamente -->
                        </div>
                    </div>
                </div>

                <!-- Información del Formulario -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info"></i> Información
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Versión</label>
                            <div><span class="badge bg-info">v{{ $form->version }}</span></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <div>
                                @if($form->is_active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                                
                                @if($form->is_published)
                                    <span class="badge bg-primary">Publicado</span>
                                @else
                                    <span class="badge bg-warning">Borrador</span>
                                @endif
                            </div>
                        </div>

                        <div class="row text-center">
                            <div class="col-6">
                                <div class="fs-5 fw-bold text-primary">{{ $form->fields->count() }}</div>
                                <small class="text-muted">Campos</small>
                            </div>
                            <div class="col-6">
                                <div class="fs-5 fw-bold text-success">{{ $form->submissions()->count() }}</div>
                                <small class="text-muted">Respuestas</small>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            
                            <a href="{{ route('admin.programs.forms.submissions', [$program, $form]) }}" 
                               class="btn btn-outline-info">
                                <i class="fas fa-clipboard-list"></i> Ver Respuestas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campos ocultos para los datos del formulario -->
        <input type="hidden" name="sections" id="sectionsData">
        <input type="hidden" name="fields" id="fieldsData">
    </form>
</div>

<!-- Modal para Configurar Sección -->
<div class="modal fade" id="sectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configurar Sección</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="sectionName" class="form-label">Nombre de la Sección</label>
                    <input type="text" class="form-control" id="sectionName" placeholder="ej: informacion_personal">
                </div>
                <div class="mb-3">
                    <label for="sectionTitle" class="form-label">Título Visible</label>
                    <input type="text" class="form-control" id="sectionTitle" placeholder="ej: Información Personal">
                </div>
                <div class="mb-3">
                    <label for="sectionDescription" class="form-label">Descripción</label>
                    <textarea class="form-control" id="sectionDescription" rows="2" 
                              placeholder="Descripción opcional de la sección..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveSectionBtn">Guardar Sección</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Vista Previa -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vista Previa del Formulario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- Se generará dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Función helper para crear modales que funciona con o sin Bootstrap
function createModal(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
        console.error(`Elemento con ID ${elementId} no encontrado`);
        return null;
    }
    
    // Si Bootstrap está disponible, usarlo
    if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
        return new window.bootstrap.Modal(element);
    }
    
    // Fallback: mostrar/ocultar modal manualmente
    return {
        show: function() {
            element.style.display = 'block';
            element.classList.add('show');
            document.body.classList.add('modal-open');
            
            // Crear backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'manual-backdrop';
            document.body.appendChild(backdrop);
        },
        hide: function() {
            element.style.display = 'none';
            element.classList.remove('show');
            document.body.classList.remove('modal-open');
            
            // Remover backdrop
            const backdrop = document.getElementById('manual-backdrop');
            if (backdrop) backdrop.remove();
        }
    };
}

function getModalInstance(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return null;
    
    if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
        return window.bootstrap.Modal.getInstance(element);
    }
    
    // Fallback
    return {
        hide: function() {
            element.style.display = 'none';
            element.classList.remove('show');
            document.body.classList.remove('modal-open');
            
            const backdrop = document.getElementById('manual-backdrop');
            if (backdrop) backdrop.remove();
        }
    };
}

// Variables globales
let formData = {
    sections: [],
    fields: []
};

let selectedField = null;
let fieldIdCounter = 1;
let sectionIdCounter = 1;

// Tipos de campos disponibles
const fieldTypes = {
    text: { icon: 'fas fa-font', label: 'Texto' },
    textarea: { icon: 'fas fa-align-left', label: 'Área de Texto' },
    email: { icon: 'fas fa-envelope', label: 'Email' },
    tel: { icon: 'fas fa-phone', label: 'Teléfono' },
    number: { icon: 'fas fa-hashtag', label: 'Número' },
    date: { icon: 'fas fa-calendar', label: 'Fecha' },
    select: { icon: 'fas fa-caret-down', label: 'Lista Desplegable' },
    radio: { icon: 'fas fa-dot-circle', label: 'Opción Única' },
    checkbox: { icon: 'fas fa-check-square', label: 'Opción Múltiple' },
    file: { icon: 'fas fa-paperclip', label: 'Archivo' },
    url: { icon: 'fas fa-link', label: 'URL' }
};

// Cargar datos existentes del formulario
const existingForm = @json($form);
const existingFields = @json($form->fields);

// Inicializar el formulario
document.addEventListener('DOMContentLoaded', function() {
    initializeFormBuilder();
    loadExistingFormData();
});

function initializeFormBuilder() {
    // Configurar drag and drop para campos
    setupFieldDragAndDrop();
    
    // Configurar eventos de botones
    document.getElementById('addSection').addEventListener('click', showSectionModal);
    document.getElementById('previewForm').addEventListener('click', showPreview);
    document.getElementById('saveSectionBtn').addEventListener('click', saveSection);
    
    // Configurar eventos del formulario
    document.getElementById('formBuilder').addEventListener('submit', function(e) {
        saveFormData();
    });
}

function loadExistingFormData() {
    if (existingFields && existingFields.length > 0) {
        // Organizar campos por secciones
        const sectionMap = new Map();
        
        existingFields.forEach(field => {
            const sectionName = field.section_name || 'default';
            if (!sectionMap.has(sectionName)) {
                sectionMap.set(sectionName, {
                    id: `section_${sectionIdCounter++}`,
                    name: sectionName,
                    title: field.section_title || (sectionName === 'default' ? 'Información General' : sectionName.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())),
                    description: field.section_description || ''
                });
            }
        });
        
        // Convertir campos existentes al formato interno
        formData.sections = Array.from(sectionMap.values());
        formData.fields = existingFields.map(field => ({
            id: `field_${fieldIdCounter++}`,
            field_type: field.field_type,
            field_label: field.field_label,
            field_key: field.field_key,
            description: field.description,
            placeholder: field.placeholder,
            is_required: field.is_required,
            options: field.options || [],
            section_name: field.section_name || 'default',
            order: field.sort_order || 0
        }));
        
        renderFormCanvas();
    }
}

function setupFieldDragAndDrop() {
    const fieldItems = document.querySelectorAll('.field-item');
    const canvas = document.getElementById('formCanvas');
    
    fieldItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.dataset.type);
            e.dataTransfer.effectAllowed = 'copy';
            
            // Crear elemento visual para el drag
            const dragElement = this.cloneNode(true);
            dragElement.style.opacity = '0.8';
            dragElement.style.transform = 'rotate(5deg)';
            e.dataTransfer.setDragImage(dragElement, 25, 25);
        });
    });
    
    // Configurar drop en el canvas principal
    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });
    
    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const fieldType = e.dataTransfer.getData('text/plain');
        if (fieldType && fieldType.trim() !== '') {
            // Si el drop es en el canvas pero no en una sección específica, agregar a la primera sección
            const target = e.target.closest('.section-content-wrapper');
            if (target) {
                const sectionName = target.dataset.section;
                addFieldToSection(fieldType, sectionName);
            } else {
                addFieldToForm(fieldType);
            }
        }
        
        // Limpiar el dataTransfer
        e.dataTransfer.clearData();
    });
    
    // Configurar eventos para las secciones existentes
    document.querySelectorAll('.section-content-wrapper').forEach(sectionContent => {
        sectionContent.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('drag-over');
            e.dataTransfer.dropEffect = 'copy';
        });
        
        sectionContent.addEventListener('dragleave', function(e) {
            // Solo remover la clase si realmente salimos del elemento
            if (!this.contains(e.relatedTarget)) {
                this.classList.remove('drag-over');
            }
        });
        
        sectionContent.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');
            
            const fieldType = e.dataTransfer.getData('text/plain');
            if (fieldType) {
                const sectionName = this.dataset.section;
                addFieldToSection(fieldType, sectionName);
            }
        });
    });
}

function addFieldToForm(fieldType) {
    // Si no hay secciones, crear una por defecto
    if (formData.sections.length === 0) {
        formData.sections.push({
            id: `section_${sectionIdCounter++}`,
            name: 'default',
            title: 'Información General',
            description: ''
        });
    }
    
    const fieldId = `field_${Date.now()}`;
    const newField = {
        id: `field_${fieldIdCounter++}`,
        field_type: fieldType,
        field_label: fieldTypes[fieldType].label,
        field_key: fieldId,
        description: '',
        placeholder: '',
        is_required: false,
        options: ['select', 'radio', 'checkbox'].includes(fieldType) ? ['Opción 1', 'Opción 2'] : [],
        section_name: formData.sections[0].name,
        order: formData.fields.length
    };
    
    formData.fields.push(newField);
    renderFormCanvas();
    
    // Seleccionar el nuevo campo para editarlo
    setTimeout(() => selectField(newField.id), 100);
}

function addFieldToSection(fieldType, sectionName) {
    const fieldId = `field_${Date.now()}`;
    const newField = {
        id: `field_${fieldIdCounter++}`,
        field_type: fieldType,
        field_label: fieldTypes[fieldType].label,
        field_key: fieldId,
        description: '',
        placeholder: '',
        is_required: false,
        options: ['select', 'radio', 'checkbox'].includes(fieldType) ? ['Opción 1', 'Opción 2'] : [],
        section_name: sectionName,
        order: formData.fields.filter(f => f.section_name === sectionName).length
    };
    
    formData.fields.push(newField);
    renderFormCanvas();
    
    // Seleccionar el nuevo campo para editarlo
    setTimeout(() => selectField(newField.id), 100);
}

function showSectionModal() {
    document.getElementById('sectionName').value = '';
    document.getElementById('sectionTitle').value = '';
    document.getElementById('sectionDescription').value = '';
    
    // Limpiar el dataset de edición
    delete document.getElementById('saveSectionBtn').dataset.editingId;
    
    const modal = createModal('sectionModal');
    if (modal) modal.show();
}

function saveSection() {
    const name = document.getElementById('sectionName').value.trim();
    const title = document.getElementById('sectionTitle').value.trim();
    const description = document.getElementById('sectionDescription').value.trim();
    const editingId = document.getElementById('saveSectionBtn').dataset.editingId;
    
    if (!name || !title) {
        alert('Por favor complete el nombre y título de la sección');
        return;
    }
    
    if (editingId) {
        // Editar sección existente
        const section = formData.sections.find(s => s.id === editingId);
        if (section) {
            const oldName = section.name;
            section.name = name.toLowerCase().replace(/\s+/g, '_');
            section.title = title;
            section.description = description;
            
            // Actualizar campos que pertenecen a esta sección
            formData.fields.forEach(field => {
                if (field.section_name === oldName) {
                    field.section_name = section.name;
                }
            });
        }
        
        // Limpiar el dataset
        delete document.getElementById('saveSectionBtn').dataset.editingId;
    } else {
        // Crear nueva sección
        const newSection = {
            id: `section_${sectionIdCounter++}`,
            name: name.toLowerCase().replace(/\s+/g, '_'),
            title: title,
            description: description
        };
        
        formData.sections.push(newSection);
    }
    
    renderFormCanvas();
    
    const modal = getModalInstance('sectionModal');
    if (modal) modal.hide();
}

function renderFormCanvas() {
    const canvas = document.getElementById('formCanvas');
    
    if (formData.sections.length === 0) {
        // Remover clase has-content cuando está vacío
        canvas.classList.remove('has-content');
        canvas.innerHTML = `
            <div class="empty-section-content">
                <div class="empty-canvas-content">
                    <i class="fas fa-clipboard-list empty-icon"></i>
                    <h5>Constructor de Formulario</h5>
                    <p class="text-muted">Arrastra campos desde el panel lateral para comenzar a construir tu formulario.</p>
                    <button type="button" class="btn btn-primary btn-lg" onclick="showSectionModal()">
                        <i class="fas fa-plus me-2"></i> Crear Primera Sección
                    </button>
                </div>
            </div>
        `;
        return;
    }
    
    // Agregar clase has-content cuando hay contenido
    canvas.classList.add('has-content');
    
    let html = '';
    
    formData.sections.forEach(section => {
        const sectionFields = formData.fields.filter(field => field.section_name === section.name);
        
        html += `
            <div class="form-section-container" data-section-id="${section.id}">
                <div class="section-header-wrapper">
                    <div class="section-header-main">
                        <div class="section-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="section-info">
                            <h6 class="section-title">${section.title}</h6>
                            ${section.description ? `<p class="section-description">${section.description}</p>` : ''}
                            <small class="section-meta">${sectionFields.length} campo${sectionFields.length !== 1 ? 's' : ''}</small>
                        </div>
                    </div>
                    <div class="section-controls">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editSection('${section.id}')" title="Editar sección">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSection('${section.id}')" title="Eliminar sección">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="section-content-wrapper" data-section="${section.name}">
                    ${sectionFields.length > 0 ? 
                        sectionFields.map(field => renderField(field)).join('') : 
                        `<div class="empty-section-content">
                            <i class="fas fa-inbox"></i>
                            <p>Esta sección está vacía</p>
                            <small class="text-muted">Arrastra campos aquí para agregarlos a esta sección</small>
                        </div>`
                    }
                </div>
            </div>
        `;
    });
    
    html += `
        <div class="add-section-area text-center mt-4">
            <button type="button" class="btn btn-outline-primary btn-lg" onclick="showSectionModal()">
                <i class="fas fa-plus me-2"></i>
                Agregar Nueva Sección
            </button>
        </div>
    `;
    
    canvas.innerHTML = html;
}

function renderField(field) {
    const icon = fieldTypes[field.field_type]?.icon || 'fas fa-question';
    
    return `
        <div class="form-field" data-field-id="${field.id}" onclick="selectField('${field.id}')">
            <div class="field-controls">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); editField('${field.id}')" title="Editar campo">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); removeField('${field.id}')" title="Eliminar campo">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="field-content">
                <div class="field-header">
                    <div class="field-icon">
                        <i class="${icon}"></i>
                    </div>
                    <div class="field-info">
                        <div class="field-label">${field.field_label}</div>
                        <div class="field-meta">
                            <span class="field-type">${fieldTypes[field.field_type]?.label || field.field_type}</span>
                            ${field.is_required ? '<span class="required-badge">Requerido</span>' : '<span class="optional-badge">Opcional</span>'}
                        </div>
                    </div>
                </div>
                ${field.description ? `<div class="field-description">${field.description}</div>` : ''}
                ${field.placeholder ? `<div class="field-placeholder">Placeholder: "${field.placeholder}"</div>` : ''}
                ${['select', 'radio', 'checkbox'].includes(field.field_type) && field.options ? 
                    `<div class="field-options">
                        <small class="text-muted">Opciones: ${field.options.slice(0, 3).join(', ')}${field.options.length > 3 ? '...' : ''}</small>
                    </div>` : ''
                }
            </div>
        </div>
    `;
}

function selectField(fieldId) {
    // Remover selección anterior
    document.querySelectorAll('.form-field.selected').forEach(el => el.classList.remove('selected'));
    
    // Seleccionar nuevo campo
    const fieldElement = document.querySelector(`[data-field-id="${fieldId}"]`);
    if (fieldElement) {
        fieldElement.classList.add('selected');
        selectedField = formData.fields.find(f => f.id === fieldId);
        showFieldProperties(selectedField);
    }
}

function showFieldProperties(field) {
    const propertiesPanel = document.getElementById('fieldProperties');
    const content = document.getElementById('fieldPropertiesContent');
    
    let html = `
        <div class="mb-3">
            <label class="form-label">Etiqueta del Campo</label>
            <input type="text" class="form-control" value="${field.field_label}" 
                   onchange="updateFieldProperty('${field.id}', 'field_label', this.value)">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Clave del Campo</label>
            <input type="text" class="form-control" value="${field.field_key}" 
                   onchange="updateFieldProperty('${field.id}', 'field_key', this.value)">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" rows="2" 
                      onchange="updateFieldProperty('${field.id}', 'description', this.value)">${field.description || ''}</textarea>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Placeholder</label>
            <input type="text" class="form-control" value="${field.placeholder || ''}" 
                   onchange="updateFieldProperty('${field.id}', 'placeholder', this.value)">
        </div>
        
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" ${field.is_required ? 'checked' : ''} 
                   onchange="updateFieldProperty('${field.id}', 'is_required', this.checked)">
            <label class="form-check-label">Campo Requerido</label>
        </div>
    `;
    
    // Opciones para campos de selección
    if (['select', 'radio', 'checkbox'].includes(field.field_type)) {
        html += `
            <div class="mb-3">
                <label class="form-label">Opciones</label>
                <div id="fieldOptions">
                    ${(field.options || []).map((option, index) => `
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" value="${option}" 
                                   onchange="updateFieldOption('${field.id}', ${index}, this.value)">
                            <button type="button" class="btn btn-outline-danger" 
                                    onclick="removeFieldOption('${field.id}', ${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" 
                        onclick="addFieldOption('${field.id}')">
                    <i class="fas fa-plus"></i> Añadir Opción
                </button>
            </div>
        `;
    }
    
    content.innerHTML = html;
    propertiesPanel.style.display = 'block';
}

function updateFieldProperty(fieldId, property, value) {
    const field = formData.fields.find(f => f.id === fieldId);
    if (field) {
        field[property] = value;
        renderFormCanvas();
    }
}

function updateFieldOption(fieldId, index, value) {
    const field = formData.fields.find(f => f.id === fieldId);
    if (field && field.options) {
        field.options[index] = value;
    }
}

function addFieldOption(fieldId) {
    const field = formData.fields.find(f => f.id === fieldId);
    if (field) {
        if (!field.options) field.options = [];
        field.options.push('Nueva opción');
        showFieldProperties(field);
    }
}

function removeFieldOption(fieldId, index) {
    const field = formData.fields.find(f => f.id === fieldId);
    if (field && field.options) {
        field.options.splice(index, 1);
        showFieldProperties(field);
    }
}

function removeField(fieldId) {
    if (confirm('¿Estás seguro de que quieres eliminar este campo?')) {
        formData.fields = formData.fields.filter(f => f.id !== fieldId);
        renderFormCanvas();
        document.getElementById('fieldProperties').style.display = 'none';
    }
}

function removeSection(sectionId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta sección y todos sus campos?')) {
        const section = formData.sections.find(s => s.id === sectionId);
        if (section) {
            // Eliminar campos de la sección
            formData.fields = formData.fields.filter(f => f.section_name !== section.name);
            // Eliminar sección
            formData.sections = formData.sections.filter(s => s.id !== sectionId);
            renderFormCanvas();
        }
    }
}

function reorderFieldsInSection(sectionName) {
    const sectionContent = document.querySelector(`[data-section="${sectionName}"]`);
    if (sectionContent) {
        const fieldElements = sectionContent.querySelectorAll('.form-field');
        fieldElements.forEach((element, index) => {
            const fieldId = element.dataset.fieldId;
            const field = formData.fields.find(f => f.id === fieldId);
            if (field) {
                field.order = index;
                field.section_name = sectionName;
            }
        });
    }
}

function showPreview() {
    const previewContent = document.getElementById('previewContent');
    
    let html = '<div class="preview-form">';
    
    formData.sections.forEach(section => {
        const sectionFields = formData.fields
            .filter(field => field.section_name === section.name)
            .sort((a, b) => a.order - b.order);
        
        if (sectionFields.length > 0) {
            html += `
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">${section.title}</h5>
                    ${section.description ? `<p class="text-muted">${section.description}</p>` : ''}
                    
                    <div class="row">
                        ${sectionFields.map(field => `
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    ${field.field_label}
                                    ${field.is_required ? '<span class="text-danger">*</span>' : ''}
                                </label>
                                ${renderPreviewField(field)}
                                ${field.description ? `<small class="form-text text-muted">${field.description}</small>` : ''}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
    });
    
    html += '</div>';
    
    previewContent.innerHTML = html;
    
    const modal = createModal('previewModal');
    modal.show();
}

function renderPreviewField(field) {
    switch (field.field_type) {
        case 'textarea':
            return `<textarea class="form-control" placeholder="${field.placeholder || ''}" disabled></textarea>`;
        case 'select':
            return `
                <select class="form-select" disabled>
                    <option>Seleccionar...</option>
                    ${(field.options || []).map(option => `<option>${option}</option>`).join('')}
                </select>
            `;
        case 'radio':
            return `
                <div>
                    ${(field.options || []).map((option, index) => `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="${field.field_key}" disabled>
                            <label class="form-check-label">${option}</label>
                        </div>
                    `).join('')}
                </div>
            `;
        case 'checkbox':
            return `
                <div>
                    ${(field.options || []).map((option, index) => `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" disabled>
                            <label class="form-check-label">${option}</label>
                        </div>
                    `).join('')}
                </div>
            `;
        case 'file':
            return `<input type="file" class="form-control" disabled>`;
        case 'date':
            return `<input type="date" class="form-control" disabled>`;
        case 'number':
            return `<input type="number" class="form-control" placeholder="${field.placeholder || ''}" disabled>`;
        default:
            return `<input type="${field.field_type}" class="form-control" placeholder="${field.placeholder || ''}" disabled>`;
    }
}

function editSection(sectionId) {
    const section = formData.sections.find(s => s.id === sectionId);
    if (section) {
        document.getElementById('sectionName').value = section.name;
        document.getElementById('sectionTitle').value = section.title;
        document.getElementById('sectionDescription').value = section.description || '';
        
        // Guardar el ID de la sección que se está editando
        document.getElementById('saveSectionBtn').dataset.editingId = sectionId;
        
        const modal = createModal('sectionModal');
        modal.show();
    }
}

function editField(fieldId) {
    selectField(fieldId);
}

function saveFormData() {
    // Guardar los datos en los campos ocultos
    document.getElementById('sectionsData').value = JSON.stringify(formData.sections);
    document.getElementById('fieldsData').value = JSON.stringify(formData.fields);
}
</script>
@endpush 