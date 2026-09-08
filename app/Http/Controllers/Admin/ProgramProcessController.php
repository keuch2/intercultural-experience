<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ParticipantNote;
use App\Models\Program;
use App\Models\ProgramDocument;
use App\Models\ProgramEnglishTest;
use App\Models\ProgramProcess;
use App\Models\ProgramSupportLog;
use App\Services\PaymentPlanService;
use App\Services\ProgramEngine\DocumentService;
use App\Services\ProgramEngine\EnglishTestService;
use App\Services\ProgramEngine\EnvelopeBuilder;
use App\Services\ProgramEngine\Exceptions\DocumentException;
use App\Services\ProgramEngine\Exceptions\StageTransitionException;
use App\Services\ProgramEngine\ModuleCatalog;
use App\Services\ProgramEngine\ProcessResolver;
use App\Services\ProgramEngine\ProgramDefinition;
use App\Services\ProgramEngine\StageAdvancer;
use App\Services\ProgramEngine\StageEvaluator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use ZipArchive;

/**
 * Hub genérico de participantes de un programa del motor (equivalente configurable
 * de AuPairProfileController). Las tabs se derivan de las etapas y módulos del
 * programa; los documentos, checklist y gates de la configuración.
 */
class ProgramProcessController extends Controller
{
    public const FIXED_TABS = ['payments' => 'Pagos', 'resources' => 'Recursos', 'reports' => 'Informes'];

    public function __construct(
        private readonly DocumentService $documents,
        private readonly StageAdvancer $advancer,
        private readonly StageEvaluator $evaluator,
        private readonly EnvelopeBuilder $envelope,
        private readonly EnglishTestService $english,
        private readonly ProcessResolver $resolver,
        private readonly PaymentPlanService $paymentPlans,
    ) {}

    // ── Listado ────────────────────────────────────────────────────────
    public function index(Request $request, Program $program)
    {
        $this->assertEngine($program);
        $definition = ProgramDefinition::for($program);

        $query = ProgramProcess::forProgram($program)
            ->with(['user', 'application.payments', 'documents', 'englishTests', 'gates']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->orWhere('ci_number', 'like', "%{$s}%"));
        }
        if ($request->filled('stage')) {
            $query->where('current_stage_key', $request->stage);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }
        if ($request->filled('english_level')) {
            $query->whereHas('englishTests', fn ($q) => $q->where('cefr_level', $request->english_level));
        }
        if ($request->filled('gate')) {
            $verified = $request->input('gate_verified', '1') === '1';
            $query->whereHas('gates', fn ($q) => $q->where('gate_key', $request->gate)->where('is_verified', $verified));
        }
        if ($request->filled('approval')) {
            $query->whereHas('application', fn ($q) => $q->where('status', $request->approval === 'approved' ? '=' : '!=', 'approved'));
        }

        $processes = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = ['total' => ProgramProcess::forProgram($program)->count()];
        foreach ($definition->stages() as $stage) {
            $stats['stages'][$stage->key] = ProgramProcess::forProgram($program)->where('current_stage_key', $stage->key)->count();
        }
        $stats['pending_approval'] = ProgramProcess::forProgram($program)->whereHas('application', fn ($q) => $q->where('status', '!=', 'approved'))->count();

        $seasons = ProgramProcess::forProgram($program)->whereNotNull('season')->distinct()->orderByDesc('season')->pluck('season');

        return view('admin.program-process.index', compact('program', 'definition', 'processes', 'stats', 'seasons'));
    }

    // ── Hub ────────────────────────────────────────────────────────────
    public function show(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $definition = ProgramDefinition::for($program);
        $this->resolver->syncRows($process, $definition);

        $process->load(['user', 'application.payments', 'documents', 'englishTests', 'visaProcess', 'supportLogs', 'checklist', 'gates']);
        $user = $process->user;
        $application = $process->application;

        $tabs = $this->tabs($definition);
        $activeTab = $request->query('tab', $process->current_stage_key);
        if (! isset($tabs[$activeTab])) {
            $activeTab = array_key_first($tabs);
        }

        $envelope = $this->envelope->build($process);
        $entries = collect($this->documents->describe($process));
        $tabData = $this->tabData($activeTab, $tabs[$activeTab], $program, $process, $definition, $entries);
        $notes = ParticipantNote::where('user_id', $user->id)->with('admin:id,name')->latest()->get();

        return view('admin.program-process.show', compact(
            'program', 'definition', 'process', 'user', 'application', 'tabs', 'activeTab', 'tabData', 'envelope', 'entries', 'notes'
        ));
    }

    /** @return array<string, array{label:string,type:string,key:string}> */
    private function tabs(ProgramDefinition $definition): array
    {
        $tabs = [];
        foreach ($definition->workflowStages() as $stage) {
            $tabs[$stage->key] = ['label' => $stage->label, 'type' => 'stage', 'key' => $stage->key, 'icon' => 'fa-layer-group'];
        }
        foreach ($definition->modules() as $module) {
            $meta = ModuleCatalog::get($module);
            $tab = $meta['admin_tab'];
            if (! $meta['implemented'] || $module === ModuleCatalog::ENGLISH_TEST || $module === ModuleCatalog::RESOURCES) {
                continue; // inglés se renderiza dentro de su etapa; recursos es tab fija
            }
            if (! view()->exists("admin.program-process.tabs._tab_{$tab}")) {
                continue; // el módulo aún no tiene vista de admin
            }
            // Si existe una etapa homónima, la vista del módulo la reemplaza y hereda su stage_key
            // para mostrar también documentos/gate de avance de esa etapa.
            $tabs[$tab] = [
                'label' => $tabs[$tab]['label'] ?? $meta['label'],
                'type' => 'module',
                'key' => $module,
                'icon' => $meta['icon'],
                'stage_key' => isset($tabs[$tab]) ? $tab : null,
            ];
        }
        foreach (self::FIXED_TABS as $key => $label) {
            $tabs[$key] = ['label' => $label, 'type' => 'fixed', 'key' => $key, 'icon' => ['payments' => 'fa-money-bill-wave', 'resources' => 'fa-folder-open', 'reports' => 'fa-chart-bar'][$key]];
        }

        return $tabs;
    }

    private function tabData(string $tab, array $meta, Program $program, ProgramProcess $process, ProgramDefinition $definition, $entries): array
    {
        if ($meta['type'] === 'stage') {
            return $this->stageData($meta['key'], $process, $definition, $entries);
        }

        $data = $this->moduleData($tab, $program, $process, $definition, $entries);
        if (! empty($meta['stage_key'])) {
            $data = array_merge($this->stageData($meta['stage_key'], $process, $definition, $entries), $data);
        }

        return $data;
    }

    private function stageData(string $stageKey, ProgramProcess $process, ProgramDefinition $definition, $entries): array
    {

        $stage = $definition->stage($stageKey);

        return [
            'stage' => $stage,
            'groups' => $definition->groups($stage->key),
            'entries' => $entries->where('stage_key', $stage->key)->values(),
            'checklist' => $definition->checklist($stage->key),
            'gates' => collect((array) $stage->guardValue('require_gates', []))->map(fn ($k) => $definition->gate($k))->filter(),
            'showEnglish' => $definition->hasModule(ModuleCatalog::ENGLISH_TEST) && ($stage->guardValue('require_english_min_level', false) || $stage->key === 'application'),
            'englishTests' => $process->englishTests,
            'englishRemaining' => $this->english->remainingAttempts($process),
            'englishBest' => $this->english->bestLevel($process),
            'blockingReasons' => $this->evaluator->blockingReasons($process),
            'isCurrent' => $process->current_stage_key === $stage->key,
            'isPast' => $definition->stageIndex($stage->key) < $definition->stageIndex($process->current_stage_key),
        ];

    }

    private function moduleData(string $tab, Program $program, ProgramProcess $process, ProgramDefinition $definition, $entries): array
    {
        return match ($tab) {
            'visa' => [
                'visa' => $process->visaProcess ?? $process->visaProcess()->create([]),
                'entries' => $entries->where('stage_key', 'visa')->values(),
                'sections' => (array) $definition->rule('visa_sections', ['c1', 'c2', 'c3', 'c4', 'c5', 'c6']),
            ],
            'support' => [
                'logs' => $process->supportLogs,
                'types' => (array) $definition->rule('support_log_types', ['arrival_followup', 'program_followup', 'incident', 'final_evaluation']),
            ],
            'payments' => array_merge($this->paymentPlans->summary($process->application), [
                'gates' => $definition->gates(),
                'currencies' => \App\Models\Currency::active()->get(),
            ]),
            'resources' => ['resources' => $program->resources()->get()],
            'reports' => ['logs' => ActivityLog::where('log_name', $program->slug)->where('subject_id', $process->user_id)->latest()->limit(100)->get()],
            'job_pool' => [
                'access' => $process->moduleAccess(ModuleCatalog::JOB_POOL),
                'assignment' => $process->activeJobAssignment()->with('offer')->first(),
                'history' => $process->jobAssignments()->with(['offer', 'assignedBy', 'releasedBy'])->get(),
                'events' => \App\Models\JobPoolEvent::where('program_process_id', $process->id)->with(['offer', 'actor'])->orderByDesc('created_at')->limit(50)->get(),
                'offers' => app(\App\Services\ProgramEngine\JobPoolService::class)->availableFor($process),
            ],
            'placement' => [
                'placement' => app(\App\Services\ProgramEngine\PlacementService::class)->ensure($process),
                'assignment' => $process->activeJobAssignment()->with('offer')->first(),
                'sponsors' => \App\Models\Sponsor::where('is_active', true)->orderBy('name')->get(),
                'entries' => $entries->where('stage_key', 'placement')->values(),
                'documentsComplete' => app(\App\Services\ProgramEngine\PlacementService::class)->documentsComplete($process),
            ],
            default => [],
        };
    }

    // ── Acciones: postulante / datos personales ────────────────────────
    public function approveApplicant(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $approve = $request->input('action', 'approve') !== 'revoke';
        $process->application?->update(['status' => $approve ? 'approved' : 'pending']);
        $this->log($program, $process, $approve ? 'applicant_approved' : 'applicant_revoked', $approve ? 'Postulante aprobado' : 'Aprobación revocada');

        return back()->with('success', $approve ? 'Postulante aprobado. Ya puede subir documentos desde la app.' : 'Aprobación revocada.');
    }

    public function updatePersonal(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $validated = $request->validate([
            'name' => 'required|string|max:255', 'phone' => 'nullable|string|max:30', 'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100', 'country' => 'nullable|string|max:100', 'nationality' => 'nullable|string|max:100',
            'ci_number' => 'nullable|string|max:30', 'marital_status' => 'nullable|string|max:30', 'academic_level' => 'nullable|string|max:100',
            'university' => 'nullable|string|max:255', 'career' => 'nullable|string|max:255', 'academic_year' => 'nullable|string|max:50',
            'current_job' => 'nullable|string|max:255', 'job_position' => 'nullable|string|max:255',
            'enrollment_date' => 'nullable|date', 'season' => 'nullable|string|max:20',
        ]);
        $process->user->update(collect($validated)->except(['enrollment_date', 'season'])->all());
        $process->update(array_filter(['enrollment_date' => $validated['enrollment_date'] ?? null, 'season' => $validated['season'] ?? null]));

        return $this->toTab($program, $process, $request->input('redirect_tab', $process->current_stage_key), 'Datos personales actualizados.');
    }

    // ── Acciones: documentos ───────────────────────────────────────────
    public function uploadDocument(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $data = $request->validate([
            'requirement_key' => 'required|string',
            'files' => 'required|array|min:1|max:20',
            'files.*' => 'required|file|max:1048576',
            'notes' => 'nullable|string|max:500',
        ]);
        $req = ProgramDefinition::for($program)->requirement($data['requirement_key']);

        try {
            $created = $this->documents->store($process, $data['requirement_key'], $request->file('files'), 'staff', $request->user());
            if (! empty($data['notes'])) {
                foreach ($created as $doc) {
                    $doc->update(['notes' => $data['notes']]);
                }
            }
        } catch (DocumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return $this->toTab($program, $process, $req?->stage_key, count($created).' documento(s) subido(s).');
    }

    public function reviewDocument(Request $request, Program $program, ProgramProcess $process, ProgramDocument $document)
    {
        $this->assertOwned($program, $process);
        abort_unless($document->program_process_id === $process->id, 404);
        $data = $request->validate(['action' => 'required|in:approve,reject', 'rejection_reason' => 'nullable|string|max:1000']);

        try {
            $this->documents->review($document, $data['action'] === 'approve' ? 'approved' : 'rejected', $data['rejection_reason'] ?? null, $request->user());
        } catch (DocumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return $this->toTab($program, $process, $document->stage_key, 'Documento '.($data['action'] === 'approve' ? 'aprobado' : 'rechazado').'.');
    }

    public function bulkApproveDocuments(Request $request, Program $program, ProgramProcess $process, string $requirementKey)
    {
        $this->assertOwned($program, $process);
        $count = $this->documents->bulkApprove($process, $requirementKey, $request->user());
        $stage = ProgramDefinition::for($program)->requirement($requirementKey)?->stage_key;

        return $this->toTab($program, $process, $stage, "{$count} documento(s) aprobado(s).");
    }

    public function deleteDocument(Request $request, Program $program, ProgramProcess $process, ProgramDocument $document)
    {
        $this->assertOwned($program, $process);
        abort_unless($document->program_process_id === $process->id, 404);
        $reason = $request->input('deletion_reason');
        if ($document->isApproved() && ! $reason) {
            return back()->with('error', 'Para eliminar un documento aprobado debe indicar un motivo.');
        }
        $this->documents->delete($document, $request->user(), $reason);

        return $this->toTab($program, $process, $document->stage_key, 'Documento eliminado.');
    }

    public function downloadDocument(Program $program, ProgramProcess $process, ProgramDocument $document)
    {
        $this->assertOwned($program, $process);
        abort_unless($document->program_process_id === $process->id && $document->file_path, 404);
        $disk = $this->documents->disk();
        abort_unless($disk->exists($document->file_path), 404, 'Archivo no disponible.');

        return $disk->download($document->file_path, $document->original_filename ?? basename($document->file_path));
    }

    public function downloadDocumentsBundle(Program $program, ProgramProcess $process, string $requirementKey)
    {
        $this->assertOwned($program, $process);
        $docs = $process->documents()->where('requirement_key', $requirementKey)->get();
        abort_if($docs->isEmpty(), 404);

        $tmp = tempnam(sys_get_temp_dir(), 'docs');
        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::OVERWRITE);
        $disk = $this->documents->disk();
        foreach ($docs as $i => $doc) {
            if ($disk->exists($doc->file_path)) {
                $zip->addFromString(($i + 1).'_'.($doc->original_filename ?? basename($doc->file_path)), $disk->get($doc->file_path));
            }
        }
        $zip->close();

        return response()->download($tmp, "{$requirementKey}_{$process->id}.zip")->deleteFileAfterSend(true);
    }

    // ── Acciones: checklist y gates ────────────────────────────────────
    public function updateChecklist(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $definition = ProgramDefinition::for($program);
        $stageKey = $request->input('stage_key');
        $this->resolver->syncRows($process, $definition);

        foreach ($definition->checklist($stageKey) as $item) {
            $row = $process->checklist()->firstOrCreate(['item_key' => $item->key]);

            if ($item->item_type === 'file') {
                if ($request->hasFile("files.{$item->key}")) {
                    if ($row->is_done && $row->file_path) {
                        return back()->with('error', "'{$item->label}' ya tiene un archivo. Elimínelo primero indicando un motivo.");
                    }
                    $file = $request->file("files.{$item->key}");
                    $row->file_path = $file->store("program-docs/{$program->slug}/{$process->id}/checklist", DocumentService::DISK);
                    $row->original_filename = $file->getClientOriginalName();
                    $row->is_done = true;
                    $row->done_at = now();
                    $row->done_by = $request->user()->id;
                    $row->save();
                }

                continue; // los ítems con archivo no se marcan a mano
            }

            $done = $request->boolean("items.{$item->key}");
            if ($done !== (bool) $row->is_done) {
                $row->update(['is_done' => $done, 'done_at' => $done ? now() : null, 'done_by' => $done ? $request->user()->id : null]);
            }
        }
        $this->log($program, $process, 'checklist_updated', 'Checklist actualizado');

        return $this->toTab($program, $process, $stageKey ?: $process->current_stage_key, 'Checklist actualizado.');
    }

    public function downloadChecklistFile(Program $program, ProgramProcess $process, string $itemKey)
    {
        $this->assertOwned($program, $process);
        $row = $process->checklist()->where('item_key', $itemKey)->firstOrFail();
        abort_unless($row->file_path && Storage::disk(DocumentService::DISK)->exists($row->file_path), 404);

        return Storage::disk(DocumentService::DISK)->download($row->file_path, $row->original_filename ?? basename($row->file_path));
    }

    public function deleteChecklistFile(Request $request, Program $program, ProgramProcess $process, string $itemKey)
    {
        $this->assertOwned($program, $process);
        $request->validate(['reason' => 'required|string|max:500']);
        $row = $process->checklist()->where('item_key', $itemKey)->firstOrFail();
        if ($row->file_path && Storage::disk(DocumentService::DISK)->exists($row->file_path)) {
            Storage::disk(DocumentService::DISK)->delete($row->file_path);
        }
        $row->update(['file_path' => null, 'original_filename' => null, 'is_done' => false, 'done_at' => null, 'done_by' => null, 'notes' => 'Eliminado: '.$request->reason]);
        $this->log($program, $process, 'checklist_file_deleted', "Archivo de '{$itemKey}' eliminado: {$request->reason}");

        return back()->with('success', 'Archivo eliminado.');
    }

    public function updateGate(Request $request, Program $program, ProgramProcess $process, string $gateKey)
    {
        $this->assertOwned($program, $process);
        $definition = ProgramDefinition::for($program);
        abort_unless($definition->gate($gateKey), 404);
        $verified = $request->boolean('value');

        $row = $process->gates()->firstOrCreate(['gate_key' => $gateKey]);
        $row->update([
            'is_verified' => $verified,
            'verified_at' => $verified ? now() : null,
            'verified_by' => $verified ? $request->user()->id : null,
            'notes' => $request->input('notes', $row->notes),
        ]);
        $this->log($program, $process, $verified ? 'gate_verified' : 'gate_unverified', "Gate '{$definition->gate($gateKey)->label}' ".($verified ? 'verificado' : 'desmarcado'));

        return $this->toTab($program, $process, $request->input('redirect_tab', 'payments'), 'Estado de pago actualizado.');
    }

    // ── Acciones: etapas ───────────────────────────────────────────────
    public function advanceStage(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        try {
            $this->advancer->advance($process, $request->user(), $request->boolean('force'));
        } catch (StageTransitionException $e) {
            return back()->with('error', 'No se puede avanzar: '.implode(' ', $e->reasons));
        }

        return $this->toTab($program, $process, $process->fresh()->current_stage_key, 'El proceso avanzó a la siguiente etapa.');
    }

    public function revertStage(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $data = $request->validate(['to_stage' => 'required|string', 'reason' => 'nullable|string|max:500']);
        try {
            $this->advancer->revert($process, $data['to_stage'], $request->user(), $data['reason'] ?? null);
        } catch (StageTransitionException $e) {
            return back()->with('error', implode(' ', $e->reasons));
        }

        return $this->toTab($program, $process, $data['to_stage'], 'Etapa retrocedida.');
    }

    public function updateFinalization(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $data = $request->validate([
            'finalization_result' => 'required|in:success,not_success,status_change,other',
            'finalization_reason' => 'nullable|string|max:2000',
            'finalization_date' => 'nullable|date',
        ]);
        $this->advancer->finalize($process, $request->user(), $data['finalization_result'], $data['finalization_reason'] ?? null, $data['finalization_date'] ?? null);

        return $this->toTab($program, $process, $request->input('redirect_tab', 'visa'), 'Finalización registrada.');
    }

    public function cancelProcess(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $request->validate(['reason' => 'nullable|string|max:1000']);
        $this->advancer->cancel($process, $request->user(), $request->input('reason'));

        return back()->with('success', 'Proceso cancelado.');
    }

    // ── Acciones: inglés ───────────────────────────────────────────────
    public function storeEnglishTest(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $validated = $request->validate([
            'evaluator_name' => 'required|string|max:255', 'exam_name' => 'required|string|max:255',
            'oral_score' => 'nullable|string|in:Good,Great,Excellent',
            'listening_score' => 'nullable|integer|min:0|max:100', 'reading_score' => 'nullable|integer|min:0|max:100',
            'final_score' => 'required|integer|min:0|max:100', 'observations' => 'nullable|string|max:1000',
            'test_pdf' => 'nullable|file|mimes:pdf|max:10240', 'results_sent_to_applicant' => 'nullable|boolean',
        ]);
        $validated['results_sent_to_applicant'] = $request->boolean('results_sent_to_applicant');
        $validated['results_sent_at'] = $validated['results_sent_to_applicant'] ? now() : null;
        unset($validated['test_pdf']);

        try {
            $test = $this->english->record($process, $validated, $request->file('test_pdf'), $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return $this->toTab($program, $process, $request->input('redirect_tab', $process->current_stage_key), "Evaluación #{$test->attempt_number} registrada. Nivel: {$test->cefr_level}.");
    }

    public function downloadEnglishTestPdf(Program $program, ProgramProcess $process, ProgramEnglishTest $test)
    {
        $this->assertOwned($program, $process);
        abort_unless($test->program_process_id === $process->id && $test->test_pdf_path && Storage::disk('public')->exists($test->test_pdf_path), 404);

        return Storage::disk('public')->download($test->test_pdf_path, "test_ingles_{$test->attempt_number}.pdf");
    }

    public function deleteEnglishTest(Request $request, Program $program, ProgramProcess $process, ProgramEnglishTest $test)
    {
        $this->assertOwned($program, $process);
        abort_unless($test->program_process_id === $process->id, 404);
        $test->delete();

        return $this->toTab($program, $process, $request->input('redirect_tab', $process->current_stage_key), 'Evaluación eliminada.');
    }

    // ── Acciones: visa ─────────────────────────────────────────────────
    public function updateVisaProcess(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $visa = $process->visaProcess ?? $process->visaProcess()->create([]);
        $bools = ['visa_email_sent', 'consular_fee_paid', 'appointment_scheduled', 'documents_sent_for_appointment', 'document_check_completed', 'pre_departure_orientation_completed'];

        $data = $request->validate([
            'appointment_date' => 'nullable|date', 'appointment_time' => 'nullable|date_format:H:i,H:i:s', 'embassy' => 'nullable|string|max:255',
            'interview_result' => 'nullable|in:pending,approved,denied,administrative_process', 'interview_result_notes' => 'nullable|string|max:1000',
            'departure_datetime' => 'nullable|date', 'arrival_usa_datetime' => 'nullable|date',
            'outbound_legs' => 'nullable|array', 'outbound_legs.*.origin' => 'nullable|string|max:255', 'outbound_legs.*.destination' => 'nullable|string|max:255',
            'outbound_legs.*.airline' => 'nullable|string|max:255', 'outbound_legs.*.flight_number' => 'nullable|string|max:50', 'outbound_legs.*.departure' => 'nullable|string|max:50',
            'return_legs' => 'nullable|array', 'return_legs.*.origin' => 'nullable|string|max:255', 'return_legs.*.destination' => 'nullable|string|max:255',
            'return_legs.*.airline' => 'nullable|string|max:255', 'return_legs.*.flight_number' => 'nullable|string|max:50', 'return_legs.*.departure' => 'nullable|string|max:50',
            'pre_departure_orientation_date' => 'nullable|date',
        ]);
        foreach ($bools as $field) {
            $data[$field] = $request->boolean($field);
        }
        if ($data['document_check_completed'] && ! $visa->document_check_completed) {
            $data['document_check_completed_at'] = now();
        }
        $legs = fn ($key) => collect($data[$key] ?? [])->filter(fn ($l) => ! empty($l['origin']) || ! empty($l['destination']))->values()->all();
        $data['flight_info'] = ['outbound_legs' => $legs('outbound_legs'), 'return_legs' => $legs('return_legs')];
        unset($data['outbound_legs'], $data['return_legs']);

        $visa->update($data);
        $this->log($program, $process, 'visa_updated', 'Proceso de visa actualizado');

        return $this->toTab($program, $process, 'visa', 'Proceso de visa actualizado.');
    }

    // ── Acciones: support ──────────────────────────────────────────────
    public function storeSupportLog(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $types = (array) ProgramDefinition::for($program)->rule('support_log_types', ['arrival_followup', 'program_followup', 'incident', 'final_evaluation']);
        $validated = $request->validate([
            'log_type' => ['required', Rule::in($types)], 'title' => 'required|string|max:255', 'description' => 'nullable|string|max:2000',
            'log_date' => 'required|date', 'follow_up_number' => 'nullable|integer|min:1', 'severity' => 'nullable|in:low,medium,high,critical',
            'resolution' => 'nullable|string|max:2000',
        ]);
        $validated['logged_by'] = $request->user()->id;
        $process->supportLogs()->create($validated);

        return $this->toTab($program, $process, 'support', 'Registro de seguimiento creado.');
    }

    public function deleteSupportLog(Program $program, ProgramProcess $process, ProgramSupportLog $log)
    {
        $this->assertOwned($program, $process);
        abort_unless($log->program_process_id === $process->id, 404);
        $log->delete();

        return $this->toTab($program, $process, 'support', 'Registro eliminado.');
    }

    // ── Acciones: Job Placement ────────────────────────────────────────
    public function updatePlacement(Request $request, Program $program, ProgramProcess $process, \App\Services\ProgramEngine\PlacementService $placements)
    {
        $this->assertOwned($program, $process);
        $data = $request->validate([
            'sponsor_id' => 'nullable|exists:sponsors,id',
            'status' => ['nullable', Rule::in(array_keys(\App\Models\JobPlacement::STATUSES))],
            'acceptance_date' => 'nullable|date', 'program_start_date' => 'nullable|date', 'program_end_date' => 'nullable|date|after_or_equal:program_start_date',
            'terms_accepted' => 'nullable|boolean',
            'sevis_number' => 'nullable|string|max:30', 'ds2019_number' => 'nullable|string|max:30',
            'ds_tracking_carrier' => 'nullable|string|max:50', 'ds_tracking_number' => 'nullable|string|max:80', 'ds_received_at' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);
        $data['terms_accepted'] = $request->boolean('terms_accepted');
        $placements->update($process, $data, $request->user());
        $this->log($program, $process, 'placement_updated', 'Job Placement actualizado');

        return $this->toTab($program, $process, 'placement', 'Job Placement actualizado.');
    }

    // ── Acciones: acceso a módulos ─────────────────────────────────────
    public function updateModuleAccess(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $data = $request->validate([
            'module' => ['required', Rule::in(ModuleCatalog::keys())],
            'enabled' => 'required|boolean',
            'allow_reselect' => 'nullable|boolean',
        ]);
        $access = $process->module_access ?? [];
        $access[$data['module']] = [
            'enabled' => (bool) $data['enabled'],
            'enabled_at' => $data['enabled'] ? now()->toIso8601String() : null,
            'enabled_by' => $data['enabled'] ? $request->user()->id : null,
            'allow_reselect' => $request->boolean('allow_reselect'),
        ];
        $process->update(['module_access' => $access]);
        $this->log($program, $process, 'module_access_updated', "Acceso al módulo '{$data['module']}' ".($data['enabled'] ? 'habilitado' : 'deshabilitado'));

        return back()->with('success', 'Acceso actualizado.');
    }

    // ── Acciones: pagos ────────────────────────────────────────────────
    public function updateProgramCost(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $validated = $request->validate([
            'total_cost' => 'required|numeric|min:0', 'cost_currency' => 'nullable|in:USD,PYG',
            'exchange_rate' => 'nullable|numeric|min:0', 'payment_deadline' => 'nullable|date',
        ]);
        $this->paymentPlans->updateProgramCost($process->application, $validated);

        return $this->toTab($program, $process, 'payments', 'Costo del programa actualizado.');
    }

    public function storeInstallmentPlan(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $validated = $request->validate([
            'plan_name' => 'nullable|string|max:100', 'total_installments' => 'required|integer|min:2|max:24',
            'total_amount' => 'required|numeric|min:0', 'first_due_date' => 'required|date',
        ]);
        $plan = $this->paymentPlans->createInstallmentPlan($process->application, $validated, $request->user()->id);

        return $this->toTab($program, $process, 'payments', "Plan de {$plan->total_installments} cuotas creado.");
    }

    // ── Acciones: notas ────────────────────────────────────────────────
    public function updateNotes(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $request->validate(['notes' => 'nullable|string|max:5000']);
        $process->update(['notes' => $request->notes]);

        return $this->toTab($program, $process, $request->input('redirect_tab', $process->current_stage_key), 'Notas guardadas.');
    }

    public function storeParticipantNote(Request $request, Program $program, ProgramProcess $process)
    {
        $this->assertOwned($program, $process);
        $request->validate(['content' => 'required|string|max:2000']);
        ParticipantNote::create(['user_id' => $process->user_id, 'admin_id' => $request->user()->id, 'application_id' => $process->application_id, 'content' => $request->content]);

        return back()->with('success', 'Nota guardada.');
    }

    public function deleteParticipantNote(Program $program, ProgramProcess $process, ParticipantNote $note)
    {
        $this->assertOwned($program, $process);
        abort_unless($note->user_id === $process->user_id, 404);
        $note->delete();

        return back()->with('success', 'Nota eliminada.');
    }

    // ── Helpers ────────────────────────────────────────────────────────
    private function assertEngine(Program $program): void
    {
        abort_unless($program->engine_enabled, 404, 'Este programa no está gestionado por el motor.');
    }

    private function assertOwned(Program $program, ProgramProcess $process): void
    {
        $this->assertEngine($program);
        abort_unless((int) $process->program_id === (int) $program->id, 404);
    }

    private function toTab(Program $program, ProgramProcess $process, ?string $tab, string $message)
    {
        return redirect()->route('admin.program.participants.show', ['program' => $program->slug, 'process' => $process->id, 'tab' => $tab ?: $process->current_stage_key])
            ->with('success', $message);
    }

    private function log(Program $program, ProgramProcess $process, string $action, string $description): void
    {
        ActivityLog::log($program->slug ?? 'program_engine')
            ->performedOn($process->user)
            ->causedBy(auth()->user())
            ->withAction($action)
            ->withProperties(['program_process_id' => $process->id])
            ->log($description);
    }
}
