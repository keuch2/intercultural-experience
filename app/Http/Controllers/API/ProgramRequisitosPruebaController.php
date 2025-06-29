<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProgramRequisite;
use App\Models\Application;
use App\Models\UserProgramRequisite;
use Illuminate\Http\JsonResponse;

class ProgramRequisitosPruebaController extends Controller
{
    /**
     * Obtener requisitos de un programa sin necesidad de autenticación (para pruebas)
     *
     * @param int $programId
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(int $programId): JsonResponse
    {
        // Verificar si hay requisitos para este programa
        $requisites = ProgramRequisite::where('program_id', $programId)->orderBy('order')->get();
        
        // Mapear propiedades para que coincidan con lo que espera el frontend
        $mappedRequisites = $requisites->map(function($requisite) {
            return [
                'id' => $requisite->id,
                'program_id' => $requisite->program_id,
                'name' => $requisite->name,
                'description' => $requisite->description,
                'type' => $requisite->type,
                'required' => $requisite->is_required, // Mapear is_required a required
                'order' => $requisite->order,
                'created_at' => $requisite->created_at,
                'updated_at' => $requisite->updated_at
            ];
        });
        
        return response()->json($mappedRequisites);
    }
    
    /**
     * Obtener requisitos de una aplicación sin necesidad de autenticación (para pruebas)
     *
     * @param int $applicationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getApplicationRequisites(int $applicationId): JsonResponse
    {
        $application = Application::findOrFail($applicationId);
        
        // Obtener los requisitos del programa asociado a esta aplicación
        $programRequisites = ProgramRequisite::where('program_id', $application->program_id)->orderBy('order')->get();
        
        // Obtener los requisitos completados por el usuario para esta aplicación
        $userRequisites = UserProgramRequisite::where('application_id', $applicationId)->get();
        
        // Mapear requisitos con su estado de completitud
        $mappedRequisites = $programRequisites->map(function($programRequisite) use ($userRequisites) {
            $userRequisite = $userRequisites->where('program_requisite_id', $programRequisite->id)->first();
            
            return [
                'id' => $programRequisite->id,
                'name' => $programRequisite->name,
                'description' => $programRequisite->description,
                'required' => (bool) $programRequisite->is_required,
                'type' => $programRequisite->type,
                'status' => $userRequisite ? $userRequisite->status : 'pending',
                'completed_at' => $userRequisite ? $userRequisite->completed_at : null,
                'comments' => $userRequisite ? $userRequisite->comments : null
            ];
        });
        
        return response()->json($mappedRequisites);
    }
}
