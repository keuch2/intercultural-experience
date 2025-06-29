@extends('layouts.admin')

@section('title', 'Crear Formulario - ' . $program->name)

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plus"></i> Crear Formulario
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.show', $program) }}">{{ $program->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.index', $program) }}">Formularios</a></li>
                    <li class="breadcrumb-item active">Crear</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.programs.forms.index', $program) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <form id="formBuilder" action="{{ route('admin.programs.forms.store', $program) }}" method="POST">
        @csrf
        
        <div class="row">
            <!-- Panel Principal de Construcción -->
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
                                    <input type="text" class="form-control" id="name" name="name" 
                                           placeholder="ej: Formulario de Inscripción 2025" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="version" class="form-label">Versión <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="version" name="version" 
                                           value="1.0" placeholder="ej: 1.0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="2" 
                                      placeholder="Descripción del formulario..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_age" class="form-label">Edad Mínima</label>
                                    <input type="number" class="form-control" id="min_age" name="min_age" 
                                           placeholder="ej: 18" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_age" class="form-label">Edad Máxima</label>
                                    <input type="number" class="form-control" id="max_age" name="max_age" 
                                           placeholder="ej: 30" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="requires_signature" 
                                           name="requires_signature" value="1">
                                    <label class="form-check-label" for="requires_signature">
                                        Requiere firma del participante
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="requires_parent_signature" 
                                           name="requires_parent_signature" value="1">
                                    <label class="form-check-label" for="requires_parent_signature">
                                        Requiere firma de padres/tutores
                                    </label>
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
                        <div id="formCanvas" class="form-canvas min-vh-50">
                            <div class="empty-canvas text-center py-5">
                                <i class="fas fa-mouse-pointer fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Arrastra campos aquí para construir tu formulario</h5>
                                <p class="text-muted">Comienza arrastrando campos desde el panel lateral</p>
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
                                      rows="6" placeholder="Ingrese los términos y condiciones específicos para este programa..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral de Herramientas -->
            <div class="col-lg-4">
                <!-- Plantillas de Formularios -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-magic"></i> Plantillas de Formularios
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="template-selector">
                            <div class="mb-3">
                                <label for="templateCategory" class="form-label">Categoría</label>
                                <select class="form-select" id="templateCategory">
                                    <option value="">Todas las categorías</option>
                                    @foreach($categories as $key => $name)
                                        <option value="{{ $key }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div id="templatesList">
                                @foreach($templates as $category => $categoryTemplates)
                                    <div class="template-category" data-category="{{ $category }}">
                                        <h6 class="text-muted mb-2">
                                            <i class="{{ $categoryTemplates->first()->icon ?? 'fas fa-file' }}"></i>
                                            {{ $categories[$category] ?? $category }}
                                        </h6>
                                        @foreach($categoryTemplates as $template)
                                            <div class="template-item mb-2" data-template-id="{{ $template->id }}">
                                                <div class="card card-body p-2 template-card">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">{{ $template->name }}</h6>
                                                            <small class="text-muted">{{ $template->description }}</small>
                                                            <div class="mt-1">
                                                                <small class="badge bg-light text-dark">
                                                                    {{ $template->fields_count }} campos
                                                                </small>
                                                                <small class="badge bg-light text-dark">
                                                                    {{ $template->sections_count }} secciones
                                                                </small>
                                                                @if($template->usage_count > 0)
                                                                    <small class="badge bg-success">
                                                                        {{ $template->usage_count }} usos
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="btn-group-vertical">
                                                            <button type="button" class="btn btn-sm btn-outline-primary load-template" 
                                                                    data-template-id="{{ $template->id }}">
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-success use-template" 
                                                                    data-template-id="{{ $template->id }}">
                                                                <i class="fas fa-magic"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paleta de Campos -->
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-toolbox"></i> Campos Disponibles
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="field-palette">
                            <!-- Campos Básicos -->
                            <div class="field-category mb-4">
                                <h6 class="text-muted mb-3">
                                    <i class="fas fa-keyboard"></i> Campos Básicos
                                </h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="field-item" data-type="text" draggable="true">
                                            <i class="fas fa-font"></i>
                                            <span>Texto</span>
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
                                        <div class="field-item" data-type="textarea" draggable="true">
                                            <i class="fas fa-align-left"></i>
                                            <span>Área Texto</span>
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
                                            <span>Radio</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="field-item" data-type="checkbox" draggable="true">
                                            <i class="fas fa-check-square"></i>
                                            <span>Checkbox</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="field-item" data-type="boolean" draggable="true">
                                            <i class="fas fa-toggle-on"></i>
                                            <span>Sí/No</span>
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
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.programs.forms.index', $program) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <div>
                        <button type="button" class="btn btn-info me-2" id="saveAsDraft">
                            <i class="fas fa-save"></i> Guardar como Borrador
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Crear Formulario
                        </button>
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
                    <input type="text" class="form-control" id="sectionName" placeholder="ej: Información Personal">
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
let selectedSection = null;
let fieldCounter = 0;
let sectionCounter = 0;

// Tipos de campos disponibles
const fieldTypes = {
    text: { label: 'Texto', icon: 'fas fa-font' },
    email: { label: 'Email', icon: 'fas fa-envelope' },
    tel: { label: 'Teléfono', icon: 'fas fa-phone' },
    number: { label: 'Número', icon: 'fas fa-hashtag' },
    date: { label: 'Fecha', icon: 'fas fa-calendar' },
    textarea: { label: 'Área de Texto', icon: 'fas fa-align-left' },
    select: { label: 'Lista Desplegable', icon: 'fas fa-caret-down' },
    radio: { label: 'Opción Única', icon: 'fas fa-dot-circle' },
    checkbox: { label: 'Casilla de Verificación', icon: 'fas fa-check-square' },
    file: { label: 'Archivo', icon: 'fas fa-paperclip' },
    url: { label: 'URL', icon: 'fas fa-link' }
};

document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
    initializeEventListeners();
    initializeTemplates();
});

function initializeDragAndDrop() {
    const formCanvas = document.getElementById('formCanvas');
    
    // Hacer el canvas un área de drop
    formCanvas.addEventListener('dragover', handleDragOver);
    formCanvas.addEventListener('drop', handleDrop);
    formCanvas.addEventListener('dragleave', handleDragLeave);

    // Hacer los campos de la paleta arrastrables
    const fieldItems = document.querySelectorAll('.field-item');
    fieldItems.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
    });
}

function initializeEventListeners() {
    // Botón añadir sección
    document.getElementById('addSection').addEventListener('click', showSectionModal);
    
    // Botón guardar sección
    document.getElementById('saveSectionBtn').addEventListener('click', saveSection);
    
    // Botón vista previa
    document.getElementById('previewForm').addEventListener('click', showPreview);
    
    // Botón guardar como borrador
    document.getElementById('saveAsDraft').addEventListener('click', saveAsDraft);
    
    // Submit del formulario
    document.getElementById('formBuilder').addEventListener('submit', handleFormSubmit);
}

function handleDragStart(e) {
    e.dataTransfer.setData('text/plain', e.target.dataset.type);
    e.dataTransfer.effectAllowed = 'copy';
    
    // Crear elemento visual para el drag
    const dragElement = e.target.cloneNode(true);
    dragElement.style.opacity = '0.8';
    dragElement.style.transform = 'rotate(5deg)';
    e.dataTransfer.setDragImage(dragElement, 25, 25);
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');
    
    const fieldType = e.dataTransfer.getData('text/plain');
    if (fieldType && fieldType.trim() !== '') {
        // Verificar que estamos agregando a la zona correcta
        const target = e.target.closest('#formCanvas');
        if (target) {
            addFieldToForm(fieldType);
        }
    }
    
    // Limpiar el dataTransfer
    e.dataTransfer.clearData();
}

function showSectionModal() {
    // Limpiar el modal
    document.getElementById('sectionName').value = '';
    document.getElementById('sectionTitle').value = '';
    document.getElementById('sectionDescription').value = '';
    
    // Mostrar el modal
    createModal('sectionModal').show();
}

function saveSection() {
    const name = document.getElementById('sectionName').value.trim();
    const title = document.getElementById('sectionTitle').value.trim();
    const description = document.getElementById('sectionDescription').value.trim();
    
    if (!name) {
        alert('El nombre de la sección es requerido');
        return;
    }
    
    const section = {
        id: 'section_' + (++sectionCounter),
        name: name.toLowerCase().replace(/\s+/g, '_'),
        title: title || name,
        description: description,
        fields: []
    };
    
    formData.sections.push(section);
    renderFormCanvas();
    
    // Cerrar modal
    getModalInstance(document.getElementById('sectionModal')).hide();
}

function addFieldToForm(fieldType, sectionId = null) {
    // Si no hay secciones, crear una por defecto
    if (formData.sections.length === 0) {
        formData.sections.push({
            id: 'section_' + (++sectionCounter),
            name: 'general',
            title: 'Información General',
            description: '',
            fields: []
        });
    }
    
    // Si no se especifica sección, usar la primera
    const targetSection = sectionId || formData.sections[0].id;
    
    const fieldKey = fieldType + '_' + fieldCounter;
    const field = {
        id: 'field_' + fieldCounter,
        section_name: formData.sections.find(s => s.id === targetSection)?.name || 'general',
        field_key: fieldKey,
        field_label: fieldTypes[fieldType]?.label || fieldType,
        field_type: fieldType,
        description: '',
        placeholder: '',
        options: fieldType === 'select' || fieldType === 'radio' || fieldType === 'checkbox' ? ['Opción 1', 'Opción 2'] : null,
        validation_rules: {},
        is_required: false,
        is_visible: true,
        sort_order: formData.fields.length,
        conditional_logic: null
    };
    
    formData.fields.push(field);
    renderFormCanvas();
    
    // Seleccionar el campo recién creado
    selectField(field.id);
}

function renderFormCanvas() {
    const canvas = document.getElementById('formCanvas');
    
    if (formData.sections.length === 0) {
        canvas.innerHTML = `
            <div class="empty-canvas">
                <div class="empty-canvas-content">
                    <i class="fas fa-clipboard-list empty-icon"></i>
                    <h5>Constructor de Formulario</h5>
                    <p class="text-muted">Arrastra campos desde el panel lateral para comenzar a construir tu formulario.</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="showSectionModal()">
                        <i class="fas fa-plus me-1"></i> Crear Primera Sección
                    </button>
                </div>
            </div>
        `;
        return;
    }
    
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
        <div class="add-section-area">
            <button type="button" class="btn btn-outline-primary btn-lg" onclick="showSectionModal()">
                <i class="fas fa-plus me-2"></i>
                Agregar Nueva Sección
            </button>
        </div>
    `;
    
    canvas.innerHTML = html;
    
    // Reinicializar sortable para las nuevas secciones
    formData.sections.forEach(section => {
        const sectionContent = document.querySelector(`[data-section="${section.name}"]`);
        if (sectionContent) {
            new Sortable(sectionContent, {
                group: 'form-fields',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onAdd: function(evt) {
                    const fieldId = evt.item.dataset.fieldId;
                    if (fieldId) {
                        const field = formData.fields.find(f => f.id === fieldId);
                        if (field) {
                            field.section_name = section.name;
                            renderFormCanvas();
                        }
                    }
                },
                onSort: function(evt) {
                    reorderFieldsInSection(section.name);
                }
            });
        }
    });
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

function reorderFields() {
    // Actualizar el orden de los campos basado en su posición en el DOM
    let newOrder = [];
    document.querySelectorAll('.form-field').forEach((element, index) => {
        const fieldId = element.dataset.fieldId;
        const field = formData.fields.find(f => f.id === fieldId);
        if (field) {
            field.sort_order = index;
            newOrder.push(field);
        }
    });
    formData.fields = newOrder;
}

function showPreview() {
    const previewContent = document.getElementById('previewContent');
    let html = '<div class="container-fluid">';
    
    formData.sections.forEach(section => {
        const sectionFields = formData.fields.filter(f => f.section_name === section.name);
        
        if (sectionFields.length > 0) {
            html += `
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">${section.title}</h5>
                        ${section.description ? `<p class="text-muted mb-0">${section.description}</p>` : ''}
                    </div>
                    <div class="card-body">
                        ${sectionFields.map(field => renderPreviewField(field)).join('')}
                    </div>
                </div>
            `;
        }
    });
    
    html += '</div>';
    previewContent.innerHTML = html;
    
    createModal('previewModal').show();
}

function renderPreviewField(field) {
    let html = `<div class="mb-3">`;
    html += `<label class="form-label">${field.field_label} ${field.is_required ? '<span class="text-danger">*</span>' : ''}</label>`;
    
    switch (field.field_type) {
        case 'textarea':
            html += `<textarea class="form-control" placeholder="${field.placeholder || ''}" disabled></textarea>`;
            break;
        case 'select':
            html += `<select class="form-control" disabled>`;
            html += `<option>Seleccionar...</option>`;
            (field.options || []).forEach(option => {
                html += `<option>${option}</option>`;
            });
            html += `</select>`;
            break;
        case 'radio':
            (field.options || []).forEach(option => {
                html += `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" disabled>
                        <label class="form-check-label">${option}</label>
                    </div>
                `;
            });
            break;
        case 'checkbox':
            (field.options || []).forEach(option => {
                html += `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" disabled>
                        <label class="form-check-label">${option}</label>
                    </div>
                `;
            });
            break;
        case 'boolean':
            html += `
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" disabled>
                    <label class="form-check-label">Sí</label>
                </div>
            `;
            break;
        case 'file':
            html += `<input type="file" class="form-control" disabled>`;
            break;
        case 'url':
            html += `<input type="url" class="form-control" disabled>`;
            break;
        default:
            html += `<input type="${field.field_type}" class="form-control" placeholder="${field.placeholder || ''}" disabled>`;
    }
    
    if (field.description) {
        html += `<div class="form-text">${field.description}</div>`;
    }
    
    html += `</div>`;
    return html;
}

function saveAsDraft() {
    // Marcar como borrador y enviar
    const form = document.getElementById('formBuilder');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'save_as_draft';
    input.value = '1';
    form.appendChild(input);
    
    handleFormSubmit();
}

function handleFormSubmit(e) {
    if (e) e.preventDefault();
    
    // Preparar datos para envío
    document.getElementById('sectionsData').value = JSON.stringify(formData.sections);
    document.getElementById('fieldsData').value = JSON.stringify(formData.fields);
    
    // Enviar formulario
    document.getElementById('formBuilder').submit();
}

// Funciones para plantillas
function initializeTemplates() {
    // Filtro de categorías
    document.getElementById('templateCategory').addEventListener('change', function() {
        const selectedCategory = this.value;
        const categories = document.querySelectorAll('.template-category');
        
        categories.forEach(category => {
            if (!selectedCategory || category.dataset.category === selectedCategory) {
                category.classList.remove('hidden');
            } else {
                category.classList.add('hidden');
            }
        });
    });
    
    // Botones de cargar plantilla
    document.querySelectorAll('.load-template').forEach(button => {
        button.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            loadTemplate(templateId);
        });
    });
    
    // Botones de usar plantilla directamente
    document.querySelectorAll('.use-template').forEach(button => {
        button.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            useTemplateDirectly(templateId);
        });
    });
}

function loadTemplate(templateId) {
    // Mostrar loading
    const button = document.querySelector(`[data-template-id="${templateId}"].load-template`);
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    fetch(`/admin/templates/${templateId}/data`)
        .then(response => response.json())
        .then(data => {
            // Cargar datos de la plantilla
            formData.sections = data.sections || [];
            formData.fields = data.fields || [];
            
            // Actualizar contadores
            sectionCounter = Math.max(...formData.sections.map(s => parseInt(s.id.replace('section_', ''))), 0);
            fieldCounter = Math.max(...formData.fields.map(f => parseInt(f.id.replace('field_', ''))), 0);
            
            // Aplicar configuraciones por defecto
            if (data.settings) {
                if (data.settings.requires_signature !== undefined) {
                    document.getElementById('requires_signature').checked = data.settings.requires_signature;
                }
                if (data.settings.requires_parent_signature !== undefined) {
                    document.getElementById('requires_parent_signature').checked = data.settings.requires_parent_signature;
                }
                if (data.settings.min_age) {
                    document.getElementById('min_age').value = data.settings.min_age;
                }
                if (data.settings.max_age) {
                    document.getElementById('max_age').value = data.settings.max_age;
                }
                if (data.settings.terms_and_conditions) {
                    document.getElementById('terms_and_conditions').value = data.settings.terms_and_conditions;
                }
            }
            
            // Actualizar nombre si está vacío
            const nameField = document.getElementById('name');
            if (!nameField.value.trim()) {
                nameField.value = data.template.name;
            }
            
            // Actualizar descripción si está vacía
            const descField = document.getElementById('description');
            if (!descField.value.trim()) {
                descField.value = data.template.description;
            }
            
            // Re-renderizar el canvas
            renderFormCanvas();
            
            // Mostrar mensaje de éxito
            showAlert('success', 'Plantilla cargada exitosamente');
            
            // Marcar plantilla como seleccionada
            document.querySelectorAll('.template-card').forEach(card => card.classList.remove('selected'));
            document.querySelector(`[data-template-id="${templateId}"] .template-card`).classList.add('selected');
        })
        .catch(error => {
            console.error('Error loading template:', error);
            showAlert('error', 'Error al cargar la plantilla');
        })
        .finally(() => {
            button.innerHTML = originalHtml;
            button.disabled = false;
        });
}

function useTemplateDirectly(templateId) {
    if (confirm('¿Desea crear el formulario directamente desde esta plantilla? Esto creará el formulario sin permitir ediciones.')) {
        // Crear formulario desde plantilla
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.programs.forms.from-template", $program) }}';
        
        // CSRF Token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfInput);
        
        // Template ID
        const templateInput = document.createElement('input');
        templateInput.type = 'hidden';
        templateInput.name = 'template_id';
        templateInput.value = templateId;
        form.appendChild(templateInput);
        
        // Nombre personalizado si existe
        const nameField = document.getElementById('name');
        if (nameField.value.trim()) {
            const nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = 'name';
            nameInput.value = nameField.value;
            form.appendChild(nameInput);
        }
        
        // Descripción personalizada si existe
        const descField = document.getElementById('description');
        if (descField.value.trim()) {
            const descInput = document.createElement('input');
            descInput.type = 'hidden';
            descInput.name = 'description';
            descInput.value = descField.value;
            form.appendChild(descInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endpush 