<?php

namespace App\Http\Controllers\API\ProgramEngine;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function index(Request $request, Program $engineProgram)
    {
        $resources = $engineProgram->resources()->active()->get();

        return response()->json(['status' => 'success', 'data' => $resources->map(fn (ProgramResource $r) => [
            'id' => $r->id, 'title' => $r->title, 'description' => $r->description, 'icon' => $r->icon, 'file_type' => $r->file_type,
            'file_size_formatted' => $r->file_size_formatted, 'external_url' => $r->external_url,
            'download_url' => $r->hasFile() ? route('api.programs.resources.download', ['engineProgram' => $engineProgram->slug, 'id' => $r->id]) : null,
        ])->values()]);
    }

    public function download(Request $request, Program $engineProgram, string $id)
    {
        $resource = $engineProgram->resources()->active()->find($id);
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
