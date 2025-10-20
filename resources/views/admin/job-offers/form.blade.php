<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="job_offer_id">ID de Oferta <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('job_offer_id') is-invalid @enderror" 
                   id="job_offer_id" name="job_offer_id" 
                   value="{{ old('job_offer_id', $offer->job_offer_id ?? '') }}" 
                   placeholder="JO-2025-001" required>
            @error('job_offer_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="position">Posición <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('position') is-invalid @enderror" 
                   id="position" name="position" 
                   value="{{ old('position', $offer->position ?? '') }}" required>
            @error('position')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="sponsor_id">Sponsor <span class="text-danger">*</span></label>
            <select class="form-control @error('sponsor_id') is-invalid @enderror" 
                    id="sponsor_id" name="sponsor_id" required>
                <option value="">Seleccionar...</option>
                @foreach($sponsors as $sponsor)
                    <option value="{{ $sponsor->id }}" 
                            {{ old('sponsor_id', $offer->sponsor_id ?? '') == $sponsor->id ? 'selected' : '' }}>
                        {{ $sponsor->name }} ({{ $sponsor->code }})
                    </option>
                @endforeach
            </select>
            @error('sponsor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="host_company_id">Empresa Host <span class="text-danger">*</span></label>
            <select class="form-control @error('host_company_id') is-invalid @enderror" 
                    id="host_company_id" name="host_company_id" required>
                <option value="">Seleccionar...</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" 
                            {{ old('host_company_id', $offer->host_company_id ?? '') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            @error('host_company_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="description">Descripción</label>
    <textarea class="form-control @error('description') is-invalid @enderror" 
              id="description" name="description" rows="3">{{ old('description', $offer->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<hr>
<h6 class="font-weight-bold text-primary mb-3">Ubicación</h6>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="city">Ciudad <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                   id="city" name="city" 
                   value="{{ old('city', $offer->city ?? '') }}" required>
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="state">Estado <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                   id="state" name="state" 
                   value="{{ old('state', $offer->state ?? '') }}" required>
            @error('state')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<hr>
<h6 class="font-weight-bold text-primary mb-3">Salario y Horas</h6>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="salary_min">Salario Mínimo (USD/hr) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control @error('salary_min') is-invalid @enderror" 
                   id="salary_min" name="salary_min" 
                   value="{{ old('salary_min', $offer->salary_min ?? '') }}" required>
            @error('salary_min')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="salary_max">Salario Máximo (USD/hr) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control @error('salary_max') is-invalid @enderror" 
                   id="salary_max" name="salary_max" 
                   value="{{ old('salary_max', $offer->salary_max ?? '') }}" required>
            @error('salary_max')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="hours_per_week">Horas por Semana <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('hours_per_week') is-invalid @enderror" 
                   id="hours_per_week" name="hours_per_week" 
                   value="{{ old('hours_per_week', $offer->hours_per_week ?? 40) }}" required>
            @error('hours_per_week')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<hr>
<h6 class="font-weight-bold text-primary mb-3">Vivienda</h6>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="housing_type">Tipo de Vivienda <span class="text-danger">*</span></label>
            <select class="form-control @error('housing_type') is-invalid @enderror" 
                    id="housing_type" name="housing_type" required>
                <option value="provided" {{ old('housing_type', $offer->housing_type ?? '') == 'provided' ? 'selected' : '' }}>Provista</option>
                <option value="assisted" {{ old('housing_type', $offer->housing_type ?? '') == 'assisted' ? 'selected' : '' }}>Asistida</option>
                <option value="not_provided" {{ old('housing_type', $offer->housing_type ?? '') == 'not_provided' ? 'selected' : '' }}>No Provista</option>
            </select>
            @error('housing_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="housing_cost">Costo de Vivienda (USD/mes)</label>
            <input type="number" step="0.01" class="form-control @error('housing_cost') is-invalid @enderror" 
                   id="housing_cost" name="housing_cost" 
                   value="{{ old('housing_cost', $offer->housing_cost ?? '') }}">
            @error('housing_cost')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<hr>
<h6 class="font-weight-bold text-primary mb-3">Cupos y Fechas</h6>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="total_slots">Cupos Totales <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('total_slots') is-invalid @enderror" 
                   id="total_slots" name="total_slots" 
                   value="{{ old('total_slots', $offer->total_slots ?? '') }}" required>
            @error('total_slots')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="available_slots">Cupos Disponibles <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('available_slots') is-invalid @enderror" 
                   id="available_slots" name="available_slots" 
                   value="{{ old('available_slots', $offer->available_slots ?? '') }}" required>
            @error('available_slots')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="start_date">Fecha Inicio <span class="text-danger">*</span></label>
            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                   id="start_date" name="start_date" 
                   value="{{ old('start_date', isset($offer->start_date) ? \Carbon\Carbon::parse($offer->start_date)->format('Y-m-d') : '') }}" required>
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="end_date">Fecha Fin <span class="text-danger">*</span></label>
            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                   id="end_date" name="end_date" 
                   value="{{ old('end_date', isset($offer->end_date) ? \Carbon\Carbon::parse($offer->end_date)->format('Y-m-d') : '') }}" required>
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<hr>
<h6 class="font-weight-bold text-primary mb-3">Requisitos</h6>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="required_english_level">Nivel de Inglés <span class="text-danger">*</span></label>
            <select class="form-control @error('required_english_level') is-invalid @enderror" 
                    id="required_english_level" name="required_english_level" required>
                <option value="A2" {{ old('required_english_level', $offer->required_english_level ?? '') == 'A2' ? 'selected' : '' }}>A2</option>
                <option value="B1" {{ old('required_english_level', $offer->required_english_level ?? '') == 'B1' ? 'selected' : '' }}>B1</option>
                <option value="B1+" {{ old('required_english_level', $offer->required_english_level ?? '') == 'B1+' ? 'selected' : '' }}>B1+</option>
                <option value="B2" {{ old('required_english_level', $offer->required_english_level ?? '') == 'B2' ? 'selected' : '' }}>B2</option>
                <option value="C1" {{ old('required_english_level', $offer->required_english_level ?? '') == 'C1' ? 'selected' : '' }}>C1</option>
                <option value="C2" {{ old('required_english_level', $offer->required_english_level ?? '') == 'C2' ? 'selected' : '' }}>C2</option>
            </select>
            @error('required_english_level')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="required_gender">Género Requerido <span class="text-danger">*</span></label>
            <select class="form-control @error('required_gender') is-invalid @enderror" 
                    id="required_gender" name="required_gender" required>
                <option value="any" {{ old('required_gender', $offer->required_gender ?? '') == 'any' ? 'selected' : '' }}>Cualquiera</option>
                <option value="male" {{ old('required_gender', $offer->required_gender ?? '') == 'male' ? 'selected' : '' }}>Masculino</option>
                <option value="female" {{ old('required_gender', $offer->required_gender ?? '') == 'female' ? 'selected' : '' }}>Femenino</option>
            </select>
            @error('required_gender')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="status">Estado <span class="text-danger">*</span></label>
            <select class="form-control @error('status') is-invalid @enderror" 
                    id="status" name="status" required>
                <option value="available" {{ old('status', $offer->status ?? 'available') == 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="full" {{ old('status', $offer->status ?? '') == 'full' ? 'selected' : '' }}>Lleno</option>
                <option value="cancelled" {{ old('status', $offer->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="requirements">Requisitos Adicionales</label>
    <textarea class="form-control @error('requirements') is-invalid @enderror" 
              id="requirements" name="requirements" rows="3">{{ old('requirements', $offer->requirements ?? '') }}</textarea>
    @error('requirements')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="benefits">Beneficios</label>
    <textarea class="form-control @error('benefits') is-invalid @enderror" 
              id="benefits" name="benefits" rows="3">{{ old('benefits', $offer->benefits ?? '') }}</textarea>
    @error('benefits')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
