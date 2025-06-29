@extends('layouts.admin')

@section('title', 'Informe Financiero')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-chart-bar"></i> Informe Financiero {{ $year }}
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.finance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Panel
                            </a>
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-primary">
                                <i class="fas fa-analytics"></i> Dashboard de Reportes
                            </a>
                            <a href="{{ route('admin.finance.report.export', ['year' => $year]) }}" class="btn btn-success">
                                <i class="fas fa-download"></i> Exportar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selector de Año -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt"></i> Seleccionar Período
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.finance.report') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="year" class="form-label">Año</label>
                            <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen Anual -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>₲ {{ number_format($totalRevenuePyg, 0, ',', '.') }}</h3>
                    <p class="mb-0">Total Anual {{ $year }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ number_format($totalParticipants, 0, ',', '.') }}</h3>
                    <p class="mb-0">Participantes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    @php
                        $avgMonthly = $totalRevenuePyg > 0 ? $totalRevenuePyg / 12 : 0;
                    @endphp
                    <h3>₲ {{ number_format($avgMonthly, 0, ',', '.') }}</h3>
                    <p class="mb-0">Promedio Mensual</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    @php
                        $maxMonth = collect($monthlyData)->sortByDesc('total')->first();
                    @endphp
                    <h3>{{ $maxMonth ? $maxMonth['name'] : 'N/A' }}</h3>
                    <p class="mb-0">Mejor Mes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Ingresos Anuales -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Evolución Mensual {{ $year }} (en Guaraníes)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="yearlyRevenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Ingresos por Programa -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie"></i> Ingresos por Programa
                    </h5>
                </div>
                <div class="card-body">
                    @if($programRevenue->count() > 0)
                        <canvas id="programRevenueChart" height="200" class="mb-4"></canvas>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Programa</th>
                                        <th>Moneda</th>
                                        <th>Participantes</th>
                                        <th>Ingresos (PYG)</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($programRevenue as $program)
                                    <tr>
                                        <td>{{ $program['name'] }}</td>
                                        <td><span class="badge bg-info">{{ $program['currency_code'] }}</span></td>
                                        <td class="text-center">{{ $program['participants'] }}</td>
                                        <td><strong>₲ {{ number_format($program['revenue_pyg'], 0, ',', '.') }}</strong></td>
                                        <td>
                                            @php
                                                $percentage = $totalRevenuePyg > 0 ? ($program['revenue_pyg'] / $totalRevenuePyg) * 100 : 0;
                                            @endphp
                                            {{ number_format($percentage, 1) }}%
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay programas con ingresos en {{ $year }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Distribución por Monedas -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-coins"></i> Distribución por Monedas
                    </h5>
                </div>
                <div class="card-body">
                    @if($currencyStats->count() > 0)
                        <canvas id="currencyChart" height="200" class="mb-4"></canvas>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Moneda</th>
                                        <th>Participantes</th>
                                        <th>Ingresos (Original)</th>
                                        <th>Ingresos (PYG)</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currencyStats as $currency)
                                    <tr>
                                        <td>
                                            <strong>{{ $currency['currency']->code }}</strong>
                                            <br><small class="text-muted">{{ $currency['currency']->name }}</small>
                                        </td>
                                        <td class="text-center">{{ $currency['participants'] }}</td>
                                        <td>
                                            <strong>{{ $currency['currency']->symbol }} {{ number_format($currency['revenue_original'], 2, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <strong>₲ {{ number_format($currency['revenue_pyg'], 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $percentage = $totalRevenuePyg > 0 ? ($currency['revenue_pyg'] / $totalRevenuePyg) * 100 : 0;
                                            @endphp
                                            {{ number_format($percentage, 1) }}%
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay datos de monedas para {{ $year }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle Mensual -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table"></i> Detalle Mensual {{ $year }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mes</th>
                                    <th>Ingresos (PYG)</th>
                                    <th>% del Año</th>
                                    <th>Crecimiento vs Mes Anterior</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $prevMonthValue = 0;
                                @endphp
                                @foreach($monthlyData as $month => $data)
                                <tr>
                                    <td><strong>{{ $data['name'] }}</strong></td>
                                    <td>₲ {{ number_format($data['total'], 0, ',', '.') }}</td>
                                    <td>
                                        @php
                                            $percentage = $totalRevenuePyg > 0 ? ($data['total'] / $totalRevenuePyg) * 100 : 0;
                                        @endphp
                                        {{ number_format($percentage, 1) }}%
                                    </td>
                                    <td>
                                        @if($prevMonthValue > 0)
                                            @php
                                                $growth = (($data['total'] - $prevMonthValue) / $prevMonthValue) * 100;
                                            @endphp
                                            <span class="{{ $growth >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                                                <i class="fas fa-{{ $growth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                        @php
                                            $prevMonthValue = $data['total'];
                                        @endphp
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th>TOTAL {{ $year }}</th>
                                    <th>₲ {{ number_format($totalRevenuePyg, 0, ',', '.') }}</th>
                                    <th>100%</th>
                                    <th>-</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de ingresos anuales
    const monthlyData = @json($monthlyData);
    const monthNames = Object.values(monthlyData).map(item => item.name);
    const monthValues = Object.values(monthlyData).map(item => item.total);
    
    const ctx = document.getElementById('yearlyRevenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthNames,
            datasets: [{
                label: 'Ingresos (PYG)',
                data: monthValues,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true,
                borderWidth: 3,
                pointBackgroundColor: 'rgb(75, 192, 192)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₲ ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Ingresos: ₲ ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfico de ingresos por programa
    const programData = @json($programRevenue);
    if (programData.length > 0) {
        const programCtx = document.getElementById('programRevenueChart').getContext('2d');
        new Chart(programCtx, {
            type: 'pie',
            data: {
                labels: programData.map(item => item.name),
                datasets: [{
                    data: programData.map(item => item.revenue_pyg),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', 
                        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ₲ ' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de distribución por monedas
    const currencyData = @json($currencyStats);
    if (currencyData.length > 0) {
        const currencyCtx = document.getElementById('currencyChart').getContext('2d');
        new Chart(currencyCtx, {
            type: 'doughnut',
            data: {
                labels: currencyData.map(item => item.currency.code),
                datasets: [{
                    data: currencyData.map(item => item.revenue_pyg),
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#17a2b8', '#6f42c1', '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ₲ ' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush

