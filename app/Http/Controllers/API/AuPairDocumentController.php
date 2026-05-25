<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Concerns\ResolvesAuPairProcess;
use App\Models\Application;
use App\Models\AuPairDocument;
use App\Models\AuPairProcess;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Endpoints documentos Au Pair (scoped al user autenticado).
 *
 *  GET    /api/au-pair/documents?stage=admission|application_payment1|application_payment2|visa
 *  POST   /api/au-pair/documents       multipart: document_type, stage, files[]
 *  DELETE /api/au-pair/documents/{id}  (solo pending + uploaded_by_type=participant)
 *  GET    /api/au-pair/documents/{id}/download
 *
 * Reglas:
 *  - Solo opera sobre el AuPairProcess del user. Si no tiene → 404.
 *  - Documentos uploaded_by=staff (DS-160, DS-2019, etc.) NO se pueden subir desde mobile.
 *  - El status nuevo siempre arranca en 'pending' (admin revisa desde web).
 *  - Soporta multi-file: child_photos (min 12), certifications (min 5), character_ref (2), childcare_ref (3).
 */
class AuPairDocumentController extends Controller
{
    use ResolvesAuPairProcess;

    private const MAX_FILE_MB = 15;
    private const MAX_VIDEO_MB = 200;
    private const ALLOWED_MIMES = ['jpeg', 'jpg', 'png', 'pdf', 'mp4', 'mov'];

    public function index(Request $request)
    {
        [$process, $err] = $this->resolveProcess($request);
        if ($err) return $err;

        $stage = $request->query('stage');
        $allStages = ['admission', 'application_payment1', 'application_payment2', 'visa'];
        if ($stage && ! in_array($stage, $allStages, true)) {
            return response()->json(['status' => 'error', 'message' => 'Stage inválido.'], 422);
        }

        $stages = $stage ? [$stage] : $allStages;
        $items = [];
        foreach ($stages as $s) {
            $items = array_merge($items, $this->describeStage($process, $s));
        }

        return response()->json(['status' => 'success', 'data' => $items]);
    }

    public function store(Request $request)
    {
        [$process, $err] = $this->resolveProcess($request);
        if ($err) return $err;

        $allStages = ['admission', 'application_payment1', 'application_payment2', 'visa'];
        $types = array_keys(AuPairDocument::documentTypes());

        $validator = Validator::make($request->all(), [
            'document_type' => ['required', 'string', Rule::in($types)],
            'stage' => ['required', 'string', Rule::in($allStages)],
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['required', 'file', 'mimes:' . implode(',', self::ALLOWED_MIMES)],
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $type = $request->input('document_type');
        $stage = $request->input('stage');
        $cfg = AuPairDocument::documentTypes()[$type] ?? null;

        if (! $cfg || $cfg['stage'] !== $stage) {
            return response()->json(['status' => 'error', 'message' => 'Stage no coincide con el tipo de documento.'], 422);
        }

        if (($cfg['uploaded_by'] ?? 'participant') === 'staff') {
            return response()->json(['status' => 'error', 'message' => 'Este documento lo carga el equipo IE, no se puede subir desde la app.'], 403);
        }

        $created = [];
        foreach ($request->file('files') as $file) {
            // Validar tamaño según tipo
            $isVideo = in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'mov'], true);
            $maxBytes = ($isVideo ? self::MAX_VIDEO_MB : self::MAX_FILE_MB) * 1024 * 1024;
            if ($file->getSize() > $maxBytes) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Archivo demasiado grande. Máximo ' . ($isVideo ? self::MAX_VIDEO_MB : self::MAX_FILE_MB) . 'MB.',
                ], 422);
            }

            $path = $file->store("au_pair/{$process->id}/{$stage}", 'public');
            $doc = AuPairDocument::create([
                'au_pair_process_id' => $process->id,
                'document_type' => $type,
                'stage' => $stage,
                'uploaded_by_type' => 'participant',
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'status' => 'pending',
                'is_required' => (bool) ($cfg['required'] ?? false),
                'min_count' => $cfg['min_count'] ?? null,
                'sort_order' => $cfg['sort'] ?? 0,
            ]);
            $created[] = $this->serializeDocRecord($doc);
        }

        return response()->json(['status' => 'success', 'data' => $created], 201);
    }

    public function destroy(Request $request, string $id)
    {
        [$process, $err] = $this->resolveProcess($request);
        if ($err) return $err;

        $doc = AuPairDocument::where('au_pair_process_id', $process->id)->find($id);
        if (! $doc) {
            return response()->json(['status' => 'error', 'message' => 'Documento no encontrado.'], 404);
        }

        if ($doc->status !== 'pending' || $doc->uploaded_by_type !== 'participant') {
            return response()->json(['status' => 'error', 'message' => 'Solo se pueden eliminar documentos pendientes que subiste vos.'], 403);
        }

        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();

        return response()->json(['status' => 'success'], 200);
    }

    public function download(Request $request, string $id)
    {
        [$process, $err] = $this->resolveProcess($request);
        if ($err) return $err;

        $doc = AuPairDocument::where('au_pair_process_id', $process->id)->find($id);
        if (! $doc || ! $doc->file_path) {
            return response()->json(['status' => 'error', 'message' => 'Documento no encontrado.'], 404);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($doc->file_path)) {
            return response()->json(['status' => 'error', 'message' => 'Archivo no disponible.'], 404);
        }

        return $disk->download($doc->file_path, $doc->original_filename ?? basename($doc->file_path));
    }

    /**
     * Devuelve un item por tipo de documento del stage, combinando config + uploads.
     */
    private function describeStage(AuPairProcess $process, string $stage): array
    {
        $cfgs = AuPairDocument::documentTypesForStage($stage);
        $docs = $process->documents()->where('stage', $stage)->orderBy('created_at')->get();

        $items = [];
        foreach ($cfgs as $type => $cfg) {
            $forType = $docs->where('document_type', $type)->values();
            $minCount = $cfg['min_count'] ?? null;
            $items[] = [
                'document_type' => $type,
                'label' => $cfg['label'],
                'stage' => $stage,
                'required' => (bool) ($cfg['required'] ?? false),
                'min_count' => $minCount,
                'allow_multiple' => ($minCount !== null && $minCount > 1) || !empty($cfg['allow_multiple']),
                'uploaded_by' => $cfg['uploaded_by'] ?? 'participant',
                'count' => $forType->count(),
                'status' => $this->aggregateStatus($forType),
                'files' => $forType->map(fn ($d) => $this->serializeDocRecord($d))->all(),
            ];
        }

        // Ordenar por sort
        usort($items, fn ($a, $b) => ($cfgs[$a['document_type']]['sort'] ?? 0) <=> ($cfgs[$b['document_type']]['sort'] ?? 0));
        return $items;
    }

    /**
     * Si hay al menos un aprobado → 'approved'. Si todos rechazados → 'rejected'.
     * Si hay alguno pending → 'pending'. Si no hay → 'missing'.
     */
    private function aggregateStatus($docs): string
    {
        if ($docs->isEmpty()) return 'missing';
        if ($docs->contains(fn ($d) => $d->status === 'approved')) return 'approved';
        if ($docs->contains(fn ($d) => $d->status === 'pending')) return 'pending';
        return 'rejected';
    }

    private function serializeDocRecord(AuPairDocument $d): array
    {
        return [
            'id' => $d->id,
            'document_type' => $d->document_type,
            'stage' => $d->stage,
            'status' => $d->status,
            'status_label' => $d->status_label,
            'original_filename' => $d->original_filename,
            'file_size' => $d->file_size,
            'file_size_formatted' => $d->file_size_formatted,
            'rejection_reason' => $d->rejection_reason,
            'reviewed_at' => optional($d->reviewed_at)->toIso8601String(),
            'uploaded_at' => optional($d->created_at)->toIso8601String(),
            'uploaded_by_type' => $d->uploaded_by_type,
            'download_url' => route('api.au-pair.documents.download', ['id' => $d->id]),
        ];
    }
}
