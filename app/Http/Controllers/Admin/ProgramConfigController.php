<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Program;
use App\Models\ProgramChecklistItem;
use App\Models\ProgramDocument;
use App\Models\ProgramDocumentRequirement;
use App\Models\ProgramPaymentGate;
use App\Models\ProgramProcess;
use App\Models\ProgramResource;
use App\Models\ProgramStage;
use App\Services\ProgramEngine\ModuleCatalog;
use App\Services\ProgramEngine\ProgramDefinition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Configuración del motor por programa: etapas, documentos, checklist, gates de
 * pago, módulos, reglas, recursos y onboarding. Todo editable desde el admin sin
 * código (objetivo 2).
 */
class ProgramConfigController extends Controller
{
    public const TABS = [
        'stages' => 'Etapas',
        'documents' => 'Documentos',
        'checklist' => 'Checklist',
        'gates' => 'Pagos (gates)',
        'modules' => 'Módulos',
        'rules' => 'Reglas',
        'resources' => 'Recursos',
        'onboarding' => 'Onboarding',
    ];

    private const KEY_RULE = 'regex:/^[a-z][a-z0-9_]{1,49}$/';

    public function show(Request $request, Program $program)
    {
        $tab = $request->query('tab', 'stages');
        if (! array_key_exists($tab, self::TABS)) {
            $tab = 'stages';
        }

        ProgramDefinition::forget($program);
        $definition = ProgramDefinition::for($program);

        return view('admin.program-config.show', [
            'program' => $program,
            'definition' => $definition,
            'activeTab' => $tab,
            'tabs' => self::TABS,
            'stages' => $program->stages()->get(),
            'requirements' => $program->documentRequirements()->get(),
            'checklist' => $program->checklistItems()->get(),
            'gates' => $program->paymentGates()->get(),
            'resources' => $program->resources()->get(),
            'currencies' => Currency::all(),
            'catalog' => ModuleCatalog::all(),
            'processesCount' => $program->processes()->count(),
        ]);
    }

    // ── Etapas ─────────────────────────────────────────────────────────
    public function storeStage(Request $request, Program $program)
    {
        $data = $this->validateStage($request, $program);
        $data['program_id'] = $program->id;
        $data['sort_order'] = $data['sort_order'] ?? ((int) $program->stages()->max('sort_order') + 1);
        ProgramStage::create($data);

        return $this->back($program, 'stages', 'Etapa creada.');
    }

    public function updateStage(Request $request, Program $program, ProgramStage $stage)
    {
        $this->assertOwned($program, $stage);
        $stage->update($this->validateStage($request, $program, $stage));

        return $this->back($program, 'stages', 'Etapa actualizada.');
    }

    public function destroyStage(Program $program, ProgramStage $stage)
    {
        $this->assertOwned($program, $stage);

        if ($program->processes()->where('current_stage_key', $stage->key)->exists()) {
            return $this->back($program, 'stages', 'No se puede eliminar: hay participantes en esta etapa.', 'error');
        }
        if ($program->documentRequirements()->where('stage_key', $stage->key)->exists()
            || $program->checklistItems()->where('stage_key', $stage->key)->exists()) {
            return $this->back($program, 'stages', 'No se puede eliminar: hay documentos o ítems de checklist asociados a esta etapa.', 'error');
        }

        $stage->delete();

        return $this->back($program, 'stages', 'Etapa eliminada.');
    }

    private function validateStage(Request $request, Program $program, ?ProgramStage $stage = null): array
    {
        $data = $request->validate([
            'key' => ['required', 'string', self::KEY_RULE, Rule::unique('program_stages', 'key')->where('program_id', $program->id)->ignore($stage?->id)],
            'label' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_terminal' => ['nullable', 'boolean'],
            'mobile_screen' => ['nullable', 'string', 'max:80'],
            'guards' => ['nullable', 'array'],
            'guards.require_docs_approved' => ['nullable', 'boolean'],
            'guards.require_english_min_level' => ['nullable', 'boolean'],
            'guards.require_job_assignment' => ['nullable', 'boolean'],
            'guards.require_placement_complete' => ['nullable', 'boolean'],
            'guards.require_visa_approved' => ['nullable', 'boolean'],
            'guards.manual_only' => ['nullable', 'boolean'],
            'guards.require_gates' => ['nullable', 'array'],
            'guards.require_gates.*' => ['string'],
            'guards.require_checklist' => ['nullable', 'array'],
            'guards.require_checklist.*' => ['string'],
        ], [], ['key' => 'clave']);

        $g = $data['guards'] ?? [];
        $data['guards'] = [
            'require_docs_approved' => (bool) ($g['require_docs_approved'] ?? false),
            'require_english_min_level' => (bool) ($g['require_english_min_level'] ?? false),
            'require_job_assignment' => (bool) ($g['require_job_assignment'] ?? false),
            'require_placement_complete' => (bool) ($g['require_placement_complete'] ?? false),
            'require_visa_approved' => (bool) ($g['require_visa_approved'] ?? false),
            'manual_only' => (bool) ($g['manual_only'] ?? false),
            'require_gates' => array_values(array_filter((array) ($g['require_gates'] ?? []))),
            'require_checklist' => array_values(array_filter((array) ($g['require_checklist'] ?? []))),
        ];
        $data['is_terminal'] = (bool) ($data['is_terminal'] ?? false);
        $data['mobile_screen'] = ($data['mobile_screen'] ?? null) ?: null;
        $data['description'] = ($data['description'] ?? null) ?: null;

        return $data;
    }

    // ── Documentos ─────────────────────────────────────────────────────
    public function storeRequirement(Request $request, Program $program)
    {
        $data = $this->validateRequirement($request, $program);
        $data['program_id'] = $program->id;
        $data['sort_order'] = $data['sort_order'] ?? ((int) $program->documentRequirements()->max('sort_order') + 1);
        ProgramDocumentRequirement::create($data);

        return $this->back($program, 'documents', 'Requisito de documento creado.');
    }

    public function updateRequirement(Request $request, Program $program, ProgramDocumentRequirement $requirement)
    {
        $this->assertOwned($program, $requirement);
        $requirement->update($this->validateRequirement($request, $program, $requirement));

        return $this->back($program, 'documents', 'Requisito actualizado.');
    }

    public function destroyRequirement(Program $program, ProgramDocumentRequirement $requirement)
    {
        $this->assertOwned($program, $requirement);

        $hasUploads = ProgramDocument::withTrashed()
            ->where('requirement_key', $requirement->key)
            ->whereIn('program_process_id', ProgramProcess::forProgram($program)->select('id'))
            ->exists();
        if ($hasUploads) {
            return $this->back($program, 'documents', 'No se puede eliminar: ya hay archivos subidos para este requisito. Desactivalo en su lugar.', 'error');
        }

        $requirement->delete();

        return $this->back($program, 'documents', 'Requisito eliminado.');
    }

    private function validateRequirement(Request $request, Program $program, ?ProgramDocumentRequirement $req = null): array
    {
        $stageKeys = $program->stages()->pluck('key')->all();
        $gateKeys = $program->paymentGates()->pluck('key')->all();

        $data = $request->validate([
            'key' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]{1,59}$/', Rule::unique('program_document_requirements', 'key')->where('program_id', $program->id)->ignore($req?->id)],
            'label' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'stage_key' => ['required', 'string', Rule::in($stageKeys)],
            'group_key' => ['nullable', 'string', 'regex:/^[a-z][a-z0-9_]{1,49}$/'],
            'is_required' => ['nullable', 'boolean'],
            'min_count' => ['nullable', 'integer', 'min:1', 'max:50'],
            'allow_multiple' => ['nullable', 'boolean'],
            'uploaded_by' => ['required', Rule::in(['participant', 'staff'])],
            'unlock_gate_key' => ['nullable', 'string', Rule::in($gateKeys)],
            'section' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [], ['key' => 'clave', 'stage_key' => 'etapa']);

        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $data['allow_multiple'] = (bool) ($data['allow_multiple'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? ($req ? $req->is_active : true));
        $data['min_count'] = $data['min_count'] ?? 1;
        foreach (['group_key', 'unlock_gate_key', 'section', 'description'] as $nullable) {
            $data[$nullable] = ($data[$nullable] ?? null) ?: null;
        }

        return $data;
    }

    // ── Checklist ──────────────────────────────────────────────────────
    public function storeChecklistItem(Request $request, Program $program)
    {
        $data = $this->validateChecklistItem($request, $program);
        $data['program_id'] = $program->id;
        $data['sort_order'] = $data['sort_order'] ?? ((int) $program->checklistItems()->max('sort_order') + 1);
        ProgramChecklistItem::create($data);

        return $this->back($program, 'checklist', 'Ítem de checklist creado.');
    }

    public function updateChecklistItem(Request $request, Program $program, ProgramChecklistItem $item)
    {
        $this->assertOwned($program, $item);
        $item->update($this->validateChecklistItem($request, $program, $item));

        return $this->back($program, 'checklist', 'Ítem actualizado.');
    }

    public function destroyChecklistItem(Program $program, ProgramChecklistItem $item)
    {
        $this->assertOwned($program, $item);
        $item->delete();

        return $this->back($program, 'checklist', 'Ítem eliminado.');
    }

    private function validateChecklistItem(Request $request, Program $program, ?ProgramChecklistItem $item = null): array
    {
        $stageKeys = $program->stages()->pluck('key')->all();
        $data = $request->validate([
            'key' => ['required', 'string', self::KEY_RULE, Rule::unique('program_checklist_items', 'key')->where('program_id', $program->id)->ignore($item?->id)],
            'label' => ['required', 'string', 'max:150'],
            'stage_key' => ['nullable', 'string', Rule::in($stageKeys)],
            'item_type' => ['required', Rule::in(['boolean', 'file'])],
            'required_for_advance' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [], ['key' => 'clave']);

        $data['required_for_advance'] = (bool) ($data['required_for_advance'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? ($item ? $item->is_active : true));
        $data['stage_key'] = ($data['stage_key'] ?? null) ?: null;

        return $data;
    }

    // ── Gates de pago ──────────────────────────────────────────────────
    public function storeGate(Request $request, Program $program)
    {
        $data = $this->validateGate($request, $program);
        $data['program_id'] = $program->id;
        $data['sort_order'] = $data['sort_order'] ?? ((int) $program->paymentGates()->max('sort_order') + 1);
        ProgramPaymentGate::create($data);

        return $this->back($program, 'gates', 'Gate de pago creado.');
    }

    public function updateGate(Request $request, Program $program, ProgramPaymentGate $gate)
    {
        $this->assertOwned($program, $gate);
        $gate->update($this->validateGate($request, $program, $gate));

        return $this->back($program, 'gates', 'Gate actualizado.');
    }

    public function destroyGate(Program $program, ProgramPaymentGate $gate)
    {
        $this->assertOwned($program, $gate);
        if ($program->documentRequirements()->where('unlock_gate_key', $gate->key)->exists()) {
            return $this->back($program, 'gates', 'No se puede eliminar: hay documentos que se desbloquean con este gate.', 'error');
        }
        $gate->delete();

        return $this->back($program, 'gates', 'Gate eliminado.');
    }

    private function validateGate(Request $request, Program $program, ?ProgramPaymentGate $gate = null): array
    {
        $data = $request->validate([
            'key' => ['required', 'string', self::KEY_RULE, Rule::unique('program_payment_gates', 'key')->where('program_id', $program->id)->ignore($gate?->id)],
            'label' => ['required', 'string', 'max:150'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency_id' => ['nullable', 'exists:currencies,id'],
            'concept_match' => ['nullable', 'string', 'max:100'],
            'auto_verify_from_payments' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [], ['key' => 'clave']);

        $data['auto_verify_from_payments'] = (bool) ($data['auto_verify_from_payments'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? ($gate ? $gate->is_active : true));
        $data['concept_match'] = ($data['concept_match'] ?? null) ?: null;
        $data['currency_id'] = ($data['currency_id'] ?? null) ?: null;

        return $data;
    }

    // ── Módulos / Reglas / Onboarding ──────────────────────────────────
    public function updateModules(Request $request, Program $program)
    {
        $data = $request->validate([
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', 'max:40'], // claves desconocidas se filtran con ModuleCatalog::sanitize
            'engine_enabled' => ['nullable', 'boolean'],
            'is_available_in_app' => ['nullable', 'boolean'],
        ]);

        $program->update([
            'modules' => ModuleCatalog::sanitize($data['modules'] ?? []),
            'engine_enabled' => (bool) ($data['engine_enabled'] ?? false),
            'is_available_in_app' => (bool) ($data['is_available_in_app'] ?? false),
        ]);

        return $this->back($program, 'modules', 'Módulos actualizados.');
    }

    public function updateRules(Request $request, Program $program)
    {
        $data = $request->validate([
            'min_english_level' => ['required', Rule::in(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'])],
            'max_english_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'job_pool_allow_reselect' => ['nullable', 'boolean'],
            'support_log_types' => ['nullable', 'string', 'max:1000'],
            'visa_sections' => ['nullable', 'array'],
            'visa_sections.*' => [Rule::in(['c1', 'c2', 'c3', 'c4', 'c5', 'c6'])],
        ]);

        $types = collect(preg_split('/[\s,]+/', (string) ($data['support_log_types'] ?? '')))
            ->map(fn ($t) => strtolower(trim($t)))
            ->filter(fn ($t) => preg_match('/^[a-z][a-z0-9_]{1,39}$/', $t))
            ->unique()->values()->all();

        $program->update(['rules' => array_merge($program->rules ?? [], [
            'min_english_level' => $data['min_english_level'],
            'max_english_attempts' => (int) $data['max_english_attempts'],
            'job_pool_allow_reselect' => (bool) ($data['job_pool_allow_reselect'] ?? false),
            'support_log_types' => $types ?: ['arrival_followup', 'program_followup', 'incident', 'final_evaluation', 'participant_report'],
            'visa_sections' => array_values($data['visa_sections'] ?? ['c1', 'c2', 'c3', 'c4', 'c5', 'c6']),
        ])]);

        return $this->back($program, 'rules', 'Reglas actualizadas.');
    }

    public function updateOnboarding(Request $request, Program $program)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'intro' => ['nullable', 'string', 'max:2000'],
            'steps' => ['nullable', 'array', 'max:6'],
            'steps.*.title' => ['nullable', 'string', 'max:120'],
            'steps.*.body' => ['nullable', 'string', 'max:1000'],
            'terms_text' => ['nullable', 'string', 'max:3000'],
            'requires_adult' => ['nullable', 'boolean'],
        ]);

        $steps = collect($data['steps'] ?? [])
            ->filter(fn ($s) => ! empty($s['title']) || ! empty($s['body']))
            ->map(fn ($s) => ['title' => $s['title'] ?? '', 'body' => $s['body'] ?? ''])
            ->values()->all();

        $program->update(['onboarding' => [
            'title' => $data['title'] ?? null,
            'intro' => $data['intro'] ?? null,
            'steps' => $steps,
            'terms_text' => $data['terms_text'] ?? null,
            'requires_adult' => (bool) ($data['requires_adult'] ?? false),
        ]]);

        return $this->back($program, 'onboarding', 'Onboarding actualizado.');
    }

    // ── Recursos ───────────────────────────────────────────────────────
    public function storeResource(Request $request, Program $program)
    {
        $data = $this->validateResource($request);
        $data['program_id'] = $program->id;
        $data['uploaded_by'] = auth()->id();
        $data['sort_order'] = $data['sort_order'] ?? ((int) $program->resources()->max('sort_order') + 1);
        $resource = new ProgramResource($data);
        $this->attachResourceFile($request, $program, $resource);
        $resource->save();

        return $this->back($program, 'resources', 'Recurso creado.');
    }

    public function updateResource(Request $request, Program $program, ProgramResource $resource)
    {
        $this->assertOwned($program, $resource);
        $resource->fill($this->validateResource($request));
        $this->attachResourceFile($request, $program, $resource);
        $resource->save();

        return $this->back($program, 'resources', 'Recurso actualizado.');
    }

    public function destroyResource(Program $program, ProgramResource $resource)
    {
        $this->assertOwned($program, $resource);
        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }
        $resource->delete();

        return $this->back($program, 'resources', 'Recurso eliminado.');
    }

    public function downloadResource(Program $program, ProgramResource $resource)
    {
        $this->assertOwned($program, $resource);
        abort_unless($resource->file_path && Storage::disk('public')->exists($resource->file_path), 404);

        return Storage::disk('public')->download($resource->file_path, $resource->original_filename ?? basename($resource->file_path));
    }

    private function validateResource(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'file_type' => ['required', Rule::in(['PDF', 'DOC', 'VIDEO', 'LINK'])],
            'external_url' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'max:51200', 'mimes:pdf,doc,docx,jpg,jpeg,png,mp4,mov'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['icon'] = match ($data['file_type']) {
            'PDF' => 'fa-file-pdf', 'DOC' => 'fa-file-word', 'VIDEO' => 'fa-video', default => 'fa-link',
        };
        unset($data['file']);

        return $data;
    }

    private function attachResourceFile(Request $request, Program $program, ProgramResource $resource): void
    {
        if (! $request->hasFile('file')) {
            return;
        }
        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }
        $file = $request->file('file');
        $resource->file_path = $file->store('program-resources/'.($program->slug ?: $program->id), 'public');
        $resource->original_filename = $file->getClientOriginalName();
        $resource->file_size = $file->getSize();
    }

    // ── Helpers ────────────────────────────────────────────────────────
    private function assertOwned(Program $program, Model $model): void
    {
        abort_unless((int) $model->program_id === (int) $program->id, 404);
    }

    private function back(Program $program, string $tab, string $message, string $type = 'success')
    {
        ProgramDefinition::forget($program);

        return redirect()->route('admin.program-config.show', ['program' => $program->id, 'tab' => $tab])->with($type, $message);
    }
}
