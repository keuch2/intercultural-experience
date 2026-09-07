<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header"><strong>Módulos habilitados</strong> <span class="text-muted small ms-2">Catálogo fijo. Activar un módulo agrega su tab en el admin y su pantalla en la app.</span></div>
            <div class="card-body">
                <form action="{{ route('admin.program-config.modules.update', $program->id) }}" method="POST">@csrf @method('PUT')
                    @php $enabled = $definition->modules(); @endphp
                    @foreach($catalog as $key => $meta)
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="modules[]" value="{{ $key }}" id="mod-{{ $key }}" {{ in_array($key, $enabled) ? 'checked' : '' }} {{ $meta['implemented'] ? '' : 'disabled' }}>
                        <label class="form-check-label" for="mod-{{ $key }}"><i class="fas {{ $meta['icon'] }} me-1 text-muted"></i> {{ $meta['label'] }} <code class="small">{{ $key }}</code>
                            @unless($meta['implemented'])<span class="badge bg-secondary">próximamente</span>@endunless
                        </label>
                    </div>
                    @endforeach
                    <hr>
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="engine_enabled" value="0">
                        <input class="form-check-input" type="checkbox" name="engine_enabled" value="1" id="eng" {{ $program->engine_enabled ? 'checked' : '' }}>
                        <label class="form-check-label" for="eng"><strong>Programa gestionado por el motor</strong> (hub de participantes, API y app genéricos)</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_available_in_app" value="0">
                        <input class="form-check-input" type="checkbox" name="is_available_in_app" value="1" id="app" {{ ($program->getAttributes()['is_available_in_app'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="app"><strong>Disponible para postular desde la app móvil</strong></label>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar módulos</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm border-info"><div class="card-body small text-muted">
            <p class="mb-1"><strong>¿Cómo funciona?</strong></p>
            <ul class="ps-3 mb-0">
                <li><strong>Test de inglés</strong>: registra intentos y nivel CEFR; la regla de nivel mínimo se define en "Reglas".</li>
                <li><strong>Visa J1</strong>: proceso C1–C6 (aplicación, cita, docs IE, resultado, viaje, orientación).</li>
                <li><strong>Pool de Ofertas</strong> + <strong>Job Placement</strong>: ofertas con cupo, selección desde la app y placement con Sponsor/SEVIS/DS-2019.</li>
                <li><strong>Support</strong>: seguimientos, incidentes y evaluación final.</li>
                <li><strong>Recursos</strong>: descargables visibles en la app.</li>
            </ul>
        </div></div>
    </div>
</div>
