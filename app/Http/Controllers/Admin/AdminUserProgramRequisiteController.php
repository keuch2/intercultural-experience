<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\UserProgramRequisite;
use App\Models\ProgramRequisite;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;

class AdminUserProgramRequisiteController extends Controller
{
    /**
     * Muestra la lista de requisitos de una solicitud.
     *
     * @param  \App\Models\Application  $application
     * @return \Illuminate\Http\Response
     */
    public function index(Application $application)
    {
        $application->load(['user', 'program']);
        $requisites = $application->requisites()->with('programRequisite')->get();
        
        return view('admin.applications.requisites.index', compact('application', 'requisites'));
    }
    
    /**
     * Verifica un requisito completado por el usuario.
     *
     * @param  \App\Models\UserProgramRequisite  $requisite
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request, UserProgramRequisite $requisite)
    {
        $request->validate([
            'observations' => 'nullable|string',
        ]);
        
        $requisite->status = 'verified';
        $requisite->observations = $request->observations;
        $requisite->verified_at = now();
        $requisite->save();
        
        // Crear notificación para el usuario
        Notification::create([
            'user_id' => $requisite->application->user_id,
            'title' => 'Requisito verificado',
            'message' => "Tu requisito '{$requisite->programRequisite->name}' ha sido verificado.",
            'type' => 'requisite_verified',
            'read' => false
        ]);
        
        // Verificar si todos los requisitos están completados para actualizar el estado de la solicitud
        $this->checkApplicationProgress($requisite->application);
        
        return redirect()->back()->with('success', 'Requisito verificado correctamente.');
    }
    
    /**
     * Rechaza un requisito completado por el usuario.
     *
     * @param  \App\Models\UserProgramRequisite  $requisite
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, UserProgramRequisite $requisite)
    {
        $request->validate([
            'observations' => 'required|string',
        ]);
        
        $requisite->status = 'rejected';
        $requisite->observations = $request->observations;
        $requisite->verified_at = now();
        $requisite->save();
        
        // Crear notificación para el usuario
        Notification::create([
            'user_id' => $requisite->application->user_id,
            'title' => 'Requisito rechazado',
            'message' => "Tu requisito '{$requisite->programRequisite->name}' ha sido rechazado. Por favor, revisa las observaciones.",
            'type' => 'requisite_rejected',
            'read' => false
        ]);
        
        return redirect()->back()->with('success', 'Requisito rechazado correctamente.');
    }
    
    /**
     * Marca un requisito como pendiente nuevamente.
     *
     * @param  \App\Models\UserProgramRequisite  $requisite
     * @return \Illuminate\Http\Response
     */
    public function reset(UserProgramRequisite $requisite)
    {
        $requisite->status = 'pending';
        $requisite->observations = null;
        $requisite->completed_at = null;
        $requisite->verified_at = null;
        
        // Si hay un archivo, eliminarlo
        if ($requisite->file_path) {
            Storage::disk('public')->delete($requisite->file_path);
            $requisite->file_path = null;
        }
        
        $requisite->save();
        
        // Crear notificación para el usuario
        Notification::create([
            'user_id' => $requisite->application->user_id,
            'title' => 'Requisito reiniciado',
            'message' => "El requisito '{$requisite->programRequisite->name}' ha sido marcado como pendiente nuevamente.",
            'type' => 'requisite_reset',
            'read' => false
        ]);
        
        return redirect()->back()->with('success', 'Requisito reiniciado correctamente.');
    }
    
    /**
     * Verifica el progreso de una solicitud y actualiza su estado si es necesario.
     *
     * @param  \App\Models\Application  $application
     * @return void
     */
    private function checkApplicationProgress(Application $application)
    {
        $totalRequiredRequisites = $application->program->requisites()
            ->where('is_required', true)
            ->count();
            
        $verifiedRequiredRequisites = $application->requisites()
            ->join('program_requisites', 'program_requisites.id', '=', 'user_program_requisites.program_requisite_id')
            ->where('program_requisites.is_required', true)
            ->where('user_program_requisites.status', 'verified')
            ->count();
            
        // Si todos los requisitos obligatorios están verificados, marcar la solicitud como aprobada
        if ($totalRequiredRequisites > 0 && $totalRequiredRequisites === $verifiedRequiredRequisites) {
            $application->status = 'approved';
            $application->completed_at = now();
            $application->save();
            
            // Crear notificación para el usuario
            Notification::create([
                'user_id' => $application->user_id,
                'title' => '¡Solicitud aprobada!',
                'message' => "Tu solicitud para el programa '{$application->program->name}' ha sido aprobada.",
                'type' => 'application_approved',
                'read' => false
            ]);
        }
    }
}
