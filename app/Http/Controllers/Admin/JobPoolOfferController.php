<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPoolAssignment;
use App\Models\JobPoolOffer;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Services\ProgramEngine\Exceptions\JobPoolException;
use App\Services\ProgramEngine\JobPoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Pool de Ofertas Laborales (admin IE): CRUD de ofertas, PDF, pausar/reactivar/cerrar,
 * asignaciones (liberar, reasignar, asignar manualmente) e historial.
 */
class JobPoolOfferController extends Controller
{
    public function __construct(private readonly JobPoolService $pool) {}

    public function index(Request $request, Program $program)
    {
        $this->assertModule($program);
        $query = JobPoolOffer::forProgram($program)->with('sponsor')->withCount(['activeAssignments'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $request->status === 'exhausted'
                ? $query->where('status', 'active')->where('positions_available', 0)
                : $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('job_title', 'like', "%{$s}%")->orWhere('employer_name', 'like', "%{$s}%")->orWhere('city', 'like', "%{$s}%")->orWhere('state', 'like', "%{$s}%"));
        }

        $offers = $query->paginate(20)->withQueryString();
        $stats = [
            'total' => JobPoolOffer::forProgram($program)->count(),
            'active' => JobPoolOffer::forProgram($program)->selectable()->count(),
            'exhausted' => JobPoolOffer::forProgram($program)->where('status', 'active')->where('positions_available', 0)->count(),
            'positions_available' => (int) JobPoolOffer::forProgram($program)->where('status', 'active')->sum('positions_available'),
            'assigned' => JobPoolAssignment::active()->whereHas('offer', fn ($q) => $q->where('program_id', $program->id))->count(),
        ];

        return view('admin.job-pool.index', compact('program', 'offers', 'stats'));
    }

    public function create(Program $program)
    {
        $this->assertModule($program);

        return view('admin.job-pool.form', ['program' => $program, 'offer' => null, 'sponsors' => $this->sponsorOptions()]);
    }

    public function store(Request $request, Program $program)
    {
        $this->assertModule($program);
        $data = $this->validateOffer($request, true);
        $offer = $this->pool->publish($program, $data, $request->file('pdf'), $request->user(), $request->file('image'));

        return redirect()->route('admin.program.job-pool.show', [$program->slug, $offer->id])->with('success', 'Oferta publicada. Los participantes habilitados fueron notificados.');
    }

    public function show(Program $program, JobPoolOffer $offer)
    {
        $this->assertOwned($program, $offer);
        $offer->load(['assignments.process.user', 'assignments.assignedBy', 'assignments.releasedBy', 'events.actor', 'creator']);

        return view('admin.job-pool.show', [
            'program' => $program,
            'offer' => $offer,
            'eligible' => $this->pool->eligibleProcesses($program),
        ]);
    }

    public function edit(Program $program, JobPoolOffer $offer)
    {
        $this->assertOwned($program, $offer);

        return view('admin.job-pool.form', ['program' => $program, 'offer' => $offer, 'sponsors' => $this->sponsorOptions($offer->sponsor_id)]);
    }

    public function update(Request $request, Program $program, JobPoolOffer $offer)
    {
        $this->assertOwned($program, $offer);
        $data = $this->validateOffer($request, false);
        try {
            $this->pool->update($offer, $data, $request->file('pdf'), $request->user(), $request->file('image'));
        } catch (JobPoolException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.program.job-pool.show', [$program->slug, $offer->id])->with('success', 'Oferta actualizada.');
    }

    public function pause(Request $request, Program $program, JobPoolOffer $offer)
    {
        $this->assertOwned($program, $offer);
        $this->pool->pause($offer, $request->user());

        return back()->with('success', 'Oferta pausada. Dejó de mostrarse en la app.');
    }

    public function reactivate(Request $request, Program $program, JobPoolOffer $offer)
    {
        $this->assertOwned($program, $offer);
        $this->pool->reactivate($offer, $request->user());

        return back()->with('success', 'Oferta reactivada.');
    }

    public function close(Request $request, Program $program, JobPoolOffer $offer)
    {
        $this->assertOwned($program, $offer);
        $this->pool->close($offer, $request->user());

        return back()->with('success', 'Oferta cerrada.');
    }

    public function destroy(Request $request, Program $program, JobPoolOffer $offer)
    {
        $this->assertOwned($program, $offer);
        try {
            $this->pool->delete($offer, $request->user());
        } catch (JobPoolException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.program.job-pool.index', $program->slug)->with('success', 'Oferta eliminada.');
    }

    public function pdf(Program $program, JobPoolOffer $offer)
    {
        $this->assertOwned($program, $offer);
        abort_unless($offer->hasPdf() && Storage::disk(JobPoolService::DISK)->exists($offer->pdf_path), 404);

        return Storage::disk(JobPoolService::DISK)->download($offer->pdf_path, $offer->pdf_original_filename ?? 'oferta.pdf');
    }

    // ── Asignaciones ───────────────────────────────────────────────────
    /** Asignación manual por IE a un participante habilitado. */
    public function assign(Request $request, Program $program, JobPoolOffer $offer)
    {
        $this->assertOwned($program, $offer);
        $data = $request->validate(['process_id' => 'required|integer']);
        $process = ProgramProcess::forProgram($program)->findOrFail($data['process_id']);
        try {
            $this->pool->select($offer, $process, $request->user(), force: true);
        } catch (JobPoolException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Oferta asignada a {$process->user?->name}.");
    }

    public function release(Request $request, Program $program, JobPoolOffer $offer, JobPoolAssignment $assignment)
    {
        $this->assertOwned($program, $offer);
        abort_unless($assignment->job_pool_offer_id === $offer->id, 404);
        $request->validate(['reason' => 'nullable|string|max:500']);
        try {
            $this->pool->release($assignment, $request->user(), $request->input('reason'));
        } catch (JobPoolException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Asignación liberada. El cupo volvió a estar disponible y el participante fue notificado.');
    }

    public function reassign(Request $request, Program $program, JobPoolOffer $offer, JobPoolAssignment $assignment)
    {
        $this->assertOwned($program, $offer);
        abort_unless($assignment->job_pool_offer_id === $offer->id, 404);
        $data = $request->validate(['to_process_id' => 'required|integer', 'reason' => 'nullable|string|max:500']);
        $to = ProgramProcess::forProgram($program)->findOrFail($data['to_process_id']);
        try {
            $this->pool->reassign($assignment, $to, $request->user(), $data['reason'] ?? null);
        } catch (JobPoolException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Oferta reasignada a {$to->user?->name}.");
    }

    // ── Helpers ────────────────────────────────────────────────────────
    /** Sponsors activos para el desplegable (más el ya asignado aunque esté inactivo). */
    private function sponsorOptions(?int $currentId = null)
    {
        return \App\Models\Sponsor::query()->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $currentId))->orderBy('name')->get(['id', 'name', 'code', 'is_active']);
    }

    private function validateOffer(Request $request, bool $creating): array
    {
        return $request->validate([
            'job_title' => 'required|string|max:150',
            'sponsor_id' => 'nullable|exists:sponsors,id',
            'requirements' => 'nullable|string|max:2000',
            'employer_name' => 'required|string|max:255',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'positions_total' => 'required|integer|min:1|max:500',
            // Al crear no se acepta una fecha ya vencida; al editar sí (p. ej. para cerrar/documentar)
            'application_deadline' => ['required', 'date', $creating ? 'after_or_equal:today' : 'date'],
            'pdf' => [$creating ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:20480'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'notes' => 'nullable|string|max:1000',
        ], ['application_deadline.after_or_equal' => 'La fecha límite para postular no puede ser anterior a hoy.'], ['job_title' => 'puesto laboral', 'requirements' => 'requisitos del puesto', 'employer_name' => 'empleador', 'positions_total' => 'posiciones', 'application_deadline' => 'fecha límite para postular', 'pdf' => 'PDF de la oferta', 'image' => 'imagen / flyer de la oferta']);
    }

    private function assertModule(Program $program): void
    {
        abort_unless($program->engine_enabled && $program->hasModule('job_pool'), 404, 'Este programa no tiene habilitado el Pool de Ofertas.');
    }

    private function assertOwned(Program $program, JobPoolOffer $offer): void
    {
        $this->assertModule($program);
        abort_unless((int) $offer->program_id === (int) $program->id, 404);
    }
}
