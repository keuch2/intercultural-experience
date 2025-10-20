<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use Carbon\Carbon;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/programs",
     *     tags={"Programs"},
     *     summary="Listar programas disponibles",
     *     description="Obtiene la lista de todos los programas activos disponibles para aplicación",
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoría principal",
     *         required=false,
     *         @OA\Schema(type="string", enum={"IE", "YFU"})
     *     ),
     *     @OA\Parameter(
     *         name="subcategory",
     *         in="query",
     *         description="Filtrar por subcategoría",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="country",
     *         in="query",
     *         description="Filtrar por país destino",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de programas",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Programas obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Program")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        // Get only active IE programs for mobile app (YFU programs are not used in mobile)
        $programs = Program::where('is_active', true)
                          ->where('main_category', 'IE')
                          ->get();
        
        // Transform the programs to match the mobile app's expected format
        $transformedPrograms = $programs->map(function ($program) {
            return [
                'id' => $program->id,
                'name' => $program->name,
                'description' => $program->description,
                'location' => $program->country,
                'start_date' => $program->start_date ? $program->start_date->toDateString() : null,
                'end_date' => $program->end_date ? $program->end_date->toDateString() : null,
                'cost' => $program->cost ?? 0,
                'image_url' => $program->image_url,
                'capacity' => $program->capacity ?? 0,
                'available_spots' => $program->available_spots ?? 0,
                'duration' => $program->duration,
                'credits' => $program->credits,
                'application_deadline' => $program->application_deadline ? $program->application_deadline->toDateString() : null,
                'status' => $program->is_active ? 'open' : 'closed',
                'created_at' => $program->created_at,
                'updated_at' => $program->updated_at
            ];
        });
        
        return response()->json($transformedPrograms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Admin functionality to create a program
        // Requires validation and authorization
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $program = Program::findOrFail($id);
        
        // Only allow IE programs in mobile app
        if ($program->main_category !== 'IE') {
            return response()->json(['message' => 'Este programa no está disponible en la aplicación móvil.'], 403);
        }
        
        if (!$program->is_active && !auth()->user()?->hasRole('admin')) {
            return response()->json(['message' => 'Este programa no está disponible actualmente.'], 403);
        }
        
        // Transform the program to match the mobile app's expected format
        $transformedProgram = [
            'id' => $program->id,
            'name' => $program->name,
            'description' => $program->description,
            'location' => $program->country,
            'start_date' => $program->start_date ? $program->start_date->toDateString() : null,
            'end_date' => $program->end_date ? $program->end_date->toDateString() : null,
            'cost' => $program->cost ?? 0,
            'image_url' => $program->image_url,
            'capacity' => $program->capacity ?? 0,
            'available_spots' => $program->available_spots ?? 0,
            'duration' => $program->duration,
            'credits' => $program->credits,
            'application_deadline' => $program->application_deadline ? $program->application_deadline->toDateString() : null,
            'status' => $program->is_active ? 'open' : 'closed',
            'created_at' => $program->created_at,
            'updated_at' => $program->updated_at,
            // Additional details for the program detail view
            'details' => [
                'prerequisites' => 'Requisitos del programa disponibles en el portal',
                'program_type' => $program->subcategory ?? $program->category,
                'main_category' => $program->main_category,
                'subcategory' => $program->subcategory,
                'coordinator' => 'Coordinador de Programas IE',
                'coordinator_email' => 'coordinator@interculturalexperience.com',
                'syllabus' => $program->description
            ]
        ];
        
        return response()->json($transformedProgram);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Admin functionality to update a program
        // Requires validation and authorization
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Admin functionality to delete a program
        // Requires validation and authorization
    }
}
