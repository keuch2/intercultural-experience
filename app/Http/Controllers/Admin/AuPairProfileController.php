<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuPairProfile;
use App\Models\AuPairProcess;
use App\Models\AuPairDocument;
use App\Models\Application;
use App\Models\Payment;
use App\Models\EnglishEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuPairProfileController extends Controller
{
    /**
     * Listado de Perfiles Au Pair con filtros
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user')
            ->whereHas('applications', function ($q) {
                $q->whereHas('program', function ($pq) {
                    $pq->where('subcategory', 'Au Pair');
                });
            })
            ->with([
                'applications' => function ($q) {
                    $q->whereHas('program', fn($pq) => $pq->where('subcategory', 'Au Pair'))
                      ->with('program')
                      ->latest();
                },
                'applications.payments',
                'auPairProfile',
                'auPairProcess.documents',
                'englishEvaluations',
            ]);

        // Filtro: búsqueda por nombre/email/CI
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ci_number', 'like', "%{$search}%");
            });
        }

        // Filtro: etapa/proceso (now uses au_pair_processes)
        if ($request->filled('stage')) {
            $stage = $request->stage;
            $query->whereHas('auPairProcess', function ($q) use ($stage) {
                $q->where('current_stage', $stage);
            });
        }

        // Filtro: nivel inglés
        if ($request->filled('english_level')) {
            $level = $request->english_level;
            $query->whereHas('englishEvaluations', function ($q) use ($level) {
                $q->where('cefr_level', $level);
            });
        }

        // Filtro: país
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        // Filtro: pagos
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'paid') {
                $query->whereHas('auPairProcess', fn($q) => $q->where('payment_1_verified', true));
            } elseif ($request->payment_status === 'pending') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('auPairProcess')
                      ->orWhereHas('auPairProcess', fn($sq) => $sq->where('payment_1_verified', false));
                });
            }
        }

        $profiles = $query->orderBy('created_at', 'desc')->paginate(20);

        // Estadísticas rápidas using AuPairProcess
        $stats = [
            'total' => AuPairProcess::count() ?: User::where('role', 'user')->whereHas('applications', fn($q) => $q->whereHas('program', fn($pq) => $pq->where('subcategory', 'Au Pair')))->count(),
            'admission' => AuPairProcess::where('current_stage', 'admission')->count(),
            'active' => AuPairProcess::whereIn('current_stage', ['application', 'match_visa'])->count(),
            'matched' => AuPairProfile::where('profile_status', 'matched')->count(),
        ];

        // Países para filtro
        $countries = User::where('role', 'user')
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country')
            ->sort();

        return view('admin.au-pair.profiles.index', compact('profiles', 'stats', 'countries'));
    }

    /**
     * Hub central del Perfil Au Pair (vista con tabs)
     */
    public function show(Request $request, $id)
    {
        $user = User::with([
            'applications' => function ($q) {
                $q->whereHas('program', fn($pq) => $pq->where('subcategory', 'Au Pair'))
                  ->with(['program', 'payments', 'documents'])
                  ->latest();
            },
            'auPairProfile.matches.familyProfile',
            'auPairProcess.documents',
            'auPairProcess.englishTests',
            'auPairProcess.visaProcess',
            'auPairProcess.matchesExtended',
            'auPairProcess.supportLogs',
            'englishEvaluations',
            'childcareExperiences',
            'references',
            'healthDeclaration',
            'emergencyContacts',
        ])->findOrFail($id);

        $application = $user->applications->first();
        $profile = $user->auPairProfile;
        $process = $user->auPairProcess;

        // Auto-create process if user has Au Pair application but no process yet
        if ($application && !$process) {
            $process = AuPairProcess::create([
                'application_id' => $application->id,
                'user_id' => $user->id,
                'enrollment_date' => $application->created_at->toDateString(),
                'enrollment_city' => $user->city,
                'enrollment_country' => $user->country,
            ]);
            $user->setRelation('auPairProcess', $process);
        }

        // Determinar etapa actual y estados
        $stages = $this->calculateStages($user, $application, $profile, $process);

        // Tab activo (default: admission)
        $activeTab = $request->get('tab', 'admission');

        // Datos para cada tab
        $tabData = $this->getTabData($activeTab, $user, $application, $profile, $process);

        return view('admin.au-pair.profiles.show', compact(
            'user', 'application', 'profile', 'process', 'stages', 'activeTab', 'tabData'
        ));
    }

    // =========================================================================
    // ACTIONS: Personal Data
    // =========================================================================

    /**
     * Update personal data (A1)
     */
    public function updatePersonalData(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'nationality' => 'nullable|string|max:100',
            'ci_number' => 'nullable|string|max:30',
            'marital_status' => 'nullable|string|max:30',
            'academic_level' => 'nullable|string|max:100',
            'university' => 'nullable|string|max:255',
            'current_job' => 'nullable|string|max:255',
            'job_position' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        // Also update process enrollment data if provided
        $process = $user->auPairProcess;
        if ($process) {
            $processData = $request->validate([
                'enrollment_date' => 'nullable|date',
                'enrollment_city' => 'nullable|string|max:100',
                'enrollment_country' => 'nullable|string|max:100',
            ]);
            $process->update(array_filter($processData));
        }

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => 'admission'])
            ->with('success', 'Datos personales actualizados correctamente.');
    }

    // =========================================================================
    // ACTIONS: Documents
    // =========================================================================

    /**
     * Upload a document
     */
    public function uploadDocument(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $process = $user->auPairProcess;

        if (!$process) {
            return back()->with('error', 'No se encontró el proceso Au Pair para este participante.');
        }

        $request->validate([
            'document_type' => 'required|string|max:50',
            'stage' => 'required|in:admission,application_payment1,application_payment2,visa',
            'file' => 'required|file|max:1048576', // 1GB
            'notes' => 'nullable|string|max:500',
        ]);

        $docType = $request->document_type;
        $docDefs = AuPairDocument::documentTypes();
        $docDef = $docDefs[$docType] ?? ['label' => $docType, 'required' => false, 'sort' => 99];

        $file = $request->file('file');
        $path = $file->store("au-pair-documents/{$user->id}", 'public');

        AuPairDocument::create([
            'au_pair_process_id' => $process->id,
            'document_type' => $docType,
            'stage' => $request->stage,
            'uploaded_by_type' => 'staff',
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
            'is_required' => $docDef['required'] ?? true,
            'min_count' => $docDef['min_count'] ?? 1,
            'sort_order' => $docDef['sort'] ?? 99,
            'notes' => $request->notes,
        ]);

        $tab = match($request->stage) {
            'admission' => 'admission',
            'application_payment1', 'application_payment2' => 'application',
            'visa' => 'match_visa',
            default => 'admission',
        };

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => $tab])
            ->with('success', 'Documento subido correctamente.');
    }

    /**
     * Download a document
     */
    public function downloadDocument($id, $docId)
    {
        $doc = AuPairDocument::where('id', $docId)
            ->whereHas('process', fn($q) => $q->where('user_id', $id))
            ->firstOrFail();

        return Storage::disk('public')->download($doc->file_path, $doc->original_filename);
    }

    /**
     * Review a document (approve/reject)
     */
    public function reviewDocument(Request $request, $id, $docId)
    {
        $doc = AuPairDocument::where('id', $docId)
            ->whereHas('process', fn($q) => $q->where('user_id', $id))
            ->firstOrFail();

        $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:500',
        ]);

        if ($request->action === 'approve') {
            $doc->approve(Auth::id());
        } else {
            $doc->reject(Auth::id(), $request->rejection_reason);
        }

        // Check if all required admission docs are now approved → update process
        $process = $doc->process;
        if ($doc->stage === 'admission' && $process->admissionDocsApproved()) {
            $process->update(['admission_status' => 'approved']);
        }

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => $this->stageToTab($doc->stage)])
            ->with('success', $request->action === 'approve' ? 'Documento aprobado.' : 'Documento rechazado.');
    }

    /**
     * Delete a document
     */
    public function deleteDocument($id, $docId)
    {
        $doc = AuPairDocument::where('id', $docId)
            ->whereHas('process', fn($q) => $q->where('user_id', $id))
            ->firstOrFail();

        $tab = $this->stageToTab($doc->stage);

        Storage::disk('public')->delete($doc->file_path);
        $doc->forceDelete();

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => $tab])
            ->with('success', 'Documento eliminado.');
    }

    // =========================================================================
    // ACTIONS: English Tests
    // =========================================================================

    /**
     * Store a new English test result
     */
    public function storeEnglishTest(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $process = $user->auPairProcess;

        if (!$process) {
            return back()->with('error', 'Proceso Au Pair no encontrado.');
        }

        $remaining = \App\Models\AuPairEnglishTest::remainingAttempts($process->id);
        if ($remaining <= 0) {
            return back()->with('error', 'Se alcanzó el límite de 3 intentos de evaluación de inglés.');
        }

        $validated = $request->validate([
            'evaluator_name' => 'required|string|max:255',
            'exam_name' => 'required|string|max:255',
            'oral_score' => 'nullable|integer|min:0|max:100',
            'listening_score' => 'nullable|integer|min:0|max:100',
            'reading_score' => 'nullable|integer|min:0|max:100',
            'final_score' => 'required|integer|min:0|max:100',
            'observations' => 'nullable|string|max:1000',
            'test_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'results_sent_to_applicant' => 'nullable|boolean',
        ]);

        $attemptNumber = $process->englishTests()->count() + 1;
        $cefrLevel = \App\Models\AuPairEnglishTest::scoreToLevel($validated['final_score']);

        $pdfPath = null;
        if ($request->hasFile('test_pdf')) {
            $pdfPath = $request->file('test_pdf')->store("au-pair-english-tests/{$user->id}", 'public');
        }

        $process->englishTests()->create([
            'evaluator_name' => $validated['evaluator_name'],
            'exam_name' => $validated['exam_name'],
            'oral_score' => $validated['oral_score'],
            'listening_score' => $validated['listening_score'],
            'reading_score' => $validated['reading_score'],
            'final_score' => $validated['final_score'],
            'cefr_level' => $cefrLevel,
            'observations' => $validated['observations'],
            'test_pdf_path' => $pdfPath,
            'results_sent_to_applicant' => $request->has('results_sent_to_applicant'),
            'results_sent_at' => $request->has('results_sent_to_applicant') ? now() : null,
            'attempt_number' => $attemptNumber,
        ]);

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => 'application'])
            ->with('success', "Evaluación de inglés #$attemptNumber registrada. Nivel: $cefrLevel.");
    }

    /**
     * Download English test PDF
     */
    public function downloadEnglishTestPdf($id, $testId)
    {
        $test = \App\Models\AuPairEnglishTest::where('id', $testId)
            ->whereHas('process', fn($q) => $q->where('user_id', $id))
            ->firstOrFail();

        if (!$test->test_pdf_path) {
            return back()->with('error', 'No se encontró el PDF del test.');
        }

        return Storage::disk('public')->download($test->test_pdf_path, "english_test_{$test->attempt_number}.pdf");
    }

    /**
     * Delete an English test
     */
    public function deleteEnglishTest($id, $testId)
    {
        $test = \App\Models\AuPairEnglishTest::where('id', $testId)
            ->whereHas('process', fn($q) => $q->where('user_id', $id))
            ->firstOrFail();

        if ($test->test_pdf_path) {
            Storage::disk('public')->delete($test->test_pdf_path);
        }

        $test->delete();

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => 'application'])
            ->with('success', 'Evaluación de inglés eliminada.');
    }

    // =========================================================================
    // ACTIONS: Checklist & Stage
    // =========================================================================

    /**
     * Update checklist flags (B4)
     */
    public function updateChecklist(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $process = $user->auPairProcess;

        if (!$process) {
            return back()->with('error', 'Proceso Au Pair no encontrado.');
        }

        $validated = $request->validate([
            'welcome_email_sent' => 'nullable|boolean',
            'interview_process_email_sent' => 'nullable|boolean',
            'all_docs_and_payments_complete' => 'nullable|boolean',
            'itep_completed' => 'nullable|boolean',
            'contract_signed' => 'nullable|boolean',
        ]);

        // Convert checkbox values
        foreach (['welcome_email_sent', 'interview_process_email_sent', 'all_docs_and_payments_complete', 'itep_completed', 'contract_signed'] as $field) {
            $validated[$field] = $request->has($field);
        }

        if ($validated['contract_signed'] && !$process->contract_signed) {
            $validated['contract_signed_at'] = now();
            $validated['contract_confirmed_by'] = Auth::id();
        }

        $process->update($validated);

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => 'application'])
            ->with('success', 'Checklist actualizado correctamente.');
    }

    /**
     * Advance to next stage
     */
    public function advanceStage(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $process = $user->auPairProcess;

        if (!$process) {
            return back()->with('error', 'Proceso Au Pair no encontrado.');
        }

        $result = $process->advanceStage();

        if ($result) {
            return redirect()
                ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => $process->fresh()->current_stage === 'application' ? 'application' : 'match_visa'])
                ->with('success', 'El proceso avanzó a la siguiente etapa.');
        }

        return back()->with('error', 'No se puede avanzar. Verifique que se cumplan todos los requisitos.');
    }

    // =========================================================================
    // ACTIONS: Visa Process
    // =========================================================================

    /**
     * Update visa process fields
     */
    public function updateVisaProcess(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $process = $user->auPairProcess;
        if (!$process) return back()->with('error', 'Proceso Au Pair no encontrado.');

        $visa = $process->visaProcess ?? $process->visaProcess()->create([]);

        $boolFields = [
            'visa_email_sent', 'consular_fee_paid', 'appointment_scheduled',
            'documents_sent_for_appointment', 'document_check_completed',
            'pre_departure_orientation_completed',
        ];

        $data = $request->validate([
            'appointment_date' => 'nullable|date',
            'appointment_time' => 'nullable|date_format:H:i',
            'embassy' => 'nullable|string|max:255',
            'interview_result' => 'nullable|in:pending,approved,denied,administrative_process',
            'interview_result_notes' => 'nullable|string|max:1000',
            'departure_datetime' => 'nullable|date',
            'arrival_usa_datetime' => 'nullable|date',
            'flight_airline' => 'nullable|string|max:255',
            'flight_number' => 'nullable|string|max:50',
            'flight_layovers' => 'nullable|string|max:1000',
            'pre_departure_orientation_date' => 'nullable|date',
        ]);

        foreach ($boolFields as $field) {
            $data[$field] = $request->has($field);
        }

        if ($data['document_check_completed'] && !$visa->document_check_completed) {
            $data['document_check_completed_at'] = now();
        }

        // Build flight_info JSON
        $data['flight_info'] = [
            'airline' => $data['flight_airline'] ?? null,
            'flight_number' => $data['flight_number'] ?? null,
            'layovers' => $data['flight_layovers'] ?? null,
        ];
        unset($data['flight_airline'], $data['flight_number'], $data['flight_layovers']);

        $visa->update($data);

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => 'match_visa'])
            ->with('success', 'Proceso de visa actualizado.');
    }

    /**
     * Update finalization data
     */
    public function updateFinalization(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $process = $user->auPairProcess;
        if (!$process) return back()->with('error', 'Proceso Au Pair no encontrado.');

        $data = $request->validate([
            'finalization_result' => 'required|in:success,not_success,status_change,other',
            'finalization_reason' => 'nullable|string|max:2000',
            'finalization_date' => 'nullable|date',
        ]);

        $data['finalized_by'] = Auth::id();
        $data['current_stage'] = 'completed';

        $process->update($data);

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => 'match_visa'])
            ->with('success', 'Finalización registrada correctamente.');
    }

    // =========================================================================
    // ACTIONS: Matches
    // =========================================================================

    /**
     * Store a new match
     */
    public function storeMatch(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $process = $user->auPairProcess;
        if (!$process) return back()->with('error', 'Proceso Au Pair no encontrado.');

        $validated = $request->validate([
            'match_type' => 'required|in:initial,rematch,extension',
            'match_date' => 'nullable|date',
            'host_state' => 'nullable|string|max:100',
            'host_city' => 'nullable|string|max:100',
            'host_address' => 'nullable|string|max:500',
            'host_mom_name' => 'nullable|string|max:255',
            'host_dad_name' => 'nullable|string|max:255',
            'host_email' => 'nullable|email|max:255',
            'host_phone' => 'nullable|string|max:30',
        ]);

        $validated['sort_order'] = $process->matchesExtended()->count() + 1;

        // Deactivate previous active match if creating a new one
        if (in_array($validated['match_type'], ['rematch', 'extension'])) {
            $process->matchesExtended()->where('is_active', true)->update([
                'is_active' => false,
                'ended_at' => now(),
            ]);
        }

        $process->matchesExtended()->create($validated);

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => 'match_visa'])
            ->with('success', 'Match registrado correctamente.');
    }

    /**
     * Delete a match
     */
    public function deleteMatch($id, $matchId)
    {
        $match = \App\Models\AuPairMatchExtended::where('id', $matchId)
            ->whereHas('process', fn($q) => $q->where('user_id', $id))
            ->firstOrFail();

        $match->delete();

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => 'match_visa'])
            ->with('success', 'Match eliminado.');
    }

    // =========================================================================
    // ACTIONS: Support Logs
    // =========================================================================

    /**
     * Store a support log
     */
    public function storeSupportLog(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $process = $user->auPairProcess;
        if (!$process) return back()->with('error', 'Proceso Au Pair no encontrado.');

        $validated = $request->validate([
            'log_type' => 'required|in:arrival_followup,monthly_followup,incident,experience_evaluation',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'log_date' => 'required|date',
            'follow_up_number' => 'nullable|integer|min:1',
            'severity' => 'nullable|in:low,medium,high,critical',
        ]);

        $validated['logged_by'] = Auth::id();

        $process->supportLogs()->create($validated);

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => 'support'])
            ->with('success', 'Registro de seguimiento creado.');
    }

    /**
     * Delete a support log
     */
    public function deleteSupportLog($id, $logId)
    {
        $log = \App\Models\AuPairSupportLog::where('id', $logId)
            ->whereHas('process', fn($q) => $q->where('user_id', $id))
            ->firstOrFail();

        $log->delete();

        return redirect()
            ->route('admin.aupair.profiles.show', ['id' => $id, 'tab' => 'support'])
            ->with('success', 'Registro eliminado.');
    }

    // =========================================================================
    // STANDALONE PAGES
    // =========================================================================

    /**
     * Recursos del programa (listado)
     */
    public function resources()
    {
        $resources = \App\Models\AuPairResource::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.au-pair.resources.index', compact('resources'));
    }

    /**
     * Crear un recurso
     */
    public function storeResource(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'file_type' => 'required|in:PDF,DOC,VIDEO,LINK',
            'file' => 'nullable|file|max:51200', // 50MB
            'external_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['title', 'description', 'icon', 'file_type', 'external_url', 'sort_order']);
        $data['uploaded_by'] = Auth::id();
        $data['icon'] = $data['icon'] ?: 'fa-file-pdf';
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('au-pair-resources', 'public');
            $data['original_filename'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
        }

        \App\Models\AuPairResource::create($data);

        return redirect()->route('admin.aupair.resources.index')
            ->with('success', 'Recurso creado correctamente.');
    }

    /**
     * Actualizar metadatos de un recurso
     */
    public function updateResource(Request $request, $resourceId)
    {
        $resource = \App\Models\AuPairResource::findOrFail($resourceId);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'file_type' => 'required|in:PDF,DOC,VIDEO,LINK',
            'external_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $resource->update([
            'title' => $request->title,
            'description' => $request->description,
            'icon' => $request->icon ?: $resource->icon,
            'file_type' => $request->file_type,
            'external_url' => $request->external_url,
            'sort_order' => $request->sort_order ?? $resource->sort_order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.aupair.resources.index')
            ->with('success', 'Recurso actualizado.');
    }

    /**
     * Subir/reemplazar archivo de un recurso
     */
    public function uploadResourceFile(Request $request, $resourceId)
    {
        $resource = \App\Models\AuPairResource::findOrFail($resourceId);

        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        // Delete old file if exists
        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $file = $request->file('file');
        $resource->update([
            'file_path' => $file->store('au-pair-resources', 'public'),
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('admin.aupair.resources.index')
            ->with('success', 'Archivo subido correctamente.');
    }

    /**
     * Descargar archivo de un recurso
     */
    public function downloadResource($resourceId)
    {
        $resource = \App\Models\AuPairResource::findOrFail($resourceId);

        if (!$resource->file_path || !Storage::disk('public')->exists($resource->file_path)) {
            return back()->with('error', 'El archivo no existe.');
        }

        return Storage::disk('public')->download($resource->file_path, $resource->original_filename);
    }

    /**
     * Eliminar un recurso
     */
    public function deleteResource($resourceId)
    {
        $resource = \App\Models\AuPairResource::findOrFail($resourceId);

        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return redirect()->route('admin.aupair.resources.index')
            ->with('success', 'Recurso eliminado.');
    }

    /**
     * Reportes Au Pair (listado con filtros)
     */
    public function reports(Request $request)
    {
        return view('admin.au-pair.reports.index');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Calcular estado de cada etapa del proceso
     */
    private function calculateStages($user, $application, $profile, $process): array
    {
        // Use AuPairProcess if available, fallback to legacy detection
        if ($process) {
            $admissionDocsApproved = $process->admission_status === 'approved' || $process->admissionDocsApproved();
            $payment1Verified = $process->payment_1_verified;
            $payment2Verified = $process->payment_2_verified;
            $contractSigned = $process->contract_signed;
            $currentStage = $process->current_stage;
        } else {
            $admissionDocsApproved = false;
            $payment1Verified = false;
            $payment2Verified = false;
            $contractSigned = false;
            $currentStage = 'admission';

            // Legacy fallback: check payments table
            if ($application) {
                $payment1Verified = $application->payments()
                    ->where(function ($q) {
                        $q->where('payment_number', 1)
                          ->orWhere('concept', 'like', '%inscripción%');
                    })
                    ->where('status', 'verified')
                    ->exists();
                $payment2Verified = $application->payments()
                    ->where(function ($q) {
                        $q->where('payment_number', 2)
                          ->orWhere('concept', 'like', '%programa%');
                    })
                    ->where('status', 'verified')
                    ->exists();
            }
        }

        // Nivel de inglés (prefer process-level tests, fallback to legacy)
        $englishLevel = null;
        if ($process && $process->englishTests->count() > 0) {
            $bestProcessTest = $process->englishTests->sortByDesc('final_score')->first();
            $englishLevel = $bestProcessTest->cefr_level;
        } else {
            $bestEnglish = $user->englishEvaluations->sortByDesc('score')->first();
            $englishLevel = $bestEnglish ? $bestEnglish->cefr_level : null;
        }

        // Determine stage statuses — admin panel always has full access, no locks
        $stageOrder = ['admission', 'application', 'match_visa', 'support', 'completed'];
        $currentIndex = array_search($currentStage, $stageOrder) ?: 0;

        $admissionStatus = $currentIndex > 0 ? 'complete' : 'in_progress';
        $applicationStatus = $currentIndex > 1 ? 'complete' : ($currentIndex === 1 ? 'in_progress' : 'pending');
        $matchVisaStatus = $currentIndex > 2 ? 'complete' : ($currentIndex === 2 ? 'in_progress' : 'pending');
        $supportStatus = $currentIndex > 3 ? 'complete' : ($currentIndex === 3 ? 'in_progress' : 'pending');

        return [
            'admission' => [
                'status' => $admissionStatus,
                'label' => 'Admisión',
                'icon' => 'fa-user-edit',
            ],
            'application' => [
                'status' => $applicationStatus,
                'label' => 'Aplicación',
                'icon' => 'fa-file-alt',
            ],
            'match_visa' => [
                'status' => $matchVisaStatus,
                'label' => 'Match / Visa J1',
                'icon' => 'fa-passport',
            ],
            'support' => [
                'status' => $supportStatus,
                'label' => 'Support',
                'icon' => 'fa-headset',
            ],
            'resources' => [
                'status' => 'available',
                'label' => 'Recursos',
                'icon' => 'fa-folder-open',
            ],
            'reports' => [
                'status' => 'available',
                'label' => 'Informes',
                'icon' => 'fa-chart-bar',
            ],
            'payments' => [
                'status' => 'available',
                'label' => 'Pagos',
                'icon' => 'fa-money-bill-wave',
                'payment1' => $payment1Verified,
                'payment2' => $payment2Verified,
            ],
            '_meta' => [
                'english_level' => $englishLevel,
                'admission_docs_approved' => $admissionDocsApproved,
                'payment1_verified' => $payment1Verified,
                'payment2_verified' => $payment2Verified,
                'contract_signed' => $contractSigned,
                'current_stage' => $currentStage,
            ],
        ];
    }

    /**
     * Obtener datos específicos para cada tab
     */
    private function getTabData(string $tab, $user, $application, $profile, $process): array
    {
        switch ($tab) {
            case 'admission':
                return [
                    'documents' => $process
                        ? $process->documents()->where('stage', 'admission')->orderBy('sort_order')->get()
                        : collect(),
                ];

            case 'application':
                $englishTests = $process
                    ? $process->englishTests()->orderBy('attempt_number')->get()
                    : collect();
                $remainingAttempts = $process
                    ? \App\Models\AuPairEnglishTest::remainingAttempts($process->id)
                    : 3;
                return [
                    'englishTests' => $englishTests,
                    'remainingAttempts' => $remainingAttempts,
                    'documents_p1' => $process
                        ? $process->documents()->where('stage', 'application_payment1')->orderBy('sort_order')->get()
                        : collect(),
                    'documents_p2' => $process
                        ? $process->documents()->where('stage', 'application_payment2')->orderBy('sort_order')->get()
                        : collect(),
                    'process' => $process,
                ];

            case 'match_visa':
                $visaProc = $process ? $process->visaProcess : null;
                if ($process && !$visaProc) {
                    $visaProc = $process->visaProcess()->create([]);
                }
                return [
                    'visaProcess' => $visaProc,
                    'matchesExtended' => $process ? $process->matchesExtended : collect(),
                    'visaDocs' => $process
                        ? $process->documents()->where('stage', 'visa')->orderBy('sort_order')->get()
                        : collect(),
                    'process' => $process,
                ];

            case 'support':
                return [
                    'supportLogs' => $process ? $process->supportLogs : collect(),
                    'process' => $process,
                ];

            case 'resources':
                return [];

            case 'reports':
                return [];

            case 'payments':
                return [
                    'payments' => $application ? $application->payments()->orderBy('created_at', 'desc')->get() : collect(),
                ];

            default:
                return [];
        }
    }

    /**
     * Map document stage to tab name
     */
    private function stageToTab(string $stage): string
    {
        return match($stage) {
            'admission' => 'admission',
            'application_payment1', 'application_payment2' => 'application',
            'visa' => 'match_visa',
            default => 'admission',
        };
    }
}
