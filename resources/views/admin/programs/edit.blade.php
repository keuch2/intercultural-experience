@extends('layouts.admin')

@section('title', 'Editar Programa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Editar Programa: {{ $program->name }}</h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.programs.forms.index', $program) }}" class="btn btn-success">
                                <i class="fas fa-file-alt"></i> Gestionar Formularios
                            </a>
                            <a href="{{ route('admin.programs.show', $program) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> Ver Programa
                            </a>
                            <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a Lista
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

                    <form action="{{ route('admin.programs.update', $program) }}" method="POST" enctype="multipart/form-data">
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
                                            <label for="category" class="form-label">Categoría *</label>
                                            <select class="form-select @error('category') is-invalid @enderror" 
                                                    id="category" 
                                                    name="category" 
                                                    required>
                                                <option value="">Seleccionar categoría</option>
                                                <option value="academic" {{ old('category', $program->category) == 'academic' ? 'selected' : '' }}>Académico</option>
                                                <option value="volunteer" {{ old('category', $program->category) == 'volunteer' ? 'selected' : '' }}>Voluntariado</option>
                                                <option value="internship" {{ old('category', $program->category) == 'internship' ? 'selected' : '' }}>Prácticas</option>
                                                <option value="language" {{ old('category', $program->category) == 'language' ? 'selected' : '' }}>Idiomas</option>
                                                <option value="cultural" {{ old('category', $program->category) == 'cultural' ? 'selected' : '' }}>Cultural</option>
                                                <option value="research" {{ old('category', $program->category) == 'research' ? 'selected' : '' }}>Investigación</option>
                                            </select>
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="country" class="form-label">País *</label>
                                            <select class="form-select @error('country') is-invalid @enderror" 
                                                    id="country" 
                                                    name="country" 
                                                    required>
                                                <option value="">Seleccionar país</option>
                                                <option value="España" {{ old('country', $program->country) == 'España' ? 'selected' : '' }}>España</option>
                                                <option value="México" {{ old('country', $program->country) == 'México' ? 'selected' : '' }}>México</option>
                                                <option value="Argentina" {{ old('country', $program->country) == 'Argentina' ? 'selected' : '' }}>Argentina</option>
                                                <option value="Colombia" {{ old('country', $program->country) == 'Colombia' ? 'selected' : '' }}>Colombia</option>
                                                <option value="Chile" {{ old('country', $program->country) == 'Chile' ? 'selected' : '' }}>Chile</option>
                                                <option value="Perú" {{ old('country', $program->country) == 'Perú' ? 'selected' : '' }}>Perú</option>
                                                <option value="Estados Unidos" {{ old('country', $program->country) == 'Estados Unidos' ? 'selected' : '' }}>Estados Unidos</option>
                                                <option value="Canadá" {{ old('country', $program->country) == 'Canadá' ? 'selected' : '' }}>Canadá</option>
                                                <option value="Reino Unido" {{ old('country', $program->country) == 'Reino Unido' ? 'selected' : '' }}>Reino Unido</option>
                                                <option value="Francia" {{ old('country', $program->country) == 'Francia' ? 'selected' : '' }}>Francia</option>
                                                <option value="Alemania" {{ old('country', $program->country) == 'Alemania' ? 'selected' : '' }}>Alemania</option>
                                                <option value="Italia" {{ old('country', $program->country) == 'Italia' ? 'selected' : '' }}>Italia</option>
                                                <option value="Brasil" {{ old('country', $program->country) == 'Brasil' ? 'selected' : '' }}>Brasil</option>
                                                <option value="Japón" {{ old('country', $program->country) == 'Japón' ? 'selected' : '' }}>Japón</option>
                                                <option value="China" {{ old('country', $program->country) == 'China' ? 'selected' : '' }}>China</option>
                                                <option value="Australia" {{ old('country', $program->country) == 'Australia' ? 'selected' : '' }}>Australia</option>
                                            </select>
                                            @error('country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="location" class="form-label">Ciudad/Ubicación</label>
                                            <input type="text" 
                                                   class="form-control @error('location') is-invalid @enderror" 
                                                   id="location" 
                                                   name="location" 
                                                   value="{{ old('location', $program->location) }}" 
                                                   placeholder="Madrid, Barcelona, etc.">
                                            @error('location')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Descripción *</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4" 
                                              required>{{ old('description', $program->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                                   value="{{ old('start_date', $program->start_date ? $program->start_date->format('Y-m-d') : '') }}">
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
                                                   value="{{ old('end_date', $program->end_date ? $program->end_date->format('Y-m-d') : '') }}">
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="application_deadline" class="form-label">Fecha Límite de Postulación</label>
                                            <input type="date" 
                                                   class="form-control @error('application_deadline') is-invalid @enderror" 
                                                   id="application_deadline" 
                                                   name="application_deadline" 
                                                   value="{{ old('application_deadline', $program->application_deadline ? $program->application_deadline->format('Y-m-d') : '') }}">
                                            @error('application_deadline')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="duration" class="form-label">Duración</label>
                                            <input type="text" 
                                                   class="form-control @error('duration') is-invalid @enderror" 
                                                   id="duration" 
                                                   name="duration" 
                                                   value="{{ old('duration', $program->duration) }}" 
                                                   placeholder="4 semanas, 1 semestre, etc.">
                                            @error('duration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="credits" class="form-label">Créditos Académicos</label>
                                            <input type="number" 
                                                   class="form-control @error('credits') is-invalid @enderror" 
                                                   id="credits" 
                                                   name="credits" 
                                                   value="{{ old('credits', $program->credits) }}" 
                                                   min="0">
                                            @error('credits')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Costo y Capacidad -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Costo y Capacidad</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="currency_id" class="form-label">Moneda *</label>
                                            <select class="form-select @error('currency_id') is-invalid @enderror" 
                                                    id="currency_id" 
                                                    name="currency_id" 
                                                    required>
                                                <option value="">Seleccionar moneda</option>
                                                @foreach($currencies as $currency)
                                                    <option value="{{ $currency->id }}" 
                                                            data-symbol="{{ $currency->symbol }}"
                                                            {{ old('currency_id', $program->currency_id) == $currency->id ? 'selected' : '' }}>
                                                        {{ $currency->code }} - {{ $currency->name }} ({{ $currency->symbol }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('currency_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="cost" class="form-label">Costo del Programa *</label>
                                            <div class="input-group">
                                                <span class="input-group-text" id="currency-symbol">
                                                    {{ $program->currency ? $program->currency->symbol : '$' }}
                                                </span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control @error('cost') is-invalid @enderror" 
                                                       id="cost" 
                                                       name="cost" 
                                                       value="{{ old('cost', $program->cost) }}" 
                                                       required>
                                            </div>
                                            @error('cost')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">
                                                Costo actual: {{ $program->formatted_cost ?? 'No definido' }}
                                                @if($program->currency)
                                                    (≈ {{ $program->formatted_cost_in_pyg }})
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="capacity" class="form-label">Capacidad Total *</label>
                                            <input type="number" 
                                                   class="form-control @error('capacity') is-invalid @enderror" 
                                                   id="capacity" 
                                                   name="capacity" 
                                                   value="{{ old('capacity', $program->capacity) }}" 
                                                   min="1" 
                                                   required>
                                            @error('capacity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">
                                                Disponibles: {{ $program->available_spots ?? 'No definido' }} de {{ $program->capacity ?? 0 }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Imagen y Estado -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Imagen y Estado</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="image_url" class="form-label">URL de Imagen</label>
                                            <input type="url" 
                                                   class="form-control @error('image_url') is-invalid @enderror" 
                                                   id="image_url" 
                                                   name="image_url" 
                                                   value="{{ old('image_url', $program->image_url) }}" 
                                                   placeholder="https://ejemplo.com/imagen.jpg">
                                            @error('image_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">URL directa de la imagen del programa</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="image" class="form-label">O subir nueva imagen</label>
                                            <input type="file" 
                                                   class="form-control @error('image') is-invalid @enderror" 
                                                   id="image" 
                                                   name="image" 
                                                   accept="image/*">
                                            @error('image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">JPG, PNG. Máximo 2MB. Deje vacío para mantener la imagen actual.</small>
                                        </div>

                                        @if($program->image_url)
                                            <div class="mb-3">
                                                <label class="form-label">Imagen Actual</label>
                                                <div>
                                                    <img src="{{ $program->image_url }}" 
                                                         alt="{{ $program->name }}" 
                                                         class="img-thumbnail" 
                                                         style="max-height: 200px; max-width: 100%;">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="is_active" class="form-label">Estado del Programa *</label>
                                            <select class="form-select @error('is_active') is-invalid @enderror" 
                                                    id="is_active" 
                                                    name="is_active" 
                                                    required>
                                                <option value="1" {{ old('is_active', $program->is_active) == '1' ? 'selected' : '' }}>Activo</option>
                                                <option value="0" {{ old('is_active', $program->is_active) == '0' ? 'selected' : '' }}>Inactivo</option>
                                            </select>
                                            @error('is_active')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Solo los programas activos son visibles para los usuarios</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Estadísticas del Programa</label>
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="row text-center">
                                                        <div class="col-4">
                                                            <div class="h5 text-primary">{{ $program->applications()->count() }}</div>
                                                            <small class="text-muted">Postulaciones</small>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="h5 text-success">{{ $program->applications()->where('status', 'approved')->count() }}</div>
                                                            <small class="text-muted">Aprobadas</small>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="h5 text-info">{{ $program->available_spots ?? 'N/A' }}</div>
                                                            <small class="text-muted">Cupos Libres</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('admin.programs.show', $program) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i> Ver Programa
                                        </a>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Actualizar Programa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Actualizar símbolo de moneda cuando se selecciona una moneda
        const currencySelect = document.getElementById('currency_id');
        const currencySymbol = document.getElementById('currency-symbol');
        
        currencySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const symbol = selectedOption.getAttribute('data-symbol');
            if (symbol) {
                currencySymbol.textContent = symbol;
            } else {
                currencySymbol.textContent = '$';
            }
        });

        // Validar que la fecha de fin sea posterior a la de inicio
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        
        function validateDates() {
            if (startDate.value && endDate.value) {
                if (new Date(endDate.value) <= new Date(startDate.value)) {
                    endDate.setCustomValidity('La fecha de finalización debe ser posterior a la fecha de inicio');
                } else {
                    endDate.setCustomValidity('');
                }
            }
        }
        
        startDate.addEventListener('change', validateDates);
        endDate.addEventListener('change', validateDates);
    });
</script>
@endpush
