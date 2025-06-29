@extends('layouts.admin')

@section('title', 'Reportes por Monedas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-coins"></i> Reportes Financieros por Monedas
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Dashboard
                            </a>
                            <a href="{{ route('admin.reports.export', ['type' => 'currencies', 'format' => 'csv', 'year' => $year]) }}" class="btn btn-success">
                                <i class="fas fa-download"></i> Exportar CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtro de Año -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Filtros
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.currencies') }}" class="row g-3">
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
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>₲ {{ number_format($totalRevenuePyg, 0, ',', '.') }}</h3>
                    <p class="mb-0">Ingresos Totales {{ $year }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ number_format($totalParticipants, 0, ',', '.') }}</h3>
                    <p class="mb-0">Total Participantes</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $currencyRevenue->count() }}</h3>
                    <p class="mb-0">Monedas Activas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table"></i> Detalle por Monedas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Moneda</th>
                                    <th>Tasa de Cambio</th>
                                    <th>Programas</th>
                                    <th>Participantes</th>
                                    <th>Ingresos (Moneda Original)</th>
                                    <th>Ingresos (PYG)</th>
                                    <th>% del Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($currencyRevenue as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <i class="fas fa-coins text-warning"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $item['currency']->name }}</strong>
                                                <br><small class="text-muted">{{ $item['currency']->code }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>₲ {{ number_format($item['currency']->exchange_rate_to_pyg, 0, ',', '.') }}</strong>
                                        <br><small class="text-muted">por {{ $item['currency']->symbol }}1</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info fs-6">{{ $item['programs_count'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success fs-6">{{ $item['participants_count'] }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $item['currency']->symbol }} {{ number_format($item['revenue_original'], 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-success">₲ {{ number_format($item['revenue_pyg'], 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        @php
                                            $percentage = $totalRevenuePyg > 0 ? ($item['revenue_pyg'] / $totalRevenuePyg) * 100 : 0;
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-warning" 
                                                     role="progressbar" 
                                                     style="width: {{ $percentage }}%">
                                                </div>
                                            </div>
                                            <small><strong>{{ number_format($percentage, 1) }}%</strong></small>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>No se encontraron ingresos para el año {{ $year }}.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            
                            @if($currencyRevenue->count() > 0)
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="4">TOTALES</th>
                                    <th>-</th>
                                    <th><strong>₲ {{ number_format($totalRevenuePyg, 0, ',', '.') }}</strong></th>
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
</div>
@endsection 