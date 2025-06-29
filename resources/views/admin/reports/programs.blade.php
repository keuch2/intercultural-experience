@extends('layouts.admin')

@section('title', 'Reportes por Programas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-chart-bar"></i> Reportes Financieros por Programas
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Dashboard
                            </a>
                            <a href="{{ route('admin.reports.export', ['type' => 'programs', 'format' => 'csv'] + $request->all()) }}" class="btn btn-success">
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
                        <i class="fas fa-filter"></i> Filtros
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.programs') }}" class="row g-3">
                        <div class="col-md-2">
                            <label for="year" class="form-label">Año</label>
                            <select class="form-select" id="year" name="year">
                                <option value="">Todos</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $request->input('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="category" class="form-label">Categoría</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Todas</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ $request->input('category') == $category ? 'selected' : '' }}>
                                        {{ ucfirst($category) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="country" class="form-label">País</label>
                            <select class="form-select" id="country" name="country">
                                <option value="">Todos</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country }}" {{ $request->input('country') == $country ? 'selected' : '' }}>
                                        {{ $country }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="currency_id" class="form-label">Moneda</label>
                            <select class="form-select" id="currency_id" name="currency_id">
                                <option value="">Todas</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ $request->input('currency_id') == $currency->id ? 'selected' : '' }}>
                                        {{ $currency->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.reports.programs') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
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
                    <p class="mb-0">Ingresos Totales</p>
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
                    <h3>{{ $programsWithRevenue->count() }}</h3>
                    <p class="mb-0">Programas con Ingresos</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Programas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table"></i> Detalle por Programas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Programa</th>
                                    <th>País/Ciudad</th>
                                    <th>Categoría</th>
                                    <th>Costo Original</th>
                                    <th>Participantes</th>
                                    <th>Ingresos (Moneda Original)</th>
                                    <th>Ingresos (PYG)</th>
                                    <th>% del Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programsWithRevenue as $item)
                                    @if($item['participants_count'] > 0)
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
                                            @switch($item['program']->category)
                                                @case('academic')
                                                    <span class="badge bg-primary">Académico</span>
                                                    @break
                                                @case('volunteer')
                                                    <span class="badge bg-success">Voluntariado</span>
                                                    @break
                                                @case('internship')
                                                    <span class="badge bg-warning">Prácticas</span>
                                                    @break
                                                @case('language')
                                                    <span class="badge bg-info">Idiomas</span>
                                                    @break
                                                @case('cultural')
                                                    <span class="badge bg-purple">Cultural</span>
                                                    @break
                                                @case('research')
                                                    <span class="badge bg-dark">Investigación</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($item['program']->category) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <strong>{{ $item['currency_symbol'] }} {{ number_format($item['program']->cost, 2, ',', '.') }}</strong>
                                            <br><small class="text-muted">{{ $item['currency_code'] }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark fs-6">{{ $item['participants_count'] }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $item['currency_symbol'] }} {{ number_format($item['revenue_original'], 2, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <strong class="text-success">₲ {{ number_format($item['revenue_pyg'], 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $percentage = $totalRevenuePyg > 0 ? ($item['revenue_pyg'] / $totalRevenuePyg) * 100 : 0;
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-success" 
                                                         role="progressbar" 
                                                         style="width: {{ $percentage }}%">
                                                    </div>
                                                </div>
                                                <small>{{ number_format($percentage, 1) }}%</small>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No se encontraron programas con ingresos para los filtros seleccionados.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            
                            @if($programsWithRevenue->count() > 0)
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="5">TOTALES</th>
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

@push('styles')
<style>
.badge.bg-purple {
    background-color: #6f42c1 !important;
}
.progress {
    background-color: #e9ecef;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change (optional)
    const filterSelects = document.querySelectorAll('select[name="year"], select[name="category"], select[name="country"], select[name="currency_id"]');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Uncomment to auto-submit on change
            // this.form.submit();
        });
    });
});
</script>
@endpush 