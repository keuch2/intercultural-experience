@extends('layouts.admin')

@section('title', 'Dashboard de Reportes')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-chart-line"></i> Dashboard de Reportes Financieros
                        </h3>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'programs', 'format' => 'csv']) }}">Programas CSV</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'currencies', 'format' => 'csv']) }}">Monedas CSV</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'monthly', 'format' => 'csv']) }}">Mensual CSV</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">₲ {{ number_format($stats['total_revenue_pyg'], 0, ',', '.') }}</h4>
                            <p class="mb-0">Ingresos Totales {{ $currentYear }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total_participants'], 0, ',', '.') }}</h4>
                            <p class="mb-0">Participantes Confirmados</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $stats['active_programs_with_revenue'] }}</h4>
                            <p class="mb-0">Programas con Ingresos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">₲ {{ number_format($stats['avg_revenue_per_participant'], 0, ',', '.') }}</h4>
                            <p class="mb-0">Promedio por Participante</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calculator fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <!-- Ingresos Mensuales -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Ingresos Mensuales {{ $currentYear }} (en Guaraníes)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyRevenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Distribución por Monedas -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-coins"></i> Ingresos por Moneda
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="currencyChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Reportes Detallados -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> Reportes por Programas
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Análisis detallado de ingresos por programa, incluyendo conversiones de moneda y participantes.</p>
                    <a href="{{ route('admin.reports.programs') }}" class="btn btn-primary">
                        <i class="fas fa-chart-bar"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-coins"></i> Reportes por Moneda
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Análisis de ingresos agrupados por moneda con conversiones automáticas a Guaraníes.</p>
                    <a href="{{ route('admin.reports.currencies') }}" class="btn btn-success">
                        <i class="fas fa-money-bill-wave"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt"></i> Reportes Mensuales
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Análisis mensual detallado con comparaciones y tendencias de crecimiento.</p>
                    <a href="{{ route('admin.reports.monthly') }}" class="btn btn-info">
                        <i class="fas fa-chart-pie"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Programas por Ingresos -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-trophy"></i> Top Programas por Ingresos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Programa</th>
                                    <th>Participantes</th>
                                    <th>Ingresos (PYG)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $currentYearValue = $currentYear ?? date('Y');
                                    $topPrograms = App\Models\Program::with(['currency', 'applications' => function($q) use ($currentYearValue) {
                                        $q->where('status', 'approved')->whereYear('created_at', $currentYearValue);
                                    }])->get()->map(function($program) {
                                        $participants = $program->applications->count();
                                        $revenue = $participants * $program->cost;
                                        $revenuePyg = $program->currency ? $program->currency->convertToPyg($revenue) : $revenue;
                                        return [
                                            'program' => $program,
                                            'participants' => $participants,
                                            'revenue_pyg' => $revenuePyg
                                        ];
                                    })->sortByDesc('revenue_pyg')->take(5);
                                @endphp
                                
                                @forelse($topPrograms as $item)
                                    @if($item['participants'] > 0)
                                    <tr>
                                        <td>
                                            <strong>{{ $item['program']->name }}</strong>
                                            <br><small class="text-muted">{{ $item['program']->country }}</small>
                                        </td>
                                        <td>{{ $item['participants'] }}</td>
                                        <td>₲ {{ number_format($item['revenue_pyg'], 0, ',', '.') }}</td>
                                    </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No hay datos disponibles</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie"></i> Distribución por Categoría
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="200"></canvas>
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
    // Gráfico de ingresos mensuales
    const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
    const monthlyData = @json($monthlyData);
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.month_name),
            datasets: [{
                label: 'Ingresos (PYG)',
                data: monthlyData.map(item => item.revenue_pyg),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
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

    // Gráfico de distribución por monedas
    const currencyCtx = document.getElementById('currencyChart').getContext('2d');
    const currencyData = @json($currencyStats);
    
    new Chart(currencyCtx, {
        type: 'doughnut',
        data: {
            labels: currencyData.map(item => item.currency.code),
            datasets: [{
                data: currencyData.map(item => item.revenue_pyg),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label;
                            const value = context.parsed;
                            return label + ': ₲ ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfico de categorías
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryData = @json($programStats);
    
    new Chart(categoryCtx, {
        type: 'pie',
        data: {
            labels: categoryData.map(item => {
                const categories = {
                    'academic': 'Académico',
                    'volunteer': 'Voluntariado',
                    'internship': 'Prácticas',
                    'language': 'Idiomas',
                    'cultural': 'Cultural',
                    'research': 'Investigación'
                };
                return categories[item.category] || item.category;
            }),
            datasets: [{
                data: categoryData.map(item => item.count),
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#17a2b8',
                    '#6f42c1',
                    '#fd7e14'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush 