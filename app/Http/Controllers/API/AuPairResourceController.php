<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AuPairResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * GET /api/au-pair/resources               → recursos activos para participantes
 * GET /api/au-pair/resources/{id}/download → descarga el archivo
 *
 * Los recursos son globales (no scoped al user), pero requieren auth.
 */
class AuPairResourceController extends Controller
{
    public function index(Request $request)
    {
        $resources = AuPairResource::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $resources->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'description' => $r->description,
                'icon' => $r->icon,
                'file_type' => $r->file_type,
                'file_size_formatted' => $r->file_size_formatted,
                'external_url' => $r->external_url,
                'download_url' => $r->hasFile() ? url("/api/au-pair/resources/{$r->id}/download") : null,
            ])->values(),
        ]);
    }

    public function download(Request $request, string $id)
    {
        $resource = AuPairResource::where('is_active', true)->find($id);
        if (! $resource || ! $resource->hasFile()) {
            return response()->json(['status' => 'error', 'message' => 'Recurso no disponible.'], 404);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($resource->file_path)) {
            return response()->json(['status' => 'error', 'message' => 'Archivo no encontrado.'], 404);
        }

        return $disk->download($resource->file_path, $resource->original_filename ?? basename($resource->file_path));
    }
}
