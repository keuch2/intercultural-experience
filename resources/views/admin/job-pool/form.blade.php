@extends('layouts.admin')
@section('title', ($offer ? 'Editar oferta' : 'Nueva oferta') . ' — ' . $program->name)
@section('content')
<div class="container-fluid" style="max-width: 860px">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ $offer ? 'Editar oferta: ' . $offer->display_name : 'Nueva oferta laboral' }}</h3>
            <a href="{{ $offer ? route('admin.program.job-pool.show', [$program->slug, $offer->id]) : route('admin.program.job-pool.index', $program->slug) }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
        <div class="card-body">
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            <p class="text-muted small"><i class="fas fa-info-circle me-1"></i> Cada oferta es un <strong>puesto de trabajo</strong>: el participante ve primero el puesto y sus requisitos; el empleador es el contexto. El detalle extenso (salario, funciones, housing, fechas, beneficios) va en el <strong>PDF descargable</strong>.</p>
            <form method="POST" action="{{ $offer ? route('admin.program.job-pool.update', [$program->slug, $offer->id]) : route('admin.program.job-pool.store', $program->slug) }}" enctype="multipart/form-data">
                @csrf @if($offer) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-12"><label class="form-label">Puesto laboral *</label><input type="text" name="job_title" class="form-control @error('job_title') is-invalid @enderror" value="{{ old('job_title', $offer?->job_title) }}" placeholder="Lifeguard, Housekeeper, Ride Operator…" maxlength="150" required>
                        <div class="form-text">Es el título de la oferta en la app y en el admin.</div></div>
                    <div class="col-md-12"><label class="form-label">Requisitos del puesto</label><textarea name="requirements" class="form-control @error('requirements') is-invalid @enderror" rows="3" maxlength="2000" placeholder="Inglés intermedio (B1+), mayor de 18 años, experiencia en atención al cliente, disponibilidad de junio a septiembre…">{{ old('requirements', $offer?->requirements) }}</textarea>
                        <div class="form-text">Se muestran en la tarjeta de la oferta en la app, antes de descargar el PDF.</div></div>
                    <div class="col-md-7"><label class="form-label">Empleador *</label><input type="text" name="employer_name" class="form-control @error('employer_name') is-invalid @enderror" value="{{ old('employer_name', $offer?->employer_name) }}" required></div>
                    <div class="col-md-5"><label class="form-label">Sponsor</label><select name="sponsor_id" class="form-select @error('sponsor_id') is-invalid @enderror"><option value="">-- Sin sponsor --</option>@foreach($sponsors as $sp)<option value="{{ $sp->id }}" {{ (string) old('sponsor_id', $offer?->sponsor_id) === (string) $sp->id ? 'selected' : '' }}>{{ $sp->name }} ({{ $sp->code }}){{ $sp->is_active ? '' : ' — inactivo' }}</option>@endforeach</select>
                        <div class="form-text">Opcional. El Job Placement del participante lo hereda al seleccionar la oferta.</div></div>
                    <div class="col-md-5"><label class="form-label">Estado *</label><input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $offer?->state) }}" placeholder="Florida" required></div>
                    <div class="col-md-5"><label class="form-label">Ciudad *</label><input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $offer?->city) }}" placeholder="Orlando" required></div>
                    <div class="col-md-2"><label class="form-label">Posiciones *</label><input type="number" name="positions_total" min="1" max="500" class="form-control @error('positions_total') is-invalid @enderror" value="{{ old('positions_total', $offer?->positions_total ?? 1) }}" required>
                        @if($offer)<div class="form-text">Asignadas: {{ $offer->positions_taken }}</div>@endif</div>
                    <div class="col-md-4"><label class="form-label">Fecha límite para postular *</label><input type="date" name="application_deadline" class="form-control @error('application_deadline') is-invalid @enderror" value="{{ old('application_deadline', $offer?->application_deadline?->format('Y-m-d')) }}" @unless($offer) min="{{ now()->format('Y-m-d') }}" @endunless required>
                        <div class="form-text">Se muestra en la app mientras la oferta siga abierta; vencida, deja de mostrarse.</div></div>
                    <div class="col-md-12"><label class="form-label">PDF de la oferta {{ $offer ? '(reemplazar)' : '*' }}</label><input type="file" name="pdf" class="form-control @error('pdf') is-invalid @enderror" accept=".pdf" {{ $offer ? '' : 'required' }}>
                        @if($offer?->hasPdf())<div class="form-text">Actual: <a href="{{ route('admin.program.job-pool.pdf', [$program->slug, $offer->id]) }}">{{ $offer->pdf_original_filename }}</a></div>@endif</div>
                    <div class="col-md-12"><label class="form-label">Imagen / flyer de la oferta {{ $offer?->hasImage() ? '(reemplazar)' : '' }}</label><input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG, PNG o WebP de hasta 5 MB. Se muestra en la tarjeta de la oferta en la app.</div>
                        @if($offer?->hasImage())<div class="mt-2"><img src="{{ $offer->image_url }}" alt="Flyer actual" style="max-height: 160px; border-radius: 6px" class="border"><div class="form-text">Actual: {{ $offer->image_original_filename }}</div></div>@endif</div>
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
