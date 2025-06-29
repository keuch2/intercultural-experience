<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Program;
use App\Models\ProgramRequisite;
use App\Models\UserProgramRequisite;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $applications = Application::where('user_id', auth()->id())
            ->with(['program'])
            ->orderBy('applied_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $applications
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'program_id' => 'required|exists:programs,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar si el programa está activo
        $program = Program::findOrFail($request->program_id);
        if (!$program->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Este programa no está disponible actualmente.'
            ], 422);
        }
        
        // Verificar si el usuario ya tiene una solicitud para este programa
        $existingApplication = Application::where('user_id', auth()->id())
            ->where('program_id', $request->program_id)
            ->whereIn('status', ['pending', 'in_review'])
            ->first();
            
        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una solicitud activa para este programa.'
            ], 422);
        }
        
        // Crear la solicitud
        $application = new Application();
        $application->user_id = auth()->id();
        $application->program_id = $request->program_id;
        $application->status = 'pending';
        $application->applied_at = now();
        $application->save();
        
        // Crear los requisitos del usuario para esta solicitud
        $this->createUserProgramRequisites($application);
        
        return response()->json([
            'success' => true,
            'message' => 'Solicitud creada correctamente.',
            'data' => $application->load('program')
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $application = Application::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['program', 'documents'])
            ->firstOrFail();
            
        // Obtener el progreso de la solicitud
        $progressPercentage = $application->getProgressPercentage();
        
        // Obtener estadísticas de requisitos
        $requisitesStats = [
            'total' => $application->requisites()->count(),
            'completed' => $application->requisites()->whereIn('status', ['completed', 'verified'])->count(),
            'verified' => $application->requisites()->where('status', 'verified')->count(),
            'pending' => $application->requisites()->where('status', 'pending')->count(),
            'rejected' => $application->requisites()->where('status', 'rejected')->count()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $application,
            'progress' => $progressPercentage,
            'requisites_stats' => $requisitesStats
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // No se permite actualizar solicitudes desde la API móvil
        return response()->json([
            'success' => false,
            'message' => 'Operación no permitida'
        ], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $application = Application::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();
            
        // Solo se pueden cancelar solicitudes pendientes
        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden cancelar solicitudes pendientes.'
            ], 422);
        }
        
        // Eliminar los requisitos de usuario asociados
        $application->requisites()->delete();
        
        // Eliminar los documentos asociados
        $application->documents()->delete();
        
        // Eliminar la solicitud
        $application->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Solicitud cancelada correctamente.'
        ]);
    }
    
    /**
     * Crea los requisitos de usuario para una solicitud nueva.
     *
     * @param  \App\Models\Application  $application
     * @return void
     */
    private function createUserProgramRequisites(Application $application)
    {
        // Obtener todos los requisitos del programa
        $programRequisites = ProgramRequisite::where('program_id', $application->program_id)
            ->orderBy('order')
            ->get();
            
        // Crear un requisito de usuario para cada requisito del programa
        foreach ($programRequisites as $requisite) {
            UserProgramRequisite::create([
                'application_id' => $application->id,
                'program_requisite_id' => $requisite->id,
                'status' => 'pending'
            ]);
        }
    }
    
    /**
     * Marca una solicitud como en revisión (solo para administradores).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function review($id)
    {
        $application = Application::findOrFail($id);
        
        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden revisar solicitudes pendientes.'
            ], 422);
        }
        
        $application->status = 'in_review';
        $application->save();
        
        return response()->json([
            'success' => true,
            'message' => 'La solicitud ha sido marcada como en revisión.',
            'data' => $application
        ]);
    }
    
    /**
     * Aprueba una solicitud (solo para administradores).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        $application = Application::findOrFail($id);
        
        if ($application->status !== 'in_review') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden aprobar solicitudes en revisión.'
            ], 422);
        }
        
        $application->status = 'approved';
        $application->completed_at = now();
        $application->save();
        
        return response()->json([
            'success' => true,
            'message' => 'La solicitud ha sido aprobada.',
            'data' => $application
        ]);
    }
    
    /**
     * Rechaza una solicitud (solo para administradores).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reject($id)
    {
        $application = Application::findOrFail($id);
        
        if ($application->status !== 'in_review') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden rechazar solicitudes en revisión.'
            ], 422);
        }
        
        $application->status = 'rejected';
        $application->completed_at = now();
        $application->save();
        
        return response()->json([
            'success' => true,
            'message' => 'La solicitud ha sido rechazada.',
            'data' => $application
        ]);
    }
}
