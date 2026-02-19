@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Perfiles Au Pair</h2>
        <p class="text-muted mb-0">Gestión de participantes del programa Au Pair</p>
    </div>
</div>

{{-- Estadísticas rápidas --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-dashboard card-primary shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">Total Perfiles</div>
                        <div class="h3 mb-0 fw-bold">{{ $stats['total'] }}</div>
                    </div>
                    <div class="text-primary opacity-50"><i class="fas fa-users fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-dashboard card-warning shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">En Admisión</div>
                        <div class="h3 mb-0 fw-bold">{{ $stats['admission'] }}</div>
                    </div>
                    <div class="text-warning opacity-50"><i class="fas fa-user-edit fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-dashboard card-info shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">Activos</div>
                        <div class="h3 mb-0 fw-bold">{{ $stats['active'] }}</div>
                    </div>
                    <div class="text-info opacity-50"><i class="fas fa-user-check fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-dashboard card-success shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">Matched</div>
                        <div class="h3 mb-0 fw-bold">{{ $stats['matched'] }}</div>
                    </div>
                    <div class="text-success opacity-50"><i class="fas fa-heart fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.aupair.profiles.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Buscar</label>
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Nombre, email o CI..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Etapa</label>
                <select name="stage" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="admission" {{ request('stage') == 'admission' ? 'selected' : '' }}>Admisión</option>
                    <option value="application" {{ request('stage') == 'application' ? 'selected' : '' }}>Aplicación</option>
                    <option value="match_visa" {{ request('stage') == 'match_visa' ? 'selected' : '' }}>Match / Visa</option>
                    <option value="support" {{ request('stage') == 'support' ? 'selected' : '' }}>Support</option>
                    <option value="completed" {{ request('stage') == 'completed' ? 'selected' : '' }}>Completado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Pagos</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Al día</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Nivel Inglés</label>
                <select name="english_level" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach(['A1','A2','B1','B2','C1','C2'] as $level)
                    <option value="{{ $level }}" {{ request('english_level') == $level ? 'selected' : '' }}>{{ $level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">País</label>
                <select name="country" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($countries as $country)
                    <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Tabla de perfiles --}}
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Participante</th>
                    <th>Fecha Inscripción</th>
                    <th>Etapa</th>
                    <th class="text-center">Docs</th>
                    <th class="text-center">Pagos</th>
                    <th class="text-center">Inglés</th>
                    <th>Última Actualización</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($profiles as $user)
                @php
                    $app = $user->applications->first();
                    $prof = $user->auPairProfile;
                    $proc = $user->auPairProcess;
                    $bestEnglish = $user->englishEvaluations->sortByDesc('score')->first();
                    $verifiedPayments = $proc ? (($proc->payment_1_verified ? 1 : 0) + ($proc->payment_2_verified ? 1 : 0)) : ($app ? $app->payments->where('status', 'verified')->count() : 0);
                    $procDocs = $proc ? $proc->documents : collect();
                    $totalDocs = $procDocs->count();
                    $approvedDocs = $procDocs->where('status', 'approved')->count();
                    $pendingDocs = $procDocs->where('status', 'pending')->count();
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="{{ $user->avatar_url }}" alt="" class="rounded-circle me-2" width="36" height="36">
                            <div>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <small>{{ $app ? $app->created_at->format('d/m/Y') : '-' }}</small>
                    </td>
                    <td>
                        @if($proc)
                            @switch($proc->current_stage)
                                @case('admission')
                                    <span class="badge bg-warning text-dark">Admisión</span>
                                    @break
                                @case('application')
                                    <span class="badge bg-info">Aplicación</span>
                                    @break
                                @case('match_visa')
                                    <span class="badge bg-primary">Match / Visa</span>
                                    @break
                                @case('support')
                                    <span class="badge bg-success">Support</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-dark">Completado</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">Cancelado</span>
                                    @break
                            @endswitch
                        @else
                            <span class="badge bg-light text-dark">Sin proceso</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($totalDocs > 0)
                            <span class="badge {{ $pendingDocs > 0 ? 'bg-warning text-dark' : 'bg-success' }}">
                                {{ $approvedDocs }}/{{ $totalDocs }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($verifiedPayments >= 2)
                            <span class="badge bg-success"><i class="fas fa-check"></i> 2/2</span>
                        @elseif($verifiedPayments == 1)
                            <span class="badge bg-warning text-dark">1/2</span>
                        @else
                            <span class="badge bg-danger">0/2</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($bestEnglish)
                            <span class="badge {{ in_array($bestEnglish->cefr_level, ['B1','B2','C1','C2']) ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $bestEnglish->cefr_level }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                    </td>
                    <td>
                        <a href="{{ route('admin.aupair.profiles.show', $user->id) }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
                        No se encontraron perfiles Au Pair.
                        @if(request()->hasAny(['search', 'stage', 'payment_status', 'english_level', 'country']))
                            <br><a href="{{ route('admin.aupair.profiles.index') }}">Limpiar filtros</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($profiles->hasPages())
    <div class="card-footer">
        {{ $profiles->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
