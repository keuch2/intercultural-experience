@extends('layouts.admin')

@section('title', 'Panel Financiero')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-chart-line"></i> Panel Financiero {{ $currentYear }}
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.finance.report') }}" class="btn btn-primary">
                                <i class="fas fa-chart-bar"></i> Reportes Detallados
                            </a>
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-success">
                                <i class="fas fa-analytics"></i> Dashboard de Reportes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">₲ {{ number_format($totalRevenuePyg, 0, ',', '.') }}</h4>
                            <p class="mb-0">Ingresos Totales {{ $currentYear }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">₲ {{ number_format($pendingRevenuePyg, 0, ',', '.') }}</h4>
                            <p class="mb-0">Ingresos Pendientes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($totalParticipants, 0, ',', '.') }}</h4>
                            <p class="mb-0">Participantes Confirmados</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $activeProgramsWithRevenue }}</h4>
                            <p class="mb-0">Programas con Ingresos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-globe fa-2x"></i>
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
                        <i class="fas fa-coins"></i> Distribución por Monedas
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="currencyChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de Datos -->
    <div class="row">
        <!-- Top Programas por Ingresos -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-trophy"></i> Top Programas por Ingresos ({{ $currentYear }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Programa</th>
                                    <th>Moneda</th>
                                    <th>Participantes</th>
                                    <th>Ingresos (Original)</th>
                                    <th>Ingresos (PYG)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programRevenue as $program)
                                <tr>
                                    <td>
                                        <strong>{{ $program['name'] }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $program['currency_code'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success fs-6">{{ $program['participants'] }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $program['currency_symbol'] }} {{ number_format($program['revenue_original'], 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-success">₲ {{ number_format($program['revenue_pyg'], 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No hay programas con ingresos en {{ $currentYear }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones Rápidas y Resumen por Monedas -->
        <div class="col-lg-4">
            <!-- Acciones Rápidas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.finance.payments') }}" class="btn btn-primary">
                            <i class="fas fa-list"></i> Ver Todos los Pagos
                        </a>
                        <a href="{{ route('admin.finance.payments.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Registrar Pago Manual
                        </a>
                        <a href="{{ route('admin.finance.transactions') }}" class="btn btn-info">
                            <i class="fas fa-exchange-alt"></i> Transacciones Financieras
                        </a>
                        <a href="{{ route('admin.finance.transactions.create') }}" class="btn btn-warning">
                            <i class="fas fa-plus-circle"></i> Agregar Ingreso/Egreso
                        </a>
                        <a href="{{ route('admin.reports.programs') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Reportes por Programas
                        </a>
                        <a href="{{ route('admin.currencies.index') }}" class="btn btn-warning">
                            <i class="fas fa-coins"></i> Gestionar Monedas
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Resumen por Monedas -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave"></i> Resumen por Monedas
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($currencyDistribution as $currency)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded">
                        <div>
                            <strong>{{ $currency['currency']->code }}</strong>
                            <br><small class="text-muted">{{ $currency['participants'] }} participantes</small>
                        </div>
                        <div class="text-end">
                            <div><strong>₲ {{ number_format($currency['revenue_pyg'], 0, ',', '.') }}</strong></div>
                            <small class="text-muted">
                                {{ $currency['currency']->symbol }} {{ number_format($currency['revenue_original'], 2, ',', '.') }}
                            </small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No hay datos de monedas</p>
                    </div>
                    @endforelse
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
    const monthlyData = @json($monthlyRevenue);
    const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthNames,
            datasets: [{
                label: 'Ingresos (PYG)',
                data: monthlyData,
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

    // Gráfico de distribución por monedas
    const currencyCtx = document.getElementById('currencyChart').getContext('2d');
    const currencyData = @json($currencyDistribution);
    
    if (currencyData.length > 0) {
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
                maintainAspectRatio: false,
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
    } else {
        // Mostrar mensaje si no hay datos
        currencyCtx.font = "16px Arial";
        currencyCtx.textAlign = "center";
        currencyCtx.fillText("No hay datos disponibles", currencyCtx.canvas.width/2, currencyCtx.canvas.height/2);
    }
});
</script>
@endpush

