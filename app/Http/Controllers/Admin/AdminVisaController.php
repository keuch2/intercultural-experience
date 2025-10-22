<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisaProcess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminVisaController extends Controller
{
    /**
     * Dashboard principal de visas
     */
    public function dashboard()
    {
        // KPIs principales
        $totalInProcess = VisaProcess::inProgress()->count();
        $approvedThisMonth = VisaProcess::approved()
            ->whereMonth('visa_result_date', now()->month)
            ->whereYear('visa_result_date', now()->year)
            ->count();
        $rejectedTotal = VisaProcess::rejected()->count();
        
        // Próximas citas consulares (7 días)
        $upcomingAppointments = VisaProcess::with('user')
            ->where('consular_appointment_scheduled', true)
            ->whereBetween('consular_appointment_date', [now(), now()->addDays(7)])
            ->orderBy('consular_appointment_date')
            ->get();
        
        // Pendientes por etapa
        $pendingDs160 = VisaProcess::where('current_step', 'ds160')->count();
        $pendingDs2019 = VisaProcess::where('current_step', 'ds2019')->count();
        $pendingSevis = VisaProcess::where('current_step', 'sevis')->count();
        
        // Distribución por estados (para gráfico)
        $statusDistribution = VisaProcess::selectRaw('visa_result, count(*) as count')
            ->groupBy('visa_result')
            ->get()
            ->pluck('count', 'visa_result');
        
        return view('admin.visa.dashboard', compact(
            'totalInProcess',
            'approvedThisMonth',
            'rejectedTotal',
            'upcomingAppointments',
            'pendingDs160',
            'pendingDs2019',
            'pendingSevis',
            'statusDistribution'
        ));
    }
    
    /**
     * Lista de todos los procesos de visa
     */
    public function index(Request $request)
    {
        $query = VisaProcess::with(['user', 'application']);
        
        // Filtros
        if ($request->filled('status')) {
            $query->where('visa_result', $request->status);
        }
        
        if ($request->filled('current_step')) {
            $query->where('current_step', $request->current_step);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $visaProcesses = $query->paginate(20);
        
        return view('admin.visa.index', compact('visaProcesses'));
    }
    
    /**
     * Timeline del proceso de visa de un participante
     */
    public function timeline($userId)
    {
        $user = User::with(['visaProcess', 'applications'])->findOrFail($userId);
        $visaProcess = $user->visaProcess ?? VisaProcess::create([
            'user_id' => $userId,
            'current_step' => 'documentation',
            'progress_percentage' => 0,
        ]);
        
        return view('admin.visa.timeline', compact('user', 'visaProcess'));
    }
    
    /**
     * Actualizar un paso del proceso de visa
     */
    public function updateStep(Request $request, $userId)
    {
        $visaProcess = VisaProcess::where('user_id', $userId)->firstOrFail();
        
        $request->validate([
            'step' => 'required|string',
            'status' => 'required',
            'date' => 'nullable|date',
            'notes' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);
        
        $step = $request->step;
        $filePath = null;
        
        // Upload de archivo si existe
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('visa-documents', 'public');
        }
        
        // Actualizar según el paso
        switch ($step) {
            case 'documentation':
                $visaProcess->update([
                    'documentation_complete' => $request->status == 'completed',
                    'documentation_complete_date' => $request->date,
                    'current_step' => 'sponsor_interview',
                ]);
                break;
                
            case 'sponsor_interview':
                $visaProcess->update([
                    'sponsor_interview_status' => $request->status,
                    'sponsor_interview_date' => $request->date,
                    'sponsor_interview_notes' => $request->notes,
                    'current_step' => $request->status == 'approved' ? 'job_interview' : 'sponsor_interview',
                ]);
                break;
                
            case 'job_interview':
                $visaProcess->update([
                    'job_interview_status' => $request->status,
                    'job_interview_date' => $request->date,
                    'job_interview_notes' => $request->notes,
                    'current_step' => $request->status == 'approved' ? 'ds160' : 'job_interview',
                ]);
                break;
                
            case 'ds160':
                $visaProcess->update([
                    'ds160_completed' => true,
                    'ds160_completed_date' => $request->date,
                    'ds160_confirmation_number' => $request->confirmation_number ?? null,
                    'ds160_file_path' => $filePath,
                    'current_step' => 'ds2019',
                ]);
                break;
                
            case 'ds2019':
                $visaProcess->update([
                    'ds2019_received' => true,
                    'ds2019_received_date' => $request->date,
                    'ds2019_file_path' => $filePath,
                    'current_step' => 'sevis',
                ]);
                break;
                
            case 'sevis':
                $visaProcess->update([
                    'sevis_paid' => true,
                    'sevis_paid_date' => $request->date,
                    'sevis_amount' => $request->amount ?? 0,
                    'sevis_receipt_path' => $filePath,
                    'current_step' => 'consular_fee',
                ]);
                break;
                
            case 'consular_fee':
                $visaProcess->update([
                    'consular_fee_paid' => true,
                    'consular_fee_paid_date' => $request->date,
                    'consular_fee_amount' => $request->amount ?? 0,
                    'consular_fee_receipt_path' => $filePath,
                    'current_step' => 'appointment',
                ]);
                break;
                
            case 'appointment':
                $visaProcess->update([
                    'consular_appointment_scheduled' => true,
                    'consular_appointment_date' => $request->date,
                    'consular_appointment_location' => $request->location ?? null,
                    'current_step' => 'result',
                ]);
                break;
                
            case 'result':
                $visaProcess->update([
                    'visa_result' => $request->status, // approved, correspondence, rejected
                    'visa_result_date' => $request->date,
                    'visa_result_notes' => $request->notes,
                    'current_step' => 'completed',
                ]);
                break;
        }
        
        // Recalcular progreso
        $visaProcess->update([
            'progress_percentage' => $visaProcess->getProgressPercentage(),
        ]);
        
        return redirect()->back()->with('success', 'Paso actualizado correctamente');
    }
    
    /**
     * Calendario de citas consulares
     */
    public function calendar()
    {
        $appointments = VisaProcess::with('user')
            ->where('consular_appointment_scheduled', true)
            ->whereNotNull('consular_appointment_date')
            ->orderBy('consular_appointment_date')
            ->get()
            ->map(function($visa) {
                return [
                    'id' => $visa->id,
                    'title' => $visa->user->name,
                    'start' => $visa->consular_appointment_date->toIso8601String(),
                    'url' => route('admin.visa.timeline', $visa->user_id),
                    'backgroundColor' => '#4e73df',
                ];
            });
        
        return view('admin.visa.calendar', compact('appointments'));
    }
    
    /**
     * Actualización masiva de estados
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'visa_process_ids' => 'required|array',
            'action' => 'required|string',
            'value' => 'nullable',
        ]);
        
        $visaProcesses = VisaProcess::whereIn('id', $request->visa_process_ids)->get();
        
        foreach ($visaProcesses as $visaProcess) {
            // Aplicar la acción según el tipo
            // Esto se puede expandir según necesidades
        }
        
        return redirect()->back()->with('success', 'Procesos actualizados correctamente');
    }
    
    /**
     * Upload de documento
     */
    public function uploadDocument(Request $request, $userId)
    {
        $request->validate([
            'document_type' => 'required|in:passport,visa_photo,ds160,ds2019,sevis_receipt,consular_fee_receipt',
            'file' => 'required|file|max:10240',
        ]);
        
        $visaProcess = VisaProcess::where('user_id', $userId)->firstOrFail();
        
        $filePath = $request->file('file')->store('visa-documents', 'public');
        
        $field = $request->document_type . '_path';
        $visaProcess->update([
            $field => $filePath,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Documento subido correctamente',
            'path' => $filePath,
        ]);
    }
    
    /**
     * Descargar documento
     */
    public function downloadDocument($userId, $type)
    {
        $visaProcess = VisaProcess::where('user_id', $userId)->firstOrFail();
        
        $field = $type . '_path';
        $filePath = $visaProcess->$field;
        
        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'Documento no encontrado');
        }
        
        return Storage::disk('public')->download($filePath);
    }
}
