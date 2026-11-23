@extends('layouts.admin')

@section('title', $campaign->exists ? 'Editar aviso programado' : 'Nuevo aviso especial')

@section('content')
@php
    $audience = old('audience_mode') ? [
        'mode' => old('audience_mode'), 'program_ids' => old('program_ids', []), 'application_statuses' => old('application_statuses', []),
        'stage' => old('stage'), 'season' => old('season'), 'user_ids' => old('user_ids', []),
    ] : ($campaign->audience ?? ['mode' => 'all']);
    $mode = $audience['mode'] ?? 'all';
    $programIds = array_map('intval', $audience['program_ids'] ?? []);
    $statuses = $audience['application_statuses'] ?? [];
    $sendMode = old('send_mode', $campaign->scheduled_at ? 'scheduled' : 'now');
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $campaign->exists ? 'Editar aviso programado' : 'Nuevo aviso especial' }}</h1>
        <a href="{{ route('admin.notification-campaigns.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" id="campaignForm"
          action="{{ $campaign->exists ? route('admin.notification-campaigns.update', $campaign) : route('admin.notification-campaigns.store') }}">
        @csrf
        @if($campaign->exists) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Mensaje</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Título *</label>
                            <input type="text" name="title" id="cTitle" class="form-control" maxlength="120" required value="{{ old('title', $campaign->title) }}" placeholder="Ej.: Abrimos la temporada 2027">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Mensaje *</label>
                            <textarea name="message" id="cMessage" class="form-control" rows="4" maxlength="1000" required placeholder="Texto que ve el participante en Avisos">{{ old('message', $campaign->message) }}</textarea>
                        </div>
                        <small class="text-muted"><span id="cCount">0</span>/1000 · En la notificación push se ven los primeros ~150 caracteres; el texto completo queda en Avisos.</small>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Al tocar el aviso</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <select name="link_type" id="linkType" class="form-select">
                                    @foreach(\App\Models\NotificationCampaign::LINK_TYPES as $value => $label)
                                        <option value="{{ $value }}" @selected(old('link_type', $campaign->link_type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" data-show-link="screen">
                                <select name="link_target" class="form-select">
                                    <option value="">Elegí una sección…</option>
                                    @foreach(\App\Support\NotificationCatalog::LINK_TARGETS as $value => $label)
                                        <option value="{{ $value }}" @selected(old('link_target', $campaign->link_target) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Se abre la sección del programa de cada participante.</small>
                            </div>
                            <div class="col-md-6" data-show-link="program">
                                <select name="link_program_id" class="form-select">
                                    <option value="">Elegí un programa…</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" @selected((int) old('link_program_id', $campaign->link_program_id) === $program->id)>{{ $program->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" data-show-link="url">
                                <input type="url" name="link_url" class="form-control" maxlength="500" placeholder="https://ie.org.py/…" value="{{ old('link_url', $campaign->link_url) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Destinatarios</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            @foreach(['all' => 'Todos los participantes', 'segment' => 'Filtrar por programa, estado o etapa', 'users' => 'Personas específicas'] as $value => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="audience_mode" id="mode_{{ $value }}" value="{{ $value }}" @checked($mode === $value)>
                                    <label class="form-check-label" for="mode_{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div data-show-mode="segment" class="border rounded p-3 bg-light">
                            <label class="form-label fw-semibold">Programas</label>
                            <div class="row mb-3">
                                @foreach($programs as $program)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input js-program" type="checkbox" name="program_ids[]" value="{{ $program->id }}" id="prog_{{ $program->id }}" @checked(in_array($program->id, $programIds, true))>
                                            <label class="form-check-label" for="prog_{{ $program->id }}">{{ $program->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="col-12"><small class="text-muted">Sin marcar = todos los programas.</small></div>
                            </div>

                            <label class="form-label fw-semibold">Estado de la postulación</label>
                            <div class="mb-3">
                                @foreach(\App\Services\Notifications\CampaignAudience::APPLICATION_STATUSES as $value => $label)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="application_statuses[]" value="{{ $value }}" id="st_{{ $value }}" @checked(in_array($value, $statuses, true))>
                                        <label class="form-check-label" for="st_{{ $value }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                                <div><small class="text-muted">Sin marcar = cualquier estado (las no aprobadas nunca se incluyen).</small></div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Etapa del proceso</label>
                                    <select name="stage" id="stageSelect" class="form-select" data-selected="{{ $audience['stage'] ?? '' }}">
                                        <option value="">Cualquier etapa</option>
                                    </select>
                                    <small class="text-muted" id="stageHint">Elegí un solo programa para filtrar por etapa.</small>
                                </div>
                                @if(count($seasons))
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Temporada</label>
                                        <select name="season" class="form-select">
                                            <option value="">Cualquier temporada</option>
                                            @foreach($seasons as $season)
                                                <option value="{{ $season }}" @selected(($audience['season'] ?? null) === $season)>{{ $season }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div data-show-mode="users" class="border rounded p-3 bg-light">
                            <label class="form-label fw-semibold">Buscar participantes</label>
                            <input type="search" id="userSearch" class="form-control" placeholder="Nombre, email o cédula" autocomplete="off">
                            <div id="userResults" class="list-group mt-1"></div>
                            <div id="userChips" class="d-flex flex-wrap gap-2 mt-3">
                                @foreach($selectedUsers as $u)
                                    <span class="badge bg-primary d-inline-flex align-items-center gap-2 py-2 px-3" data-user="{{ $u->id }}">
                                        {{ $u->name }}
                                        <input type="hidden" name="user_ids[]" value="{{ $u->id }}">
                                        <button type="button" class="btn-close btn-close-white" style="font-size: .55rem" aria-label="Quitar"></button>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Vista previa</h5></div>
                    <div class="card-body" style="background: #1f2937;">
                        <div class="rounded-3 p-3" style="background: rgba(255,255,255,.92);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <img src="{{ asset('images/logo-ie.png') }}" onerror="this.style.display='none'" alt="" style="width: 18px; height: 18px; border-radius: 4px;">
                                <small class="text-muted text-uppercase" style="font-size: .65rem; letter-spacing: .04em;">IE Xperience · ahora</small>
                            </div>
                            <div class="fw-bold small" id="pvTitle">Título del aviso</div>
                            <div class="small" id="pvMessage" style="display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;"></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Envío</h5></div>
                    <div class="card-body">
                        <label class="form-label">Canal</label>
                        @foreach(\App\Models\NotificationCampaign::CHANNELS as $value => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="ch_{{ $value }}" value="{{ $value }}" @checked(old('channel', $campaign->channel) === $value)>
                                <label class="form-check-label" for="ch_{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach

                        <div class="form-check form-switch mt-3" data-show-channel="push">
                            <input type="hidden" name="is_important" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_important" id="isImportant" value="1" @checked(old('is_important', $campaign->is_important))>
                            <label class="form-check-label" for="isImportant"><strong>Importante</strong></label>
                            <div><small class="text-muted">Envía la push aunque el participante haya silenciado "Novedades y promociones". Usalo solo para avisos que no pueden perderse.</small></div>
                        </div>

                        <hr>
                        <label class="form-label">Cuándo</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="send_mode" id="send_now" value="now" @checked($sendMode === 'now')>
                            <label class="form-check-label" for="send_now">Enviar ahora</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="send_mode" id="send_scheduled" value="scheduled" @checked($sendMode === 'scheduled')>
                            <label class="form-check-label" for="send_scheduled">Programar</label>
                        </div>
                        <div class="mt-2" data-show-send="scheduled">
                            <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\TH:i')) }}">
                            <small class="text-muted">Hora de Paraguay. Se envía dentro del minuto programado.</small>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="display-6 fw-bold" id="recipientCount">–</span>
                            <span class="text-muted">destinatario(s)</span>
                        </div>
                        <div class="small text-muted" id="recipientDetail">Calculando…</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100" id="submitBtn"><i class="fas fa-paper-plane me-1"></i> <span>Enviar aviso</span></button>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const $ = (s) => document.querySelector(s);
    const $$ = (s) => document.querySelectorAll(s);
    const stages = @json($stagesByProgram);
    const csrf = document.querySelector('input[name="_token"]').value;
    const form = $('#campaignForm');
    const val = (name) => form.querySelector(`[name="${name}"]:checked`)?.value;

    function toggle() {
        const link = $('#linkType').value;
        $$('[data-show-link]').forEach((el) => {
            const show = el.dataset.showLink === link;
            el.style.display = show ? '' : 'none';
            el.querySelectorAll('input, select').forEach((i) => i.disabled = !show);
        });
        $$('[data-show-mode]').forEach((el) => el.style.display = el.dataset.showMode === val('audience_mode') ? '' : 'none');
        $$('[data-show-channel]').forEach((el) => el.style.display = el.dataset.showChannel === val('channel') ? '' : 'none');
        $$('[data-show-send]').forEach((el) => el.style.display = el.dataset.showSend === val('send_mode') ? '' : 'none');
        $('#submitBtn span').textContent = val('send_mode') === 'scheduled' ? 'Programar aviso' : 'Enviar aviso';
    }

    function refreshStages() {
        const checked = [...$$('.js-program:checked')].map((c) => c.value);
        const select = $('#stageSelect');
        const current = select.value || select.dataset.selected;
        select.dataset.selected = '';
        select.innerHTML = '<option value="">Cualquier etapa</option>';
        const list = checked.length === 1 ? (stages[checked[0]] || {}) : {};
        Object.entries(list).forEach(([key, label]) => {
            const o = new Option(label, key, false, key === current);
            select.add(o);
        });
        select.disabled = checked.length !== 1;
        $('#stageHint').style.display = checked.length === 1 ? 'none' : '';
    }

    function preview() {
        $('#pvTitle').textContent = $('#cTitle').value || 'Título del aviso';
        $('#pvMessage').textContent = $('#cMessage').value;
        $('#cCount').textContent = $('#cMessage').value.length;
    }

    let timer;
    function countRecipients() {
        clearTimeout(timer);
        timer = setTimeout(async () => {
            const data = new FormData(form);
            data.delete('_method');
            try {
                const res = await fetch(@json(route('admin.notification-campaigns.preview')), {
                    method: 'POST', body: data, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                });
                const json = await res.json();
                $('#recipientCount').textContent = json.count;
                const names = json.sample.length ? ' Ej.: ' + json.sample.join(', ') + (json.count > json.sample.length ? '…' : '') : '';
                $('#recipientDetail').textContent = `${json.with_device} con la app instalada para recibir push.${names}`;
            } catch (e) {
                $('#recipientDetail').textContent = 'No se pudo calcular.';
            }
        }, 300);
    }

    // Búsqueda de participantes
    const results = $('#userResults');
    let searchTimer;
    $('#userSearch').addEventListener('input', (e) => {
        clearTimeout(searchTimer);
        const q = e.target.value.trim();
        if (q.length < 2) { results.innerHTML = ''; return; }
        searchTimer = setTimeout(async () => {
            const res = await fetch(@json(route('admin.notification-campaigns.users')) + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            results.innerHTML = '';
            json.data.forEach((u) => {
                const a = document.createElement('button');
                a.type = 'button';
                a.className = 'list-group-item list-group-item-action small';
                a.textContent = `${u.name} — ${u.email}`;
                a.addEventListener('click', () => addUser(u));
                results.appendChild(a);
            });
            if (!json.data.length) results.innerHTML = '<div class="list-group-item small text-muted">Sin resultados</div>';
        }, 250);
    });

    function addUser(u) {
        if (document.querySelector(`[data-user="${u.id}"]`)) return;
        const chip = document.createElement('span');
        chip.className = 'badge bg-primary d-inline-flex align-items-center gap-2 py-2 px-3';
        chip.dataset.user = u.id;
        chip.textContent = u.name;
        chip.insertAdjacentHTML('beforeend', `<input type="hidden" name="user_ids[]" value="${u.id}"><button type="button" class="btn-close btn-close-white" style="font-size:.55rem" aria-label="Quitar"></button>`);
        $('#userChips').appendChild(chip);
        results.innerHTML = '';
        $('#userSearch').value = '';
        countRecipients();
    }
    $('#userChips').addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-close')) { e.target.closest('[data-user]').remove(); countRecipients(); }
    });

    form.addEventListener('change', (e) => {
        toggle();
        if (e.target.classList.contains('js-program')) refreshStages();
        if (!['cTitle', 'cMessage'].includes(e.target.id)) countRecipients();
    });
    ['#cTitle', '#cMessage'].forEach((s) => $(s).addEventListener('input', preview));
    form.addEventListener('submit', (e) => {
        if (val('send_mode') === 'now' && !confirm(`¿Enviar el aviso ahora a ${$('#recipientCount').textContent} participante(s)?`)) e.preventDefault();
    });

    toggle();
    refreshStages();
    preview();
    countRecipients();
})();
</script>
@endsection
