@extends('layouts.admin')

@section('title', 'Detalle de Perfil Au Pair')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-circle"></i> Perfil Au Pair: {{ $profile->user->name }}
        </h1>
        <div>
            @if($profile->profile_status == 'pending')
                <button class="btn btn-success" onclick="approveProfile()">
                    <i class="fas fa-check"></i> Aprobar Perfil
                </button>
            @endif
            <a href="{{ route('admin.au-pair.profiles') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Estado y Completitud -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Estado del Perfil</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @switch($profile->profile_status)
                                    @case('draft')
                                        <span class="badge badge-secondary">Borrador</span>
                                        @break
                                    @case('pending')
                                        <span class="badge badge-warning">Pendiente</span>
                                        @break
                                    @case('active')
                                        <span class="badge badge-success">Activo</span>
                                        @break
                                    @case('matched')
                                        <span class="badge badge-info">Con Match</span>
                                        @break
                                    @default
                                        <span class="badge badge-dark">{{ ucfirst($profile->profile_status) }}</span>
                                @endswitch
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flag fa-2x text-gray-300"></i>
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
                                Completitud</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $completeness }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                Vistas del Perfil</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $profile->profile_views }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
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
                                Disponible desde</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $profile->available_from ? $profile->available_from->format('M Y') : 'No definido' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda -->
        <div class="col-lg-4">
            <!-- Información Personal -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-user"></i> Información Personal
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Foto Principal -->
                    @if($profile->main_photo)
                        <div class="text-center mb-3">
                            <img src="{{ asset('storage/' . $profile->main_photo) }}" 
                                 class="img-fluid rounded-circle" style="max-width: 200px;">
                        </div>
                    @endif
                    
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Nombre:</strong></td>
                            <td>{{ $profile->user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Edad:</strong></td>
                            <td>{{ $profile->user->age }} años</td>
                        </tr>
                        <tr>
                            <td><strong>Género:</strong></td>
                            <td>{{ ucfirst($profile->user->gender ?? 'No especificado') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Nacionalidad:</strong></td>
                            <td>{{ $profile->user->nationality }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $profile->user->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Teléfono:</strong></td>
                            <td>{{ $profile->user->phone }}</td>
                        </tr>
                        <tr>
                            <td><strong>Ciudad:</strong></td>
                            <td>{{ $profile->user->city }}, {{ $profile->user->country }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Habilidades y Certificaciones -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-certificate"></i> Habilidades
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        @if($profile->user->has_drivers_license)
                            <span class="badge badge-success">
                                <i class="fas fa-car"></i> Licencia de Conducir
                            </span>
                        @else
                            <span class="badge badge-secondary">
                                <i class="fas fa-car"></i> Sin Licencia
                            </span>
                        @endif
                    </div>
                    
                    <div class="mb-2">
                        @if($profile->user->can_swim)
                            <span class="badge badge-info">
                                <i class="fas fa-swimmer"></i> Sabe Nadar
                            </span>
                        @else
                            <span class="badge badge-secondary">
                                <i class="fas fa-swimmer"></i> No Nada
                            </span>
                        @endif
                    </div>
                    
                    <div class="mb-2">
                        @if($profile->user->first_aid_certified)
                            <span class="badge badge-danger">
                                <i class="fas fa-first-aid"></i> Primeros Auxilios
                            </span>
                        @endif
                    </div>
                    
                    <div class="mb-2">
                        @if($profile->user->cpr_certified)
                            <span class="badge badge-danger">
                                <i class="fas fa-heartbeat"></i> CPR Certificado
                            </span>
                        @endif
                    </div>
                    
                    <div class="mb-2">
                        @if(!$profile->user->smoker)
                            <span class="badge badge-success">
                                <i class="fas fa-smoking-ban"></i> No Fumador
                            </span>
                        @else
                            <span class="badge badge-warning">
                                <i class="fas fa-smoking"></i> Fumador
                            </span>
                        @endif
                    </div>
                    
                    @if($profile->user->driving_years > 0)
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-car"></i> {{ $profile->user->driving_years }} años conduciendo
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Columna Central -->
        <div class="col-lg-8">
            <!-- Carta a la Familia -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-envelope-open-text"></i> Carta "Dear Family"
                    </h6>
                </div>
                <div class="card-body">
                    @if($profile->dear_family_letter)
                        <p style="white-space: pre-wrap;">{{ $profile->dear_family_letter }}</p>
                    @else
                        <p class="text-muted">No ha escrito carta de presentación aún.</p>
                    @endif
                </div>
            </div>

            <!-- Experiencia con Niños -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-baby"></i> Experiencia con Niños
                    </h6>
                    <a href="{{ route('admin.au-pair.childcare', $profile->user_id) }}" 
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-list"></i> Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    @if($profile->user->childcareExperiences->count() > 0)
                        @php
                            $totalMonths = $profile->user->childcareExperiences->sum('duration_months');
                            $hasInfantExp = $profile->user->childcareExperiences->where('cared_for_infants', true)->count() > 0;
                            $hasSpecialNeeds = $profile->user->childcareExperiences->where('special_needs_experience', true)->count() > 0;
                        @endphp
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-primary">{{ $totalMonths }}</h4>
                                    <small class="text-muted">Meses de Experiencia</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-info">{{ $profile->user->childcareExperiences->count() }}</h4>
                                    <small class="text-muted">Experiencias Registradas</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    @if($hasInfantExp)
                                        <span class="badge badge-success">Con Bebés</span>
                                    @endif
                                    @if($hasSpecialNeeds)
                                        <span class="badge badge-warning">Necesidades Especiales</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Últimas 2 experiencias -->
                        @foreach($profile->user->childcareExperiences->take(2) as $exp)
                            <div class="border-left-primary pl-3 mb-3">
                                <h6 class="font-weight-bold">{{ $exp->experience_type }}</h6>
                                <p class="mb-1">
                                    <small>
                                        <i class="fas fa-calendar"></i> {{ $exp->duration_months }} meses |
                                        <i class="fas fa-child"></i> Edades: {{ $exp->ages_cared }}
                                    </small>
                                </p>
                                <p class="text-muted small">{{ Str::limit($exp->responsibilities, 100) }}</p>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No ha registrado experiencia con niños.</p>
                    @endif
                </div>
            </div>

            <!-- Referencias -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-check"></i> Referencias ({{ $profile->user->references->count() }})
                    </h6>
                    <a href="{{ route('admin.au-pair.references', $profile->user_id) }}" 
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-list"></i> Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    @if($profile->user->references->count() > 0)
                        <div class="row">
                            @foreach($profile->user->references->take(3) as $ref)
                                <div class="col-md-4 mb-3">
                                    <div class="card {{ $ref->verified ? 'border-success' : 'border-warning' }}">
                                        <div class="card-body p-2">
                                            <h6 class="card-title mb-1">{{ $ref->name }}</h6>
                                            <p class="card-text small">
                                                <span class="badge badge-secondary">{{ ucfirst($ref->reference_type) }}</span><br>
                                                <i class="fas fa-user-tie"></i> {{ $ref->relationship }}<br>
                                                <i class="fas fa-phone"></i> {{ $ref->phone }}
                                            </p>
                                            @if($ref->verified)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Verificada
                                                </span>
                                            @else
                                                <button class="btn btn-sm btn-warning" 
                                                        onclick="verifyReference({{ $ref->id }})">
                                                    <i class="fas fa-check"></i> Verificar
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($profile->user->references->count() < 3)
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Se requieren mínimo 3 referencias. Faltan {{ 3 - $profile->user->references->count() }}.
                            </div>
                        @endif
                    @else
                        <p class="text-muted">No ha proporcionado referencias.</p>
                    @endif
                </div>
            </div>

            <!-- Preferencias de Familia -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-home"></i> Preferencias de Familia
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Edades Preferidas:</strong> {{ $profile->preferred_ages ?? 'Sin preferencia' }}</p>
                            <p><strong>Máximo de Niños:</strong> {{ $profile->max_children }}</p>
                            <p><strong>Compromiso:</strong> {{ $profile->commitment_months }} meses</p>
                        </div>
                        <div class="col-md-6">
                            @if($profile->ideal_family_description)
                                <p><strong>Familia Ideal:</strong></p>
                                <p class="small">{{ $profile->ideal_family_description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Galería de Fotos -->
            @if($profile->photos && count($profile->photos) > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-images"></i> Galería de Fotos ({{ count($profile->photos) }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($profile->photos as $photo)
                            <div class="col-md-3 mb-3">
                                <img src="{{ asset('storage/' . $photo) }}" 
                                     class="img-fluid rounded" 
                                     style="cursor: pointer;"
                                     onclick="showPhoto('{{ asset('storage/' . $photo) }}')">
                            </div>
                        @endforeach
                    </div>
                    
                    @if(count($profile->photos) < 6)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Se requieren mínimo 6 fotos. Faltan {{ 6 - count($profile->photos) }}.
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Video de Presentación -->
            @if($profile->video_presentation)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-video"></i> Video de Presentación
                    </h6>
                </div>
                <div class="card-body">
                    <video width="100%" controls>
                        <source src="{{ asset('storage/' . $profile->video_presentation) }}" type="video/mp4">
                        Tu navegador no soporta el elemento de video.
                    </video>
                    <p class="mt-2">
                        <small class="text-muted">
                            Duración: {{ gmdate("i:s", $profile->video_duration) }}
                        </small>
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Checklist de Requisitos -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-gradient-info text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-clipboard-check"></i> Checklist de Requisitos
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check1" 
                               {{ $profile->user->age >= 18 && $profile->user->age <= 26 ? 'checked' : '' }} disabled>
                        <label class="custom-control-label" for="check1">
                            Edad 18-26 años
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check2" 
                               {{ $profile->photos && count($profile->photos) >= 6 ? 'checked' : '' }} disabled>
                        <label class="custom-control-label" for="check2">
                            Mínimo 6 fotos
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check3" 
                               {{ $profile->video_presentation ? 'checked' : '' }} disabled>
                        <label class="custom-control-label" for="check3">
                            Video de presentación
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check4" 
                               {{ $profile->user->references->count() >= 3 ? 'checked' : '' }} disabled>
                        <label class="custom-control-label" for="check4">
                            Mínimo 3 referencias
                        </label>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check5" 
                               {{ $profile->user->childcareExperiences->count() > 0 ? 'checked' : '' }} disabled>
                        <label class="custom-control-label" for="check5">
                            Experiencia con niños
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check6" 
                               {{ $profile->dear_family_letter ? 'checked' : '' }} disabled>
                        <label class="custom-control-label" for="check6">
                            Carta Dear Family
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check7" 
                               {{ $profile->user->healthDeclaration ? 'checked' : '' }} disabled>
                        <label class="custom-control-label" for="check7">
                            Declaración de salud
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check8" 
                               {{ $profile->user->emergencyContacts->count() >= 2 ? 'checked' : '' }} disabled>
                        <label class="custom-control-label" for="check8">
                            Contactos de emergencia
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para foto ampliada -->
<div class="modal fade" id="photoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalPhoto" src="" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approveProfile() {
    if (confirm('¿Aprobar este perfil de Au Pair?')) {
        $.post('{{ route("admin.au-pair.profile.approve", $profile->id) }}', {
            _token: '{{ csrf_token() }}'
        }).done(function(response) {
            alert('Perfil aprobado exitosamente');
            location.reload();
        });
    }
}

function verifyReference(refId) {
    if (confirm('¿Marcar esta referencia como verificada?')) {
        $.post('{{ route("admin.au-pair.references.verify", "") }}/' + refId, {
            _token: '{{ csrf_token() }}'
        }).done(function(response) {
            alert('Referencia verificada');
            location.reload();
        });
    }
}

function showPhoto(url) {
    $('#modalPhoto').attr('src', url);
    $('#photoModal').modal('show');
}
</script>
@endpush
