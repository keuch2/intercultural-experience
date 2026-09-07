<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-id-card text-primary me-2"></i> Datos personales</h5>
        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editPersonalData"><i class="fas fa-edit"></i> Editar</button>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label small text-muted mb-0">Fecha de inscripción</label><p class="mb-0 fw-semibold">{{ $process->enrollment_date?->format('d/m/Y') ?? '-' }}</p></div>
            <div class="col-md-3"><label class="form-label small text-muted mb-0">Temporada</label><p class="mb-0 fw-semibold">{{ $process->season ?? '-' }}</p></div>
            <div class="col-md-3"><label class="form-label small text-muted mb-0">Nacionalidad</label><p class="mb-0 fw-semibold">{{ $user->nationality ?? '-' }}</p></div>
            <div class="col-md-3"><label class="form-label small text-muted mb-0">CI</label><p class="mb-0 fw-semibold">{{ $user->ci_number ?? '-' }}</p></div>
            <div class="col-md-3"><label class="form-label small text-muted mb-0">Fecha de nacimiento</label><p class="mb-0 fw-semibold">{{ $user->birth_date?->format('d/m/Y') ?? '-' }} @if($user->age)({{ $user->age }} años)@endif</p></div>
            <div class="col-md-3"><label class="form-label small text-muted mb-0">Celular</label><p class="mb-0 fw-semibold">{{ $user->phone ?? '-' }}</p></div>
            <div class="col-md-6"><label class="form-label small text-muted mb-0">Domicilio</label><p class="mb-0 fw-semibold">{{ $user->address ?? '-' }} @if($user->city)· {{ $user->city }}@endif</p></div>
            <div class="col-md-4"><label class="form-label small text-muted mb-0">Universidad</label><p class="mb-0 fw-semibold">{{ $user->university ?? '-' }}</p></div>
            <div class="col-md-4"><label class="form-label small text-muted mb-0">Carrera</label><p class="mb-0 fw-semibold">{{ $user->career ?? '-' }}</p></div>
            <div class="col-md-4"><label class="form-label small text-muted mb-0">Año / semestre</label><p class="mb-0 fw-semibold">{{ $user->academic_year ?? '-' }}</p></div>
            <div class="col-md-6"><label class="form-label small text-muted mb-0">Trabajo actual</label><p class="mb-0 fw-semibold">{{ $user->current_job ?? '-' }} @if($user->job_position)· {{ $user->job_position }}@endif</p></div>
            <div class="col-md-6"><label class="form-label small text-muted mb-0">Email</label><p class="mb-0 fw-semibold">{{ $user->email }}</p></div>
        </div>
        <div class="collapse mt-3" id="editPersonalData">
            <form method="POST" action="{{ route('admin.program.participants.update-personal', [$program->slug, $process->id]) }}">@csrf @method('PUT')<input type="hidden" name="redirect_tab" value="{{ $activeTab }}">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label small">Fecha de inscripción</label><input type="date" name="enrollment_date" class="form-control form-control-sm" value="{{ $process->enrollment_date?->format('Y-m-d') }}"></div>
                    <div class="col-md-3"><label class="form-label small">Temporada</label><input type="text" name="season" class="form-control form-control-sm" value="{{ $process->season }}" placeholder="2026"></div>
                    <div class="col-md-6"><label class="form-label small">Nombre y apellido *</label><input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $user->name) }}" required></div>
                    <div class="col-md-3"><label class="form-label small">Nacionalidad</label><input type="text" name="nationality" class="form-control form-control-sm" value="{{ $user->nationality }}"></div>
                    <div class="col-md-3"><label class="form-label small">CI</label><input type="text" name="ci_number" class="form-control form-control-sm" value="{{ $user->ci_number }}"></div>
                    <div class="col-md-3"><label class="form-label small">Celular</label><input type="text" name="phone" class="form-control form-control-sm" value="{{ $user->phone }}"></div>
                    <div class="col-md-3"><label class="form-label small">Estado civil</label><select name="marital_status" class="form-select form-select-sm"><option value="">--</option>@foreach(['single' => 'Soltero/a', 'married' => 'Casado/a', 'divorced' => 'Divorciado/a', 'widowed' => 'Viudo/a'] as $v => $l)<option value="{{ $v }}" {{ $user->marital_status == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label small">Domicilio</label><input type="text" name="address" class="form-control form-control-sm" value="{{ $user->address }}"></div>
                    <div class="col-md-3"><label class="form-label small">Ciudad</label><input type="text" name="city" class="form-control form-control-sm" value="{{ $user->city }}"></div>
                    <div class="col-md-3"><label class="form-label small">País</label><input type="text" name="country" class="form-control form-control-sm" value="{{ $user->country }}"></div>
                    <div class="col-md-4"><label class="form-label small">Universidad</label><input type="text" name="university" class="form-control form-control-sm" value="{{ $user->university }}"></div>
                    <div class="col-md-4"><label class="form-label small">Carrera</label><input type="text" name="career" class="form-control form-control-sm" value="{{ $user->career }}"></div>
                    <div class="col-md-4"><label class="form-label small">Año / semestre</label><input type="text" name="academic_year" class="form-control form-control-sm" value="{{ $user->academic_year }}"></div>
                    <div class="col-md-4"><label class="form-label small">Nivel académico</label><input type="text" name="academic_level" class="form-control form-control-sm" value="{{ $user->academic_level }}"></div>
                    <div class="col-md-4"><label class="form-label small">Trabajo actual</label><input type="text" name="current_job" class="form-control form-control-sm" value="{{ $user->current_job }}"></div>
                    <div class="col-md-4"><label class="form-label small">Cargo</label><input type="text" name="job_position" class="form-control form-control-sm" value="{{ $user->job_position }}"></div>
                </div>
                <div class="mt-3 d-flex gap-2"><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Guardar</button><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#editPersonalData">Cancelar</button></div>
            </form>
        </div>
    </div>
</div>
