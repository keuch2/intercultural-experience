<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPoolOffer;
use App\Models\Program;
use App\Models\ProgramEnglishTest;
use App\Models\ProgramProcess;
use App\Models\Sponsor;
use App\Models\User;
use App\Services\ProgramEngine\ProgramDefinition;
use App\Services\ProgramEngine\StageEvaluator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Informes y planillas por programa del motor (spec W&T: temporada, universidad,
 * carrera, estado documental, inglés, oferta laboral, sponsor, visa, viaje, pagos).
 * Export CSV con el mismo patrón que AdminParticipantController::export().
 */
class ProgramReportController extends Controller
{
    public const MAX_ROWS = 1000;

    public function __construct(private readonly StageEvaluator $evaluator) {}

    public function index(Request $request, Program $program)
    {
        $this->assertEngine($program);
        $definition = ProgramDefinition::for($program);
        $rows = $this->rows($request, $program, $definition);

        return view('admin.program-reports.index', [
            'program' => $program,
            'definition' => $definition,
            'rows' => $rows->take(self::MAX_ROWS),
            'total' => $rows->count(),
            'truncated' => $rows->count() > self::MAX_ROWS,
            'summary' => $this->summary($rows, $definition),
            'options' => $this->filterOptions($program, $definition),
        ]);
    }

    public function export(Request $request, Program $program)
    {
        $this->assertEngine($program);
        $definition = ProgramDefinition::for($program);
        $rows = $this->rows($request, $program, $definition);
        $gateLabels = $definition->gates()->pluck('label')->all();

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_merge([
            'ID', 'Participante', 'Email', 'Teléfono', 'CI', 'Universidad', 'Carrera', 'Año/Semestre', 'Temporada',
            'Etapa', 'Estado proceso', 'Postulante aprobado', 'Docs requeridos aprobados (etapa actual)', 'Docs pendientes', 'Docs rechazados',
            'Estado documental', 'Nivel inglés', 'Oferta laboral', 'Ciudad oferta', 'Estado oferta', 'Sponsor', 'Estado placement', 'SEVIS', 'DS-2019',
            'Resultado visa', 'Fecha cita', 'Salida', 'Llegada USA', 'Total pagado', 'Costo programa', 'Inscripción',
        ], $gateLabels, ['Última actualización']));

        foreach ($rows as $r) {
            fputcsv($handle, array_merge([
                $r['id'], $r['name'], $r['email'], $r['phone'], $r['ci'], $r['university'], $r['career'], $r['academic_year'], $r['season'],
                $r['stage'], $r['status'], $r['approved'] ? 'Sí' : 'No', "{$r['docs_approved']}/{$r['docs_required']}", $r['docs_pending'], $r['docs_rejected'],
                $r['doc_status_label'], $r['english'] ?? '', $r['offer'] ?? '', $r['offer_location'] ?? '', $r['offer_status'] ?? '', $r['sponsor'] ?? '', $r['placement_status'] ?? '',
                $r['sevis'] ?? '', $r['ds2019'] ?? '', $r['visa_result'] ?? '', $r['appointment'] ?? '', $r['departure'] ?? '', $r['arrival'] ?? '',
                $r['paid'], $r['cost'], $r['enrollment_date'] ?? '',
            ], array_map(fn ($ok) => $ok ? 'Verificado' : 'Pendiente', $r['gates']), [$r['updated_at']]));
        }

        rewind($handle);
        $csv = "\xEF\xBB\xBF".stream_get_contents($handle); // BOM para Excel
        fclose($handle);

        $filename = ($program->slug ?: 'programa').'_informe_'.date('Y-m-d_His').'.csv';

        return response($csv, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    // ── Query + filas ──────────────────────────────────────────────────
    private function baseQuery(Request $request, Program $program): Builder
    {
        $q = ProgramProcess::forProgram($program)
            ->with(['user', 'application.payments', 'documents', 'englishTests', 'gates', 'visaProcess', 'placement.sponsor', 'activeJobAssignment.offer']);

        if ($request->filled('season')) {
            $q->where('season', $request->season);
        }
        if ($request->filled('stage')) {
            $q->where('current_stage_key', $request->stage);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('approval')) {
            $q->whereHas('application', fn ($a) => $a->where('status', $request->approval === 'approved' ? '=' : '!=', 'approved'));
        }
        if ($request->filled('university')) {
            $q->whereHas('user', fn ($u) => $u->where('university', 'like', '%'.$request->university.'%'));
        }
        if ($request->filled('career')) {
            $q->whereHas('user', fn ($u) => $u->where('career', 'like', '%'.$request->career.'%'));
        }
        if ($request->filled('english_level')) {
            $q->whereHas('englishTests', fn ($t) => $t->where('cefr_level', $request->english_level));
        }
        if ($request->filled('offer_id')) {
            $q->whereHas('activeJobAssignment', fn ($a) => $a->where('job_pool_offer_id', $request->offer_id));
        }
        if ($request->filled('sponsor_id')) {
            $q->whereHas('placement', fn ($p) => $p->where('sponsor_id', $request->sponsor_id));
        }
        if ($request->filled('visa_result')) {
            $q->whereHas('visaProcess', fn ($v) => $v->where('interview_result', $request->visa_result));
        }
        if ($request->filled('travel')) {
            $request->travel === 'scheduled'
                ? $q->whereHas('visaProcess', fn ($v) => $v->whereNotNull('departure_datetime'))
                : $q->whereDoesntHave('visaProcess', fn ($v) => $v->whereNotNull('departure_datetime'));
        }
        if ($request->filled('gate')) {
            $verified = $request->input('gate_verified', '1') === '1';
            $q->whereHas('gates', fn ($g) => $g->where('gate_key', $request->gate)->where('is_verified', $verified));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->orWhere('ci_number', 'like', "%{$s}%"));
        }

        return $q->orderBy('created_at');
    }

    private function rows(Request $request, Program $program, ProgramDefinition $definition): Collection
    {
        $rows = $this->baseQuery($request, $program)->get()->map(fn (ProgramProcess $p) => $this->row($p, $definition));

        if ($request->filled('doc_status')) {
            $rows = $rows->where('doc_status', $request->doc_status);
        }

        return $rows->values();
    }

    private function row(ProgramProcess $p, ProgramDefinition $definition): array
    {
        $u = $p->user;
        $stage = $definition->stage($p->current_stage_key);
        $required = $definition->requirements($p->current_stage_key)->where('is_required', true);
        $docs = $p->documents;
        $approvedReq = $required->filter(fn ($r) => $docs->where('requirement_key', $r->key)->where('status', 'approved')->count() >= max(1, (int) $r->min_count))->count();
        $pending = $docs->where('status', 'pending')->count();
        $rejected = $docs->where('status', 'rejected')->count();
        $docStatus = $required->isEmpty() || $approvedReq >= $required->count() ? 'complete' : ($rejected > 0 ? 'rejected' : ($pending > 0 ? 'pending' : 'missing'));

        $best = $p->englishTests->sortByDesc(fn ($t) => array_search($t->cefr_level, ProgramEnglishTest::CEFR_ORDER, true))->first()?->cefr_level;
        $offer = $p->activeJobAssignment?->offer;
        $placement = $p->placement;
        $visa = $p->visaProcess;
        $paid = (float) ($p->application?->payments->where('status', 'verified')->sum(fn ($x) => $x->converted_amount ?? $x->amount) ?? 0);

        return [
            'id' => $p->id,
            'name' => $u?->name, 'email' => $u?->email, 'phone' => $u?->phone, 'ci' => $u?->ci_number,
            'university' => $u?->university, 'career' => $u?->career, 'academic_year' => $u?->academic_year,
            'season' => $p->season, 'stage' => $stage?->label ?? $p->current_stage_key, 'stage_key' => $p->current_stage_key,
            'status' => ['active' => 'Activo', 'completed' => 'Completado', 'cancelled' => 'Cancelado'][$p->status] ?? $p->status,
            'approved' => optional($p->application)->status === 'approved',
            'docs_required' => $required->count(), 'docs_approved' => $approvedReq, 'docs_pending' => $pending, 'docs_rejected' => $rejected,
            'doc_status' => $docStatus,
            'doc_status_label' => ['complete' => 'Completa', 'pending' => 'En revisión', 'rejected' => 'Con rechazos', 'missing' => 'Incompleta'][$docStatus],
            'english' => $best,
            'english_ok' => $best ? ProgramEnglishTest::levelMeets($best, $definition->minEnglishLevel()) : null,
            'offer' => $offer?->headline, 'offer_location' => $offer ? "{$offer->city}, {$offer->state}" : null, 'offer_status' => $offer ? 'Asignada' : null,
            'sponsor' => $placement?->sponsor?->name, 'placement_status' => $placement?->status_label, 'sevis' => $placement?->sevis_number, 'ds2019' => $placement?->ds2019_number,
            'visa_result' => $visa ? (['pending' => 'Pendiente', 'approved' => 'Aprobada', 'denied' => 'Denegada', 'administrative_process' => 'Proceso administrativo'][$visa->interview_result] ?? $visa->interview_result) : null,
            'appointment' => $visa?->appointment_date?->format('Y-m-d'),
            'departure' => $visa?->departure_datetime?->format('Y-m-d H:i'), 'arrival' => $visa?->arrival_usa_datetime?->format('Y-m-d H:i'),
            'paid' => number_format($paid, 2, '.', ''), 'cost' => number_format((float) ($p->application?->total_cost ?? 0), 2, '.', ''),
            'gates' => $definition->gates()->map(fn ($g) => (bool) $p->gates->firstWhere('gate_key', $g->key)?->is_verified)->all(),
            'enrollment_date' => $p->enrollment_date?->format('Y-m-d'),
            'updated_at' => $p->updated_at?->format('Y-m-d H:i'),
            'process_id' => $p->id,
        ];
    }

    private function summary(Collection $rows, ProgramDefinition $definition): array
    {
        return [
            'total' => $rows->count(),
            'by_stage' => $definition->stages()->mapWithKeys(fn ($s) => [$s->label => $rows->where('stage_key', $s->key)->count()])->all(),
            'doc_complete' => $rows->where('doc_status', 'complete')->count(),
            'english_ok' => $rows->where('english_ok', true)->count(),
            'with_offer' => $rows->whereNotNull('offer')->count(),
            'visa_approved' => $rows->where('visa_result', 'Aprobada')->count(),
            'traveling' => $rows->whereNotNull('departure')->count(),
        ];
    }

    private function filterOptions(Program $program, ProgramDefinition $definition): array
    {
        $userIds = ProgramProcess::forProgram($program)->select('user_id');

        return [
            'seasons' => ProgramProcess::forProgram($program)->whereNotNull('season')->distinct()->orderByDesc('season')->pluck('season'),
            'stages' => $definition->stages(),
            'gates' => $definition->gates(),
            'universities' => User::whereIn('id', $userIds)->whereNotNull('university')->distinct()->orderBy('university')->pluck('university'),
            'careers' => User::whereIn('id', $userIds)->whereNotNull('career')->distinct()->orderBy('career')->pluck('career'),
            'offers' => JobPoolOffer::forProgram($program)->orderBy('job_title')->orderBy('employer_name')->get(['id', 'job_title', 'employer_name', 'city', 'state']),
            'sponsors' => Sponsor::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function assertEngine(Program $program): void
    {
        abort_unless($program->engine_enabled, 404);
    }
}
