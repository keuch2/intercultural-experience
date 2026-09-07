@extends('layouts.admin')
@section('title', ($offer ? 'Editar oferta' : 'Nueva oferta') . ' — ' . $program->name)
@section('content')
<div class="container-fluid" style="max-width: 860px">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ $offer ? 'Editar oferta: ' . $offer->employer_name : 'Nueva oferta laboral' }}</h3>
            <a href="{{ $offer ? route('admin.program.job-pool.show', [$program->slug, $offer->id]) : route('admin.program.job-pool.index', $program->slug) }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
        <div class="card-body">
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            <p class="text-muted small"><i class="fas fa-info-circle me-1"></i> Solo se cargan estos datos; todo el detalle del puesto (salario, funciones, housing, fechas, beneficios, requisitos) va dentro del <strong>PDF descargable</strong>.</p>
            <form method="POST" action="{{ $offer ? route('admin.program.job-pool.update', [$program->slug, $offer->id]) : route('admin.program.job-pool.store', $program->slug) }}" enctype="multipart/form-data">
                @csrf @if($offer) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-12"><label class="form-label">Nombre del empleador *</label><input type="text" name="employer_name" class="form-control @error('employer_name') is-invalid @enderror" value="{{ old('employer_name', $offer?->employer_name) }}" required></div>
                    <div class="col-md-5"><label class="form-label">Estado *</label><input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $offer?->state) }}" placeholder="Florida" required></div>
                    <div class="col-md-5"><label class="form-label">Ciudad *</label><input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $offer?->city) }}" placeholder="Orlando" required></div>
                    <div class="col-md-2"><label class="form-label">Posiciones *</label><input type="number" name="positions_total" min="1" max="500" class="form-control @error('positions_total') is-invalid @enderror" value="{{ old('positions_total', $offer?->positions_total ?? 1) }}" required>
                        @if($offer)<div class="form-text">Asignadas: {{ $offer->positions_taken }}</div>@endif</div>
                    <div class="col-md-12"><label class="form-label">PDF de la oferta {{ $offer ? '(reemplazar)' : '*' }}</label><input type="file" name="pdf" class="form-control @error('pdf') is-invalid @enderror" accept=".pdf" {{ $offer ? '' : 'required' }}>
                        @if($offer?->hasPdf())<div class="form-text">Actual: <a href="{{ route('admin.program.job-pool.pdf', [$program->slug, $offer->id]) }}">{{ $offer->pdf_original_filename }}</a></div>@endif</div>
                    <div class="col-md-12"><label class="form-label">Notas internas</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $offer?->notes) }}</textarea></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ $offer ? 'Guardar cambios' : 'Publicar oferta' }}</button>
                    @unless($offer)<span class="text-muted small align-self-center">Al publicar, los participantes habilitados reciben una notificación.</span>@endunless
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
