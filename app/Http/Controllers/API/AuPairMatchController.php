<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Concerns\ResolvesAuPairProcess;
use App\Models\AuPairMatchExtended;
use Illuminate\Http\Request;

/**
 * GET /api/au-pair/matches      → lista de matches del participante
 * GET /api/au-pair/matches/{id} → detalle host family
 *
 * Read-only en V1: el admin crea/edita matches desde web.
 */
class AuPairMatchController extends Controller
{
    use ResolvesAuPairProcess;

    public function index(Request $request)
    {
        [$process, $err] = $this->resolveProcess($request);
        if ($err) return $err;

        $matches = $process->matchesExtended()->get();
        return response()->json([
            'status' => 'success',
            'data' => $matches->map(fn ($m) => $this->serialize($m, withDetail: false))->values(),
        ]);
    }

    public function show(Request $request, string $id)
    {
        [$process, $err] = $this->resolveProcess($request);
        if ($err) return $err;

        $match = AuPairMatchExtended::where('au_pair_process_id', $process->id)->find($id);
        if (! $match) {
            return response()->json(['status' => 'error', 'message' => 'Match no encontrado.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $this->serialize($match, withDetail: true)]);
    }

    private function serialize(AuPairMatchExtended $m, bool $withDetail): array
    {
        $base = [
            'id' => $m->id,
            'match_type' => $m->match_type,
            'match_type_label' => $m->match_type_label,
            'match_date' => optional($m->match_date)->toDateString(),
            'host_state' => $m->host_state,
            'host_city' => $m->host_city,
            'is_active' => (bool) $m->is_active,
        ];

        if ($withDetail) {
            $base['host_address'] = $m->host_address;
            $base['host_mom_name'] = $m->host_mom_name;
            $base['host_dad_name'] = $m->host_dad_name;
            $base['host_email'] = $m->host_email;
            $base['host_phone'] = $m->host_phone;
            $base['ended_at'] = optional($m->ended_at)->toDateString();
            $base['end_reason'] = $m->end_reason;
        }

        return $base;
    }
}
