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
    public function index()
    {
        // Get only active programs
        $programs = Program::where('is_active', true)->get();
        
        // Transform the programs to match the mobile app's expected format
        $transformedPrograms = $programs->map(function ($program) {
            // Create placeholder dates for testing
            $startDate = Carbon::now()->addWeeks(2);
            $endDate = Carbon::now()->addWeeks(6);
            
            return [
                'id' => $program->id,
                'name' => $program->name,
                'description' => $program->description,
                'location' => $program->country, // Map country to location
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'cost' => rand(1000, 5000), // Random cost for demo
                'image_url' => "https://source.unsplash.com/random/800x600?{$program->country}", // Generate random image based on country
                'capacity' => 20,
                'available_spots' => rand(1, 10),
                'duration' => $startDate->diffInWeeks($endDate) . ' semanas',
                'credits' => rand(3, 6),
                'application_deadline' => $startDate->subWeeks(1)->toDateString(),
                'status' => 'open',
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
        
        if (!$program->is_active && !auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Este programa no está disponible actualmente.'], 403);
        }
        
        // Create placeholder dates for testing
        $startDate = Carbon::now()->addWeeks(2);
        $endDate = Carbon::now()->addWeeks(6);
        
        // Transform the program to match the mobile app's expected format
        $transformedProgram = [
            'id' => $program->id,
            'name' => $program->name,
            'description' => $program->description,
            'location' => $program->country, // Map country to location
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'cost' => rand(1000, 5000), // Random cost for demo
            'image_url' => "https://source.unsplash.com/random/800x600?{$program->country}", // Generate random image based on country
            'capacity' => 20,
            'available_spots' => rand(1, 10),
            'duration' => $startDate->diffInWeeks($endDate) . ' semanas',
            'credits' => rand(3, 6),
            'application_deadline' => $startDate->subWeeks(1)->toDateString(),
            'status' => 'open',
            'created_at' => $program->created_at,
            'updated_at' => $program->updated_at,
            // Additional details for the program detail view
            'details' => [
                'prerequisites' => 'Nivel de idioma B1, promedio académico mínimo 3.0/5.0',
                'program_type' => $program->category,
                'coordinator' => 'Dr. ' . ucfirst(str_shuffle('abcdefghijklmnopqrstuvwxyz')) . ' ' . ucfirst(str_shuffle('abcdefghijklmnopqrstuvwxyz')),
                'coordinator_email' => 'coordinator@exchange.edu',
                'syllabus' => 'El programa incluye actividades culturales, visitas a museos y empresas locales, clases de idioma y cursos regulares en la universidad anfitriona.'
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
