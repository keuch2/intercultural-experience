@php $ob = $program->onboarding ?? []; $steps = $ob['steps'] ?? []; @endphp
<div class="card shadow-sm" style="max-width: 900px">
    <div class="card-header"><strong>Onboarding en la app</strong> <span class="text-muted small ms-2">Textos que ve el participante al postularse desde la app.</span></div>
    <div class="card-body">
        <form action="{{ route('admin.program-config.onboarding.update', $program->id) }}" method="POST">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Título</label><input name="title" class="form-control" value="{{ $ob['title'] ?? '' }}" placeholder="Postulá a {{ $program->name }}"></div>
            <div class="mb-3"><label class="form-label">Introducción</label><textarea name="intro" class="form-control" rows="3" placeholder="Descripción breve del programa y qué implica postularse.">{{ $ob['intro'] ?? '' }}</textarea></div>
            <label class="form-label">Pasos (hasta 6)</label>
            @for($i = 0; $i < max(3, count($steps)); $i++)
            <div class="row g-2 mb-2">
                <div class="col-md-4"><input name="steps[{{ $i }}][title]" class="form-control form-control-sm" placeholder="Título paso {{ $i + 1 }}" value="{{ $steps[$i]['title'] ?? '' }}"></div>
                <div class="col-md-8"><input name="steps[{{ $i }}][body]" class="form-control form-control-sm" placeholder="Descripción" value="{{ $steps[$i]['body'] ?? '' }}"></div>
            </div>
            @endfor
            <div class="mb-3 mt-3"><label class="form-label">Texto de términos y condiciones</label><textarea name="terms_text" class="form-control" rows="3">{{ $ob['terms_text'] ?? '' }}</textarea></div>
            <div class="form-check form-switch mb-3"><input type="hidden" name="requires_adult" value="0"><input class="form-check-input" type="checkbox" name="requires_adult" value="1" id="ra" {{ !empty($ob['requires_adult']) ? 'checked' : '' }}><label class="form-check-label" for="ra">Requiere declarar mayoría de edad</label></div>
            <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar onboarding</button>
        </form>
    </div>
</div>
