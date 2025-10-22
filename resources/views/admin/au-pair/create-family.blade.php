@extends('layouts.admin')

@section('title', 'Registrar Nueva Familia Host')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-home"></i> Registrar Nueva Familia Host
        </h1>
        <a href="{{ route('admin.au-pair.families') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Familias
        </a>
    </div>

    <form action="{{ route('admin.au-pair.families.store') }}" method="POST" id="familyForm">
        @csrf
        
        <!-- Información Básica -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-gradient-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user"></i> Información de la Familia
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required">Nombre de la Familia:</label>
                            <input type="text" name="family_name" class="form-control @error('family_name') is-invalid @enderror" 
                                   value="{{ old('family_name') }}" required>
                            @error('family_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required">Email:</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required">Nombre Padre/Madre 1:</label>
                            <input type="text" name="parent1_name" class="form-control @error('parent1_name') is-invalid @enderror" 
                                   value="{{ old('parent1_name') }}" required>
                            @error('parent1_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre Padre/Madre 2 (opcional):</label>
                            <input type="text" name="parent2_name" class="form-control @error('parent2_name') is-invalid @enderror" 
                                   value="{{ old('parent2_name') }}">
                            @error('parent2_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="required">Teléfono:</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone') }}" required>
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="required">Ciudad:</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" 
                                   value="{{ old('city') }}" required>
                            @error('city')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="required">Estado (2 letras):</label>
                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" 
                                   value="{{ old('state') }}" maxlength="2" required style="text-transform: uppercase;">
                            @error('state')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Ej: CA, NY, TX</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de los Niños -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-gradient-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-baby"></i> Información de los Niños
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="required">Número de Niños:</label>
                            <input type="number" name="number_of_children" id="number_of_children" 
                                   class="form-control @error('number_of_children') is-invalid @enderror" 
                                   value="{{ old('number_of_children', 1) }}" min="1" max="10" required>
                            @error('number_of_children')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="required">Edades de los Niños:</label>
                            <div id="children_ages_container">
                                <!-- Se generará dinámicamente con JavaScript -->
                            </div>
                            <small class="form-text text-muted">
                                Ingrese la edad de cada niño en años
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="has_infants" 
                                       name="has_infants" value="1" {{ old('has_infants') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="has_infants">
                                    <i class="fas fa-baby"></i> La familia tiene bebés (menores de 2 años)
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="has_special_needs" 
                                       name="has_special_needs" value="1" 
                                       {{ old('has_special_needs') ? 'checked' : '' }}
                                       onchange="toggleSpecialNeeds()">
                                <label class="custom-control-label" for="has_special_needs">
                                    <i class="fas fa-wheelchair"></i> Algún niño tiene necesidades especiales
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="special_needs_detail_row" style="display: none;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Detalles de las necesidades especiales:</label>
                            <textarea name="special_needs_detail" class="form-control" rows="3">{{ old('special_needs_detail') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hogar y Mascotas -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-gradient-warning text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-home"></i> Información del Hogar
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="has_pets" 
                                       name="has_pets" value="1" 
                                       {{ old('has_pets') ? 'checked' : '' }}
                                       onchange="togglePets()">
                                <label class="custom-control-label" for="has_pets">
                                    <i class="fas fa-paw"></i> La familia tiene mascotas
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group" id="pet_types_row" style="display: none;">
                            <label>Tipos de mascotas:</label>
                            <input type="text" name="pet_types" class="form-control" 
                                   value="{{ old('pet_types') }}" 
                                   placeholder="Ej: 2 perros, 1 gato">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="smoking_household" 
                                       name="smoking_household" value="1" 
                                       {{ old('smoking_household') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="smoking_household">
                                    <i class="fas fa-smoking"></i> Hay fumadores en el hogar
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requisitos para Au Pair -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-gradient-success text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user-check"></i> Requisitos para Au Pair
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Género Preferido:</label>
                            <select name="required_gender" class="form-control">
                                <option value="any" {{ old('required_gender') == 'any' ? 'selected' : '' }}>Cualquiera</option>
                                <option value="female" {{ old('required_gender') == 'female' ? 'selected' : '' }}>Femenino</option>
                                <option value="male" {{ old('required_gender') == 'male' ? 'selected' : '' }}>Masculino</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="drivers_license_required" 
                                       name="drivers_license_required" value="1" 
                                       {{ old('drivers_license_required') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="drivers_license_required">
                                    <i class="fas fa-car"></i> Requiere licencia de conducir
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="swimming_required" 
                                       name="swimming_required" value="1" 
                                       {{ old('swimming_required') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="swimming_required">
                                    <i class="fas fa-swimmer"></i> Debe saber nadar
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Oferta Económica -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-gradient-secondary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-dollar-sign"></i> Oferta Económica
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="required">Estipendio Semanal:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="weekly_stipend" class="form-control" 
                                       value="{{ old('weekly_stipend', 195.75) }}" 
                                       min="195.75" step="0.01" required>
                            </div>
                            <small class="form-text text-muted">Mínimo legal: $195.75/semana</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="required">Fondo Educativo Anual:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="education_fund" class="form-control" 
                                       value="{{ old('education_fund', 500) }}" 
                                       min="500" step="0.01" required>
                            </div>
                            <small class="form-text text-muted">Mínimo: $500/año</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Beneficios Adicionales:</label>
                            <textarea name="additional_benefits" class="form-control" rows="2" 
                                      placeholder="Ej: Uso de auto, gimnasio, viajes...">{{ old('additional_benefits') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="card shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-save"></i> Guardar Familia
                        </button>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('admin.au-pair.families') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.required:after {
    content: " *";
    color: red;
}
</style>
@endsection

@push('scripts')
<script>
// Generar campos de edad para niños
function generateAgeFields() {
    const numberOfChildren = parseInt(document.getElementById('number_of_children').value);
    const container = document.getElementById('children_ages_container');
    container.innerHTML = '';
    
    const row = document.createElement('div');
    row.className = 'row';
    
    for (let i = 0; i < numberOfChildren; i++) {
        const col = document.createElement('div');
        col.className = 'col-md-2 mb-2';
        col.innerHTML = `
            <input type="number" name="children_ages[]" class="form-control" 
                   placeholder="Niño ${i + 1}" min="0" max="18" required>
        `;
        row.appendChild(col);
    }
    
    container.appendChild(row);
}

// Mostrar/ocultar detalles de necesidades especiales
function toggleSpecialNeeds() {
    const checkbox = document.getElementById('has_special_needs');
    const detailRow = document.getElementById('special_needs_detail_row');
    detailRow.style.display = checkbox.checked ? 'block' : 'none';
}

// Mostrar/ocultar tipos de mascotas
function togglePets() {
    const checkbox = document.getElementById('has_pets');
    const petTypesRow = document.getElementById('pet_types_row');
    petTypesRow.style.display = checkbox.checked ? 'block' : 'none';
}

// Eventos al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    generateAgeFields();
    document.getElementById('number_of_children').addEventListener('change', generateAgeFields);
    
    // Verificar estados iniciales
    toggleSpecialNeeds();
    togglePets();
});

// Validación del formulario
document.getElementById('familyForm').addEventListener('submit', function(e) {
    const ages = document.getElementsByName('children_ages[]');
    let allAgesFilled = true;
    
    for (let age of ages) {
        if (!age.value) {
            allAgesFilled = false;
            age.classList.add('is-invalid');
        } else {
            age.classList.remove('is-invalid');
        }
    }
    
    if (!allAgesFilled) {
        e.preventDefault();
        alert('Por favor ingrese la edad de todos los niños');
    }
});
</script>
@endpush
