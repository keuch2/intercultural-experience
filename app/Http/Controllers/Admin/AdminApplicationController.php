<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
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
        
        if ($request->has('status') && $request->input('status') != '') {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->has('date_range')) {
            $dateRange = explode(' - ', $request->input('date_range'));
            if (count($dateRange) == 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->endOfDay();
                $query->whereBetween('applied_at', [$startDate, $endDate]);
            }
        }
        
        $applications = $query->orderBy('applied_at', 'desc')->paginate(10);
        $programs = Program::all();
        
        return view('admin.applications.index', compact('applications', 'programs'));
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
    public function approve(Application $application)
    {
        if ($application->status !== 'in_review') {
            return redirect()->back()->with('error', 'Solo se pueden aprobar solicitudes en revisión.');
        }
        
        $application->status = 'approved';
        $application->completed_at = now();
        $application->save();
        
        // Otorgar puntos al usuario por la aprobación
        Point::create([
            'user_id' => $application->user_id,
            'change' => 100, // Valor arbitrario de puntos
            'reason' => 'application_approved',
            'related_id' => $application->id
        ]);
        
        return redirect()->back()->with('success', 'La solicitud ha sido aprobada y se han otorgado puntos al usuario.');
    }

    /**
     * Rechaza una solicitud.
     *
     * @param  \App\Models\Application  $application
     * @return \Illuminate\Http\Response
     */
    public function reject(Application $application)
    {
        if ($application->status !== 'in_review') {
            return redirect()->back()->with('error', 'Solo se pueden rechazar solicitudes en revisión.');
        }
        
        $application->status = 'rejected';
        $application->completed_at = now();
        $application->save();
        
        return redirect()->back()->with('success', 'La solicitud ha sido rechazada.');
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
        if ($request->has('status') && $request->input('status') != '') {
            $query->where('status', $request->input('status'));
        }
        
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
}
