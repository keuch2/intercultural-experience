@extends('layouts.admin')

@section('title', 'Job Fairs')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Job Fairs</h1>
        <div>
            <a href="{{ route('admin.teachers.job-fair.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nuevo Job Fair
            </a>
            <a href="{{ route('admin.teachers.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.teachers.job-fairs') }}" class="row">
                <div class="col-md-3 mb-3">
                    <label>Estado</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                        <option value="registration_open" {{ request('status') == 'registration_open' ? 'selected' : '' }}>Registro Abierto</option>
                        <option value="registration_closed" {{ request('status') == 'registration_closed' ? 'selected' : '' }}>Registro Cerrado</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completado</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Tipo</label>
                    <select name="type" class="form-control">
                        <option value="">Todos</option>
                        <option value="virtual" {{ request('type') == 'virtual' ? 'selected' : '' }}>Virtual</option>
                        <option value="presencial" {{ request('type') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                        <option value="hybrid" {{ request('type') == 'hybrid' ? 'selected' : '' }}>Híbrido</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Próximos</label>
                    <select name="upcoming" class="form-control">
                        <option value="">Todos</option>
                        <option value="1" {{ request('upcoming') == '1' ? 'selected' : '' }}>Solo Próximos</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Nombre, ciudad..." value="{{ request('search') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.teachers.job-fairs') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Job Fairs Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Lista de Job Fairs ({{ $jobFairs->total() }} registros)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Evento</th>
                            <th>Fecha/Hora</th>
                            <th>Tipo</th>
                            <th>Ubicación</th>
                            <th>Registro</th>
                            <th>Participantes</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobFairs as $jobFair)
                        <tr>
                            <td>{{ $jobFair->id }}</td>
                            <td>
                                <strong>{{ $jobFair->event_name }}</strong>
                                @if($jobFair->description)
                                    <br>
                                    <small class="text-muted">{{ Str::limit($jobFair->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <i class="fas fa-calendar"></i> {{ $jobFair->event_date->format('d/m/Y') }}
                                <br>
                                <small>
                                    <i class="fas fa-clock"></i> 
                                    {{ \Carbon\Carbon::parse($jobFair->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($jobFair->end_time)->format('H:i') }}
                                </small>
                            </td>
                            <td>
                                @if($jobFair->event_type == 'virtual')
                                    <span class="badge badge-info">
                                        <i class="fas fa-video"></i> Virtual
                                    </span>
                                    @if($jobFair->virtual_platform)
                                        <br><small>{{ $jobFair->virtual_platform }}</small>
                                    @endif
                                @elseif($jobFair->event_type == 'presencial')
                                    <span class="badge badge-primary">
                                        <i class="fas fa-users"></i> Presencial
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        <i class="fas fa-sync"></i> Híbrido
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($jobFair->venue_name)
                                    {{ $jobFair->venue_name }}
                                    <br>
                                @endif
                                @if($jobFair->city)
                                    <small>
                                        <i class="fas fa-map-marker-alt"></i> 
                                        {{ $jobFair->city }}, {{ $jobFair->state }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <small>
                                    Abre: {{ $jobFair->registration_opens->format('d/m/Y') }}
                                    <br>
                                    Cierra: {{ $jobFair->registration_closes->format('d/m/Y') }}
                                </small>
                                @if($jobFair->requires_payment)
                                    <br>
                                    <span class="badge badge-warning">
                                        ${{ number_format($jobFair->registration_fee, 2) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div>
                                    <strong>{{ $jobFair->registered_participants }}</strong>/{{ $jobFair->max_participants ?: '∞' }}
                                    <br>
                                    <small>Profesores</small>
                                </div>
                                <div class="mt-2">
                                    <strong>{{ $jobFair->registered_schools }}</strong>/{{ $jobFair->max_schools ?: '∞' }}
                                    <br>
                                    <small>Escuelas</small>
                                </div>
                            </td>
                            <td>
                                @if($jobFair->status == 'draft')
                                    <span class="badge badge-secondary">Borrador</span>
                                @elseif($jobFair->status == 'published')
                                    <span class="badge badge-info">Publicado</span>
                                @elseif($jobFair->status == 'registration_open')
                                    <span class="badge badge-success">Registro Abierto</span>
                                @elseif($jobFair->status == 'registration_closed')
                                    <span class="badge badge-warning">Registro Cerrado</span>
                                @elseif($jobFair->status == 'in_progress')
                                    <span class="badge badge-primary">En Progreso</span>
                                @elseif($jobFair->status == 'completed')
                                    <span class="badge badge-dark">Completado</span>
                                    @if($jobFair->successful_placements > 0)
                                        <br>
                                        <small class="text-success">
                                            {{ $jobFair->successful_placements }} colocaciones
                                        </small>
                                    @endif
                                @else
                                    <span class="badge badge-danger">Cancelado</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.teachers.job-fair.show', $jobFair->id) }}" 
                                   class="btn btn-sm btn-info" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($jobFair->status == 'published')
                                    <form action="{{ route('admin.teachers.job-fair.open', $jobFair->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" 
                                                title="Abrir Registro">
                                            <i class="fas fa-door-open"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                @if($jobFair->status != 'cancelled' && $jobFair->status != 'completed')
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-toggle="modal" 
                                            data-target="#cancelModal{{ $jobFair->id }}"
                                            title="Cancelar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Cancel Modal -->
                        @if($jobFair->status != 'cancelled' && $jobFair->status != 'completed')
                        <div class="modal fade" id="cancelModal{{ $jobFair->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.teachers.job-fair.cancel', $jobFair->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Cancelar Job Fair</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Está seguro que desea cancelar el evento "{{ $jobFair->event_name }}"?</p>
                                            <p class="text-danger">Esta acción notificará a todos los participantes registrados.</p>
                                            
                                            <div class="form-group">
                                                <label>Razón de Cancelación <span class="text-danger">*</span></label>
                                                <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-danger">Cancelar Evento</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No se encontraron job fairs</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $jobFairs->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas Generales</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 class="text-primary">{{ $jobFairs->where('status', 'registration_open')->count() }}</h4>
                            <p>Con Registro Abierto</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success">{{ $jobFairs->where('status', 'completed')->count() }}</h4>
                            <p>Completados</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info">{{ $jobFairs->sum('registered_participants') }}</h4>
                            <p>Total Participantes</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning">{{ $jobFairs->sum('successful_placements') }}</h4>
                            <p>Total Colocaciones</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
