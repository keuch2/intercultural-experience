@extends('layouts.admin')

@section('title', 'Reportes Mensuales')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-calendar-alt"></i> Reporte Mensual Detallado
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Dashboard
                            </a>
                            <a href="{{ route('admin.reports.export', ['type' => 'monthly', 'format' => 'csv', 'year' => $year, 'month' => $month]) }}" class="btn btn-success">
                                <i class="fas fa-download"></i> Exportar CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Seleccionar Período
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.monthly') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="year" class="form-label">Año</label>
                            <select class="form-select" id="year" name="year">
                                @foreach($years as $yearOption)
                                    <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="month" class="form-label">Mes</label>
                            <select class="form-select" id="month" name="month">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Ver Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen del Mes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Resumen de {{ $startDate->format('F Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>₲ {{ number_format($monthStats['total_revenue_pyg'], 0, ',', '.') }}</h4>
                                    <p class="mb-0">Ingresos Totales</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $monthStats['total_participants'] }}</h4>
                                    <p class="mb-0">Participantes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $monthStats['total_programs'] }}</h4>
                                    <p class="mb-0">Programas Activos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $monthStats['applications_count'] }}</h4>
                                    <p class="mb-0">Aplicaciones</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparación con Mes Anterior -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Comparación con Mes Anterior
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6>Mes Actual</h6>
                                <h4 class="text-primary">₲ {{ number_format($monthStats['total_revenue_pyg'], 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6>Mes Anterior</h6>
                                <h4 class="text-secondary">₲ {{ number_format($prevMonthRevenue, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6>Crecimiento</h6>
                                <h4 class="{{ $revenueGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $revenueGrowth >= 0 ? '+' : '' }}{{ number_format($revenueGrowth, 1) }}%
                                    <i class="fas fa-{{ $revenueGrowth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle por Programas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table"></i> Detalle por Programas - {{ $startDate->format('F Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Programa</th>
                                    <th>País</th>
                                    <th>Moneda</th>
                                    <th>Costo Unitario</th>
                                    <th>Participantes</th>
                                    <th>Ingresos (Original)</th>
                                    <th>Ingresos (PYG)</th>
                                    <th>% del Mes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programRevenue as $item)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $item['program']->name }}</strong>
                                            @if($item['program']->location)
                                                <br><small class="text-muted">{{ $item['program']->location }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $item['program']->country }}</span>
                                    </td>
                                    <td>
                                        @if($item['program']->currency)
                                            <strong>{{ $item['program']->currency->code }}</strong>
                                            <br><small class="text-muted">{{ $item['program']->currency->symbol }}</small>
                                        @else
                                            <span class="badge bg-secondary">PYG</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['program']->currency)
                                            <strong>{{ $item['program']->currency->symbol }} {{ number_format($item['program']->cost, 2, ',', '.') }}</strong>
                                        @else
                                            <strong>₲ {{ number_format($item['program']->cost, 0, ',', '.') }}</strong>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success fs-6">{{ $item['participants_count'] }}</span>
                                    </td>
                                    <td>
                                        @if($item['program']->currency)
                                            <strong>{{ $item['program']->currency->symbol }} {{ number_format($item['revenue_original'], 2, ',', '.') }}</strong>
                                        @else
                                            <strong>₲ {{ number_format($item['revenue_original'], 0, ',', '.') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-success">₲ {{ number_format($item['revenue_pyg'], 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        @php
                                            $percentage = $monthStats['total_revenue_pyg'] > 0 ? ($item['revenue_pyg'] / $monthStats['total_revenue_pyg']) * 100 : 0;
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                <div class="progress-bar bg-primary" 
                                                     role="progressbar" 
                                                     style="width: {{ $percentage }}%">
                                                </div>
                                            </div>
                                            <small>{{ number_format($percentage, 1) }}%</small>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>No se registraron ingresos en {{ $startDate->format('F Y') }}.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            
                            @if($programRevenue->count() > 0)
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="5">TOTALES DEL MES</th>
                                    <th>-</th>
                                    <th><strong>₲ {{ number_format($monthStats['total_revenue_pyg'], 0, ',', '.') }}</strong></th>
                                    <th><strong>100%</strong></th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación entre Meses -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        @php
                            $prevDate = $startDate->copy()->subMonth();
                            $nextDate = $startDate->copy()->addMonth();
                            $currentDate = \Carbon\Carbon::now();
                        @endphp
                        
                        <a href="{{ route('admin.reports.monthly', ['year' => $prevDate->year, 'month' => $prevDate->month]) }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-chevron-left"></i> {{ $prevDate->format('F Y') }}
                        </a>
                        
                        <div class="text-center">
                            <h5 class="mb-0">{{ $startDate->format('F Y') }}</h5>
                            <small class="text-muted">
                                Del {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}
                            </small>
                        </div>
                        
                        @if($nextDate->lte($currentDate))
                        <a href="{{ route('admin.reports.monthly', ['year' => $nextDate->year, 'month' => $nextDate->month]) }}" 
                           class="btn btn-outline-secondary">
                            {{ $nextDate->format('F Y') }} <i class="fas fa-chevron-right"></i>
                        </a>
                        @else
                        <span class="btn btn-outline-secondary disabled">
                            {{ $nextDate->format('F Y') }} <i class="fas fa-chevron-right"></i>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.progress {
    background-color: #e9ecef;
}
</style>
@endpush 