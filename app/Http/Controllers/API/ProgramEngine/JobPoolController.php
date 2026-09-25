<?php

namespace App\Http\Controllers\API\ProgramEngine;

use App\Http\Controllers\API\ProgramEngine\Concerns\ResolvesProgramProcess;
use App\Http\Controllers\Controller;
use App\Models\JobPoolAssignment;
use App\Models\JobPoolOffer;
use App\Models\Program;
use App\Services\ProgramEngine\Exceptions\JobPoolException;
use App\Services\ProgramEngine\JobPoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 *  GET  /api/programs/{p}/job-pool/offers            lista ofertas con cupo + mi asignación
 *  GET  /api/programs/{p}/job-pool/offers/{id}/pdf   descarga el PDF de la oferta
 *  POST /api/programs/{p}/job-pool/offers/{id}/select {confirm: true}
 *  GET  /api/programs/{p}/job-pool/assignment
 */
class JobPoolController extends Controller
{
    use ResolvesProgramProcess;

    public function __construct(private readonly JobPoolService $pool) {}

    public function index(Request $request, Program $engineProgram)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }
        if (! $this->pool->canAccess($process)) {
            return response()->json(['status' => 'error', 'code' => 'not_enabled', 'message' => 'Tu acceso al Pool de Ofertas aún no fue habilitado por IE.', 'data' => [], 'access' => false], 403);
        }
        $assignment = $process->activeJobAssignment()->with('offer')->first();

        return response()->json([
            'status' => 'success',
            'access' => true,
            'allow_reselect' => (bool) ($process->moduleAccess('job_pool')['allow_reselect'] ?? false),
            'my_assignment' => $assignment ? $this->serializeAssignment($assignment, $engineProgram) : null,
            'data' => $this->pool->availableFor($process)->map(fn ($o) => $this->serializeOffer($o, $engineProgram))->values(),
        ]);
    }

    public function pdf(Request $request, Program $engineProgram, string $id)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }
        $offer = JobPoolOffer::forProgram($engineProgram)->find($id);
        $mine = $process->activeJobAssignment()->where('job_pool_offer_id', $id)->exists();
        if (! $offer || ! $offer->hasPdf() || (! $mine && ! $this->pool->canAccess($process))) {
            return response()->json(['status' => 'error', 'message' => 'Oferta no disponible.'], 404);
        }
        if (! Storage::disk(JobPoolService::DISK)->exists($offer->pdf_path)) {
            return response()->json(['status' => 'error', 'message' => 'Archivo no disponible.'], 404);
        }

        return Storage::disk(JobPoolService::DISK)->download($offer->pdf_path, $offer->pdf_original_filename ?? 'oferta.pdf');
    }

    public function select(Request $request, Program $engineProgram, string $id)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }
        if (! $request->boolean('confirm')) {
            return response()->json(['status' => 'error', 'code' => 'confirmation_required', 'message' => 'Confirmá la selección de la oferta.'], 422);
        }
        $offer = JobPoolOffer::forProgram($engineProgram)->find($id);
        if (! $offer) {
            return response()->json(['status' => 'error', 'code' => 'not_found', 'message' => 'Oferta no encontrada.'], 404);
        }

        try {
            $assignment = $this->pool->select($offer, $process);
        } catch (JobPoolException $e) {
            return response()->json(['status' => 'error', 'code' => $e->errorCode, 'message' => $e->getMessage()], $e->status);
        }

        return response()->json(['status' => 'success', 'message' => 'Oferta seleccionada. El equipo IE continuará con tu Job Placement.', 'data' => $this->serializeAssignment($assignment->load('offer'), $engineProgram)], 201);
    }

    public function assignment(Request $request, Program $engineProgram)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }
        $assignment = $process->activeJobAssignment()->with('offer')->first();

        return response()->json(['status' => 'success', 'data' => $assignment ? $this->serializeAssignment($assignment, $engineProgram) : null]);
    }

    private function serializeOffer(JobPoolOffer $o, Program $program): array
    {
        return [
            'id' => $o->id,
            'job_title' => $o->job_title,
            'requirements' => $o->requirements,
            'employer_name' => $o->employer_name,
            'sponsor' => $o->sponsor ? ['id' => $o->sponsor->id, 'name' => $o->sponsor->name, 'code' => $o->sponsor->code] : null,
            'state' => $o->state,
            'city' => $o->city,
            'positions_available' => $o->positions_available,
            'positions_total' => $o->positions_total,
            'pdf_url' => $o->hasPdf() ? route('api.programs.job-pool.pdf', ['engineProgram' => $program->slug, 'id' => $o->id]) : null,
            'image_url' => $o->image_url,
            'published_at' => $o->published_at?->toIso8601String(),
            // La app muestra la fecha límite mientras la oferta siga abierta (sin seleccionado)
            'application_deadline' => $o->application_deadline?->toDateString(),
            'deadline_passed' => $o->isDeadlinePassed(),
        ];
    }

    private function serializeAssignment(JobPoolAssignment $a, Program $program): array
    {
        return [
            'id' => $a->id,
            'status' => $a->status,
            'selected_at' => $a->selected_at?->toIso8601String(),
            'offer' => $this->serializeOffer($a->offer, $program),
        ];
    }
}
