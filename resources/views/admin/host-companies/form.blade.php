<div class="form-group">
    <label for="name">Nombre de la Empresa <span class="text-danger">*</span></label>
    <input type="text" 
           class="form-control @error('name') is-invalid @enderror" 
           id="name" 
           name="name" 
           value="{{ old('name', $company->name ?? '') }}" 
           required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="industry">Industria</label>
            <select class="form-control @error('industry') is-invalid @enderror" 
                    id="industry" 
                    name="industry">
                <option value="">Seleccionar...</option>
                <option value="Hospitality" {{ old('industry', $company->industry ?? '') == 'Hospitality' ? 'selected' : '' }}>Hospitalidad</option>
                <option value="Retail" {{ old('industry', $company->industry ?? '') == 'Retail' ? 'selected' : '' }}>Retail</option>
                <option value="Tourism" {{ old('industry', $company->industry ?? '') == 'Tourism' ? 'selected' : '' }}>Turismo</option>
                <option value="Food Service" {{ old('industry', $company->industry ?? '') == 'Food Service' ? 'selected' : '' }}>Servicio de Alimentos</option>
                <option value="Entertainment" {{ old('industry', $company->industry ?? '') == 'Entertainment' ? 'selected' : '' }}>Entretenimiento</option>
            </select>
            @error('industry')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="rating">Rating (0-5)</label>
            <input type="number" 
                   class="form-control @error('rating') is-invalid @enderror" 
                   id="rating" 
                   name="rating" 
                   min="0" 
                   max="5" 
                   step="0.1"
                   value="{{ old('rating', $company->rating ?? '') }}">
            @error('rating')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<hr>
<h6 class="font-weight-bold text-primary mb-3">Ubicación</h6>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="city">Ciudad <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('city') is-invalid @enderror" 
                   id="city" 
                   name="city" 
                   value="{{ old('city', $company->city ?? '') }}" 
                   required>
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="state">Estado <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('state') is-invalid @enderror" 
                   id="state" 
                   name="state" 
                   value="{{ old('state', $company->state ?? '') }}" 
                   required>
            @error('state')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="country">País <span class="text-danger">*</span></label>
    <select class="form-control @error('country') is-invalid @enderror" 
            id="country" 
            name="country" 
            required>
        <option value="">Seleccionar...</option>
        <option value="USA" {{ old('country', $company->country ?? '') == 'USA' ? 'selected' : '' }}>Estados Unidos</option>
        <option value="Canada" {{ old('country', $company->country ?? '') == 'Canada' ? 'selected' : '' }}>Canadá</option>
    </select>
    @error('country')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="address">Dirección</label>
    <textarea class="form-control @error('address') is-invalid @enderror" 
              id="address" 
              name="address" 
              rows="2">{{ old('address', $company->address ?? '') }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<hr>
<h6 class="font-weight-bold text-primary mb-3">Información de Contacto</h6>

<div class="form-group">
    <label for="contact_person">Persona de Contacto</label>
    <input type="text" 
           class="form-control @error('contact_person') is-invalid @enderror" 
           id="contact_person" 
           name="contact_person" 
           value="{{ old('contact_person', $company->contact_person ?? '') }}">
    @error('contact_person')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="contact_email">Email de Contacto</label>
            <input type="email" 
                   class="form-control @error('contact_email') is-invalid @enderror" 
                   id="contact_email" 
                   name="contact_email" 
                   value="{{ old('contact_email', $company->contact_email ?? '') }}">
            @error('contact_email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="contact_phone">Teléfono de Contacto</label>
            <input type="text" 
                   class="form-control @error('contact_phone') is-invalid @enderror" 
                   id="contact_phone" 
                   name="contact_phone" 
                   value="{{ old('contact_phone', $company->contact_phone ?? '') }}">
            @error('contact_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<hr>

<div class="form-group">
    <label for="notes">Notas Internas</label>
    <textarea class="form-control @error('notes') is-invalid @enderror" 
              id="notes" 
              name="notes" 
              rows="3">{{ old('notes', $company->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Información adicional sobre la empresa (uso interno)</small>
</div>

<div class="form-group">
    <div class="custom-control custom-checkbox">
        <input type="checkbox" 
               class="custom-control-input" 
               id="is_active" 
               name="is_active" 
               value="1" 
               {{ old('is_active', $company->is_active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="is_active">
            Empresa Activa
        </label>
    </div>
</div>
