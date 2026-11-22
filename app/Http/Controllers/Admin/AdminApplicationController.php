<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Services\ApplicationReviewService;
use App\Models\ApplicationDocument;
use App\Models\Program;
use App\Models\Point;
use App\Models\User;

class AdminApplicationController extends Controller
{
    /**
     * Muestra la lista de solicitudes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Application::with(['user', 'program']);
        
        // Aplicar filtros si existen
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('program_id') && $request->input('program_id') != '') {
            $query->where('program_id', $request->input('program_id'));
        }
        
        // Bandeja de solicitudes: por defecto las que esperan la decisión de IE (pendientes / en revisión).
        $status = $request->input('status', 'awaiting');
        match ($status) {
            'awaiting' => $query->awaitingReview(),
            'all', '' => null,
            default => $query->where('status', $status),
        };

        if ($request->has('date_range')) {
            $dateRange = explode(' - ', $request->input('date_range'));
            if (count($dateRange) == 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->endOfDay();
                $query->whereBetween('applied_at', [$startDate, $endDate]);
            }
        }
        
        $applications = $query->with('reviewer:id,name')->orderBy('applied_at', 'desc')->paginate(20)->withQueryString();
        $programs = Program::orderBy('name')->get();
        $counts = [
            'awaiting' => Application::awaitingReview()->count(),
            'approved' => Application::where('status', 'approved')->count(),
            'rejected' => Application::where('status', 'rejected')->count(),
        ];

        return view('admin.applications.index', compact('applications', 'programs', 'status', 'counts'));
    }

    /**
     * Muestra la información de una solicitud específica.
     *
     * @param  \App\Models\Application  $application
     * @return \Illuminate\Http\Response
     */
    public function show(Application $application)
    {
        // Cargar relaciones
        $application->load(['user', 'program']);
        
        // Obtener documentos asociados
        $documents = ApplicationDocument::where('application_id', $application->id)->get();
        
        // Obtener notas (si existe una tabla para ello)
        $notes = [];
        
        return view('admin.applications.show', compact('application', 'documents', 'notes'));
    }

    /**
     * Marca una solicitud como "en revisión".
     *
     * @param  \App\Models\Application  $application
     * @return \Illuminate\Http\Response
     */
    public function review(Application $application)
    {
        if ($application->status !== 'pending') {
            return redirect()->back()->with('error', 'Solo se pueden revisar solicitudes pendientes.');
        }
        
        $application->status = 'in_review';
        $application->save();
        
        return redirect()->back()->with('success', 'La solicitud ha sido marcada como en revisión.');
    }

    /**
     * Aprueba una solicitud.
     *
     * @param  \App\Models\Application  $application
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, Application $application, ApplicationReviewService $review)
    {
        if (! in_array($application->status, Application::AWAITING_REVIEW_STATUSES, true)) {
            return redirect()->back()->with('error', 'Solo se pueden aprobar solicitudes pendientes o en revisión.');
        }

        // Crea el proceso del programa y avisa al participante.
        $review->approve($application, $request->user());

        Point::create([
            'user_id' => $application->user_id,
            'change' => 100,
            'reason' => 'application_approved',
            'related_id' => $application->id,
        ]);

        return redirect()->back()->with('success', "Solicitud aprobada: {$application->user?->name} ya puede continuar con su proceso de {$application->program?->name}. Se le envió un aviso.");
    }

    /**
     * Rechaza una solicitud: queda como historial, no se crea (y se borra si existía) el proceso del
     * programa, y el participante recibe un aviso. Puede volver a postular.
     */
    public function reject(Request $request, Application $application, ApplicationReviewService $review)
    {
        if (! in_array($application->status, Application::AWAITING_REVIEW_STATUSES, true)) {
            return redirect()->back()->with('error', 'Solo se pueden rechazar solicitudes pendientes o en revisión.');
        }
        $data = $request->validate(['rejection_reason' => ['nullable', 'string', 'max:1000']]);

        $review->reject($application, $request->user(), $data['rejection_reason'] ?? null);

        return redirect()->back()->with('success', "Solicitud rechazada. Se le avisó a {$application->user?->name}.");
    }

    /**
     * Muestra la lista de documentos de todas las solicitudes.
     *
     * @return \Illuminate\Http\Response
     */
    public function documents(Request $request)
    {
        $query = ApplicationDocument::with(['application.user', 'application.program']);
        
        // Aplicar filtros si existen
        // Bandeja de solicitudes: por defecto las que esperan la decisión de IE (pendientes / en revisión).
        $status = $request->input('status', 'awaiting');
        match ($status) {
            'awaiting' => $query->awaitingReview(),
            'all', '' => null,
            default => $query->where('status', $status),
        };

        $documents = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.documents.index', compact('documents'));
    }

    /**
     * Verifica un documento.
     *
     * @param  \App\Models\ApplicationDocument  $document
     * @return \Illuminate\Http\Response
     */
    public function verifyDocument(ApplicationDocument $document)
    {
        $document->status = 'verified';
        $document->save();
        
        return redirect()->back()->with('success', 'El documento ha sido verificado.');
    }

    /**
     * Rechaza un documento.
     *
     * @param  \App\Models\ApplicationDocument  $document
     * @return \Illuminate\Http\Response
     */
    public function rejectDocument(ApplicationDocument $document)
    {
        $document->status = 'rejected';
        $document->save();
        
        return redirect()->back()->with('success', 'El documento ha sido rechazado.');
    }
    
    /**
     * Exporta la lista de solicitudes a un archivo CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        // Implementación básica para demostración
        return redirect()->route('admin.applications.index')
            ->with('info', 'La funcionalidad de exportación estará disponible próximamente.');
    }

    /**
     * Show timeline for specific application
     */
    public function timeline($id)
    {
        $application = Application::with(['user', 'program', 'documents', 'statusHistory'])
            ->findOrFail($id);

        // Define workflow steps
        $steps = [
            ['key' => 'draft', 'label' => 'Borrador', 'icon' => 'fa-file-alt', 'color' => 'secondary'],
            ['key' => 'submitted', 'label' => 'Enviada', 'icon' => 'fa-paper-plane', 'color' => 'info'],
            ['key' => 'under_review', 'label' => 'En Revisión', 'icon' => 'fa-search', 'color' => 'warning'],
            ['key' => 'documents_pending', 'label' => 'Documentos Pendientes', 'icon' => 'fa-file-upload', 'color' => 'warning'],
            ['key' => 'interview_scheduled', 'label' => 'Entrevista Programada', 'icon' => 'fa-calendar-check', 'color' => 'info'],
            ['key' => 'interview_completed', 'label' => 'Entrevista Completada', 'icon' => 'fa-check-circle', 'color' => 'success'],
            ['key' => 'approved', 'label' => 'Aprobada', 'icon' => 'fa-thumbs-up', 'color' => 'success'],
            ['key' => 'rejected', 'label' => 'Rechazada', 'icon' => 'fa-times-circle', 'color' => 'danger'],
        ];

        // Calculate progress
        $currentStepIndex = array_search($application->status, array_column($steps, 'key'));
        $progress = $currentStepIndex !== false ? (($currentStepIndex + 1) / count($steps)) * 100 : 0;

        // Days in current status
        $daysInStatus = $application->updated_at->diffInDays(now());

        return view('admin.applications.timeline', compact('application', 'steps', 'progress', 'daysInStatus'));
    }

    /**
     * Update application status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,submitted,under_review,documents_pending,interview_scheduled,interview_completed,approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $application = Application::findOrFail($id);
        $oldStatus = $application->status;

        // Aprobar / rechazar siempre por el servicio (crea o borra el proceso y avisa al participante).
        if (in_array($request->status, ['approved', 'rejected'], true) && in_array($oldStatus, Application::AWAITING_REVIEW_STATUSES, true)) {
            $review = app(ApplicationReviewService::class);
            $request->status === 'approved'
                ? $review->approve($application, $request->user())
                : $review->reject($application, $request->user(), $request->notes);

            return back()->with('success', 'Estado actualizado exitosamente.');
        }

        $application->update([
            'status' => $request->status,
            'status_notes' => $request->notes,
        ]);

        // Log status change
        \DB::table('application_status_history')->insert([
            'application_id' => $application->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'changed_by' => auth()->id(),
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Estado actualizado exitosamente.');
    }

    /**
     * Dashboard showing applications timeline overview
     */
    public function timelineDashboard()
    {
        // Count by status
        $statusCounts = Application::select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Applications stuck in status > 7 days
        $stuckApplications = Application::with(['user', 'program'])
            ->where('updated_at', '<', now()->subDays(7))
            ->whereNotIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'asc')
            ->limit(10)
            ->get();

        // Recent status changes
        $recentChanges = \DB::table('application_status_history')
            ->join('applications', 'application_status_history.application_id', '=', 'applications.id')
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->select('application_status_history.*', 'users.name as user_name', 'applications.id as app_id')
            ->orderBy('application_status_history.created_at', 'desc')
            ->limit(15)
            ->get();

        // Average time per status
        $avgTimeByStatus = \DB::table('application_status_history')
            ->select('new_status', \DB::raw('AVG(TIMESTAMPDIFF(DAY, created_at, updated_at)) as avg_days'))
            ->groupBy('new_status')
            ->get();

        return view('admin.applications.timeline-dashboard', compact(
            'statusCounts',
            'stuckApplications',
            'recentChanges',
            'avgTimeByStatus'
        ));
    }
}
