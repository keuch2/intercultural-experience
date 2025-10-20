@extends('layouts.admin')

@section('title', 'Editar Programa YFU')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Editar Programa YFU: {{ $program->name }}</h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.yfu-programs.show', $program) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="{{ route('admin.yfu-programs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h5>Por favor corrige los siguientes errores:</h5>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.yfu-programs.update', $program) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Información Básica -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Información Básica</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nombre del Programa *</label>
                                            <input type="text" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name', $program->name) }}" 
                                                   required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="subcategory" class="form-label">Subcategoría *</label>
                                            <select class="form-select @error('subcategory') is-invalid @enderror" 
                                                    id="subcategory" 
                                                    name="subcategory" 
                                                    required>
                                                <option value="">Selecciona una subcategoría</option>
                                                @foreach($subcategories as $subcategory)
                                                    <option value="{{ $subcategory }}" {{ old('subcategory', $program->subcategory) == $subcategory ? 'selected' : '' }}>
                                                        {{ $subcategory }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('subcategory')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Descripción</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4">{{ old('description', $program->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="country" class="form-label">País *</label>
                                            <input type="text" 
                                                   class="form-control @error('country') is-invalid @enderror" 
                                                   id="country" 
                                                   name="country" 
                                                   value="{{ old('country', $program->country) }}" 
                                                   required>
                                            @error('country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="location" class="form-label">Ubicación</label>
                                            <input type="text" 
                                                   class="form-control @error('location') is-invalid @enderror" 
                                                   id="location" 
                                                   name="location" 
                                                   value="{{ old('location', $program->location) }}">
                                            @error('location')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas y Duración -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Fechas y Duración</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Fecha de Inicio</label>
                                            <input type="date" 
                                                   class="form-control @error('start_date') is-invalid @enderror" 
                                                   id="start_date" 
                                                   name="start_date" 
                                                   value="{{ old('start_date', $program->start_date?->format('Y-m-d')) }}">
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">Fecha de Finalización</label>
                                            <input type="date" 
                                                   class="form-control @error('end_date') is-invalid @enderror" 
                                                   id="end_date" 
                                                   name="end_date" 
                                                   value="{{ old('end_date', $program->end_date?->format('Y-m-d')) }}">
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="application_deadline" class="form-label">Fecha Límite de Aplicación</label>
                                            <input type="date" 
                                                   class="form-control @error('application_deadline') is-invalid @enderror" 
                                                   id="application_deadline" 
                                                   name="application_deadline" 
                                                   value="{{ old('application_deadline', $program->application_deadline?->format('Y-m-d')) }}">
                                            @error('application_deadline')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="duration" class="form-label">Duración (días)</label>
                                            <input type="number" 
                                                   class="form-control @error('duration') is-invalid @enderror" 
                                                   id="duration" 
                                                   name="duration" 
                                                   value="{{ old('duration', $program->duration) }}" 
                                                   min="1"
                                                   readonly>
                                            <div class="form-text">Se calcula automáticamente al seleccionar las fechas</div>
                                            @error('duration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="capacity" class="form-label">Capacidad</label>
                                            <input type="number" 
                                                   class="form-control @error('capacity') is-invalid @enderror" 
                                                   id="capacity" 
                                                   name="capacity" 
                                                   value="{{ old('capacity', $program->capacity) }}" 
                                                   min="1">
                                            @error('capacity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Financiera -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Información Financiera</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cost" class="form-label">Costo</label>
                                            <input type="number" 
                                                   class="form-control @error('cost') is-invalid @enderror" 
                                                   id="cost" 
                                                   name="cost" 
                                                   value="{{ old('cost', $program->cost) }}" 
                                                   min="0" 
                                                   step="0.01">
                                            @error('cost')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="currency_id" class="form-label">Moneda</label>
                                            <select class="form-select @error('currency_id') is-invalid @enderror" 
                                                    id="currency_id" 
                                                    name="currency_id">
                                                <option value="">Selecciona una moneda</option>
                                                @foreach($currencies as $currency)
                                                    <option value="{{ $currency->id }}" {{ old('currency_id', $program->currency_id) == $currency->id ? 'selected' : '' }}>
                                                        {{ $currency->name }} ({{ $currency->symbol }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('currency_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Configuración</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="institution_id" class="form-label">Institución</label>
                                            <select class="form-select @error('institution_id') is-invalid @enderror" 
                                                    id="institution_id" 
                                                    name="institution_id">
                                                <option value="">Selecciona una institución</option>
                                                @foreach($institutions as $institution)
                                                    <option value="{{ $institution->id }}" {{ old('institution_id', $program->institution_id) == $institution->id ? 'selected' : '' }}>
                                                        {{ $institution->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('institution_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="is_active" class="form-label">Estado *</label>
                                            <select class="form-select @error('is_active') is-invalid @enderror" 
                                                    id="is_active" 
                                                    name="is_active" 
                                                    required>
                                                <option value="1" {{ old('is_active', $program->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Activo</option>
                                                <option value="0" {{ old('is_active', $program->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactivo</option>
                                            </select>
                                            @error('is_active')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.yfu-programs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Programa YFU
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Calcular duración automáticamente
function calculateDuration() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (end >= start) {
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            document.getElementById('duration').value = diffDays;
        } else {
            document.getElementById('duration').value = '';
        }
    }
}

// Agregar event listeners
document.getElementById('start_date').addEventListener('change', calculateDuration);
document.getElementById('end_date').addEventListener('change', calculateDuration);

// Calcular al cargar si hay valores
document.addEventListener('DOMContentLoaded', calculateDuration);
</script>
@endpush

@endsection
