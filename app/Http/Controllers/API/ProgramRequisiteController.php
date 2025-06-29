<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\ProgramRequisite;
use App\Models\Application;
use App\Models\UserProgramRequisite;

class ProgramRequisiteController extends Controller
{
    /**
     * Obtiene los requisitos de un programa específico.
     *
     * @param  int  $programId
     * @return \Illuminate\Http\Response
     */
    public function getProgramRequisites($programId)
    {
        $program = Program::findOrFail($programId);
        $requisites = $program->requisites()->orderBy('order')->get();
        
        return response()->json([
            'success' => true,
            'data' => $requisites
        ]);
    }
    
    /**
     * Obtiene los requisitos de la aplicación de un usuario a un programa.
     *
     * @param  int  $applicationId
     * @return \Illuminate\Http\Response
     */
    public function getUserRequisites($applicationId)
    {
        $application = Application::where('id', $applicationId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        $requisites = $application->requisites()
            ->with('programRequisite')
            ->orderBy('created_at')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $requisites,
            'progress' => $application->getProgressPercentage()
        ]);
    }
    
    /**
     * Marca un requisito como completado y sube un archivo si es necesario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $requisiteId
     * @return \Illuminate\Http\Response
     */
    public function completeRequisite(Request $request, $requisiteId)
    {
        $userRequisite = UserProgramRequisite::where('id', $requisiteId)
            ->whereHas('application', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();
            
        // Validar que el requisito no esté ya verificado
        if ($userRequisite->status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Este requisito ya ha sido verificado y no puede ser modificado.'
            ], 422);
        }
        
        // Si es un documento, validar y guardar el archivo
        if ($userRequisite->programRequisite->type === 'document' && $request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB máximo
            ]);
            
            // Guardar el archivo
            $filePath = $request->file('file')->store('requisites/' . $userRequisite->application_id, 'public');
            $userRequisite->file_path = $filePath;
        }
        
        // Actualizar el estado del requisito
        $userRequisite->status = 'completed';
        $userRequisite->completed_at = now();
        $userRequisite->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Requisito completado correctamente.',
            'data' => $userRequisite
        ]);
    }
    
    /**
     * Obtiene el progreso general de la aplicación de un usuario.
     *
     * @param  int  $applicationId
     * @return \Illuminate\Http\Response
     */
    public function getApplicationProgress($applicationId)
    {
        $application = Application::where('id', $applicationId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        $totalRequisites = $application->requisites()->count();
        $completedRequisites = $application->requisites()
            ->whereIn('status', ['completed', 'verified'])
            ->count();
        $verifiedRequisites = $application->requisites()
            ->where('status', 'verified')
            ->count();
        $pendingRequisites = $application->requisites()
            ->where('status', 'pending')
            ->count();
        $rejectedRequisites = $application->requisites()
            ->where('status', 'rejected')
            ->count();
            
        $progressPercentage = $application->getProgressPercentage();
        
        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalRequisites,
                'completed' => $completedRequisites,
                'verified' => $verifiedRequisites,
                'pending' => $pendingRequisites,
                'rejected' => $rejectedRequisites,
                'progress_percentage' => $progressPercentage
            ]
        ]);
    }
}
