@extends('layouts.admin')

@section('title', 'Experiencias con Niños')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-baby"></i> Experiencias con Niños - {{ $user->name }}
        </h1>
        <div>
            <button class="btn btn-success" data-toggle="modal" data-target="#addExperienceModal">
                <i class="fas fa-plus"></i> Agregar Experiencia
            </button>
            <a href="{{ route('admin.au-pair.profile.show', $user->auPairProfile->id ?? 0) }}" 
               class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Perfil
            </a>
        </div>
    </div>

    <!-- Resumen de Experiencia -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Experiencias</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $experiences->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Meses Totales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $experiences->sum('duration_months') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Con Bebés</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $experiences->where('cared_for_infants', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-baby-carriage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Necesidades Especiales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $experiences->where('special_needs_experience', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wheelchair fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Experiencias -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history"></i> Historial de Experiencias con Niños
            </h6>
        </div>
        <div class="card-body">
            @if($experiences->count() > 0)
                <div class="accordion" id="experiencesAccordion">
                    @foreach($experiences as $index => $exp)
                        <div class="card mb-3">
                            <div class="card-header" id="heading{{ $index }}">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center" 
                                            type="button" data-toggle="collapse" 
                                            data-target="#collapse{{ $index }}" 
                                            aria-expanded="{{ $index == 0 ? 'true' : 'false' }}">
                                        <div>
                                            <strong>{{ $exp->experience_type }}</strong>
                                            <span class="ml-3 text-muted">
                                                <i class="fas fa-clock"></i> {{ $exp->duration_months }} meses
                                            </span>
                                        </div>
                                        <div>
                                            @if($exp->cared_for_infants)
                                                <span class="badge badge-info">Con Bebés</span>
                                            @endif
                                            @if($exp->special_needs_experience)
                                                <span class="badge badge-warning">Necesidades Especiales</span>
                                            @endif
                                            <span class="badge badge-secondary">
                                                {{ $exp->start_date->format('M Y') }} - 
                                                {{ $exp->end_date ? $exp->end_date->format('M Y') : 'Presente' }}
                                            </span>
                                        </div>
                                    </button>
                                </h2>
                            </div>

                            <div id="collapse{{ $index }}" 
                                 class="collapse {{ $index == 0 ? 'show' : '' }}" 
                                 aria-labelledby="heading{{ $index }}" 
                                 data-parent="#experiencesAccordion">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="font-weight-bold">Detalles de la Experiencia:</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Tipo:</strong></td>
                                                    <td>{{ $exp->experience_type }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Edades cuidadas:</strong></td>
                                                    <td>{{ $exp->ages_cared }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Duración:</strong></td>
                                                    <td>{{ $exp->duration_months }} meses</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Período:</strong></td>
                                                    <td>
                                                        {{ $exp->start_date->format('d/m/Y') }} - 
                                                        {{ $exp->end_date ? $exp->end_date->format('d/m/Y') : 'Presente' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Cuidó bebés:</strong></td>
                                                    <td>
                                                        @if($exp->cared_for_infants)
                                                            <span class="badge badge-success">Sí</span>
                                                        @else
                                                            <span class="badge badge-secondary">No</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Necesidades especiales:</strong></td>
                                                    <td>
                                                        @if($exp->special_needs_experience)
                                                            <span class="badge badge-success">Sí</span>
                                                        @else
                                                            <span class="badge badge-secondary">No</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h6 class="font-weight-bold">Referencia:</h6>
                                            @if($exp->reference_name)
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>Nombre:</strong></td>
                                                        <td>{{ $exp->reference_name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Teléfono:</strong></td>
                                                        <td>{{ $exp->reference_phone }}</td>
                                                    </tr>
                                                    @if($exp->reference_email)
                                                    <tr>
                                                        <td><strong>Email:</strong></td>
                                                        <td>{{ $exp->reference_email }}</td>
                                                    </tr>
                                                    @endif
                                                </table>
                                                <button class="btn btn-sm btn-primary" 
                                                        onclick="contactReference('{{ $exp->reference_phone }}')">
                                                    <i class="fas fa-phone"></i> Contactar Referencia
                                                </button>
                                            @else
                                                <p class="text-muted">No proporcionó referencia para esta experiencia.</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h6 class="font-weight-bold">Responsabilidades:</h6>
                                            <p>{{ $exp->responsibilities }}</p>
                                            
                                            @if($exp->special_needs_detail)
                                                <h6 class="font-weight-bold">Detalles de Necesidades Especiales:</h6>
                                                <p>{{ $exp->special_needs_detail }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="editExperience({{ $exp->id }})">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="deleteExperience({{ $exp->id }})">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-baby fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay experiencias con niños registradas.</p>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addExperienceModal">
                        <i class="fas fa-plus"></i> Agregar Primera Experiencia
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Análisis de Experiencia -->
    @if($experiences->count() > 0)
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Distribución por Tipo
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Experiencia por Edades
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="agesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Agregar Experiencia -->
<div class="modal fade" id="addExperienceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Agregar Experiencia con Niños
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.au-pair.childcare.store', $user->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Experiencia:</label>
                                <select name="experience_type" class="form-control" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="babysitting">Babysitting</option>
                                    <option value="nanny">Niñera</option>
                                    <option value="daycare">Guardería</option>
                                    <option value="tutoring">Tutoría</option>
                                    <option value="camp_counselor">Consejero de Campamento</option>
                                    <option value="family">Familia (hermanos, primos)</option>
                                    <option value="volunteer">Voluntariado</option>
                                    <option value="other">Otro</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Edades de los Niños:</label>
                                <input type="text" name="ages_cared" class="form-control" 
                                       placeholder="Ej: 0-2, 3-5, 6-10" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Inicio:</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Fin (dejar vacío si continúa):</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Responsabilidades:</label>
                                <textarea name="responsibilities" class="form-control" rows="3" required
                                          placeholder="Describe las actividades y responsabilidades..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                       id="cared_for_infants" name="cared_for_infants" value="1">
                                <label class="custom-control-label" for="cared_for_infants">
                                    Cuidé bebés (menores de 2 años)
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                       id="special_needs_experience" name="special_needs_experience" 
                                       value="1" onchange="toggleSpecialNeeds()">
                                <label class="custom-control-label" for="special_needs_experience">
                                    Experiencia con necesidades especiales
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3" id="special_needs_row" style="display: none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Detalles de Necesidades Especiales:</label>
                                <textarea name="special_needs_detail" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="font-weight-bold">Referencia (Opcional)</h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre de la Referencia:</label>
                                <input type="text" name="reference_name" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono de la Referencia:</label>
                                <input type="tel" name="reference_phone" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Email de la Referencia:</label>
                                <input type="email" name="reference_email" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Experiencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleSpecialNeeds() {
    const checkbox = document.getElementById('special_needs_experience');
    const row = document.getElementById('special_needs_row');
    row.style.display = checkbox.checked ? 'block' : 'none';
}

function contactReference(phone) {
    alert('Contactar al: ' + phone);
    // Aquí podrías abrir un modal con opciones de contacto
}

function editExperience(id) {
    // Implementar edición
    alert('Editar experiencia #' + id);
}

function deleteExperience(id) {
    if (confirm('¿Eliminar esta experiencia?')) {
        // Implementar eliminación
        alert('Eliminar experiencia #' + id);
    }
}

// Gráficos si hay experiencias
@if($experiences->count() > 0)
    // Gráfico de tipos
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    const typeChart = new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($experiences->pluck('experience_type')->unique()->values()) !!},
            datasets: [{
                data: {!! json_encode($experiences->groupBy('experience_type')->map->count()->values()) !!},
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', 
                    '#e74a3b', '#858796', '#5a5c69', '#2e59d9'
                ]
            }]
        },
        options: {
            maintainAspectRatio: false,
            legend: {
                display: true,
                position: 'bottom'
            }
        }
    });
    
    // Gráfico de edades
    const agesCtx = document.getElementById('agesChart').getContext('2d');
    const agesChart = new Chart(agesCtx, {
        type: 'bar',
        data: {
            labels: ['0-2 años', '3-5 años', '6-10 años', '11+ años'],
            datasets: [{
                label: 'Experiencias',
                data: [
                    {{ $experiences->where('cared_for_infants', true)->count() }},
                    {{ $experiences->filter(function($e) { return str_contains($e->ages_cared, '3-5'); })->count() }},
                    {{ $experiences->filter(function($e) { return str_contains($e->ages_cared, '6-10'); })->count() }},
                    {{ $experiences->filter(function($e) { return str_contains($e->ages_cared, '11'); })->count() }}
                ],
                backgroundColor: '#4e73df'
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
@endif
</script>
@endpush
