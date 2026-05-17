<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Program;

/**
 * Endpoints públicos (sin auth) para que visitantes exploren la oferta
 * promocional de programas desde la app móvil. Cualquier programa activo
 * de la categoría IE se lista; el flag `is_available_in_app` indica si
 * el usuario puede postularse desde la app (V1: solo Au Pair) o si debe
 * contactar por WhatsApp.
 */
class PublicProgramController extends Controller
{
    public function index()
    {
        $programs = Program::active()
            ->ie()
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $programs->map(fn ($p) => $this->transform($p))->values(),
        ]);
    }

    public function show(string $id)
    {
        $program = Program::active()->ie()->find($id);

        if (! $program) {
            return response()->json([
                'status' => 'error',
                'message' => 'Programa no encontrado o no disponible.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->transform($program, withDetail: true),
        ]);
    }

    private function transform(Program $program, bool $withDetail = false): array
    {
        $base = [
            'id' => $program->id,
            'name' => $program->name,
            'description' => $program->description,
            'country' => $program->country,
            'location' => $program->country,
            'main_category' => $program->main_category,
            'subcategory' => $program->subcategory,
            'image_url' => $program->image_url,
            'duration' => $program->duration,
            'is_available_in_app' => $program->is_available_in_app,
        ];

        if ($withDetail) {
            $base['start_date'] = $program->start_date?->toDateString();
            $base['end_date'] = $program->end_date?->toDateString();
            $base['application_deadline'] = $program->application_deadline?->toDateString();
            $base['capacity'] = $program->capacity;
            $base['credits'] = $program->credits;
            $base['cost'] = $program->cost;
        }

        return $base;
    }
}
