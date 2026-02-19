@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Informes Au Pair</h2>
        <p class="text-muted mb-0">Reportes y planillas con filtros avanzados</p>
    </div>
    <button class="btn btn-outline-primary" disabled title="Próximamente">
        <i class="fas fa-file-export me-1"></i> Exportar
    </button>
</div>

{{-- Filtros --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fas fa-filter me-1"></i> Filtros</h6>
    </div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Fecha de inscripción (desde)</label>
                <input type="date" class="form-control form-control-sm" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Fecha de inscripción (hasta)</label>
                <input type="date" class="form-control form-control-sm" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Proceso / Etapa</label>
                <select class="form-select form-select-sm" disabled>
                    <option>Todos</option>
                    <option>Admisión</option>
                    <option>Aplicación</option>
                    <option>Match / Visa</option>
                    <option>Support</option>
                    <option>Completado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Nivel de escolaridad</label>
                <select class="form-select form-select-sm" disabled>
                    <option>Todos</option>
                    <option>Secundaria</option>
                    <option>Universitario</option>
                    <option>Graduado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Documentos presentados</label>
                <select class="form-select form-select-sm" disabled>
                    <option>Todos</option>
                    <option>Completos</option>
                    <option>Incompletos</option>
                    <option>Con rechazos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Edad (desde)</label>
                <input type="number" class="form-control form-control-sm" placeholder="18" disabled>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Edad (hasta)</label>
                <input type="number" class="form-control form-control-sm" placeholder="26" disabled>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Pagos</label>
                <select class="form-select form-select-sm" disabled>
                    <option>Todos</option>
                    <option>Al día</option>
                    <option>1er pago</option>
                    <option>Pendiente</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Nivel de inglés</label>
                <select class="form-select form-select-sm" disabled>
                    <option>Todos</option>
                    <option>A1</option><option>A2</option>
                    <option>B1</option><option>B2</option>
                    <option>C1</option><option>C2</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">País de domicilio</label>
                <select class="form-select form-select-sm" disabled>
                    <option>Todos</option>
                    <option>Paraguay</option>
                    <option>Argentina</option>
                    <option>Uruguay</option>
                    <option>Bolivia</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-primary me-2 w-100" disabled>
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Resultados --}}
<div class="card shadow-sm">
    <div class="card-body text-center py-5">
        <i class="fas fa-chart-bar fa-3x text-muted mb-3 d-block opacity-25"></i>
        <h5 class="text-muted">Informes disponibles próximamente</h5>
        <p class="text-muted small mb-0">Los reportes con filtros avanzados y exportación se habilitarán en la Fase 5 del prototipo.</p>
    </div>
</div>
@endsection
