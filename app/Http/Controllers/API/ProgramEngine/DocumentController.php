<?php

namespace App\Http\Controllers\API\ProgramEngine;

use App\Http\Controllers\API\ProgramEngine\Concerns\ResolvesProgramProcess;
use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramDocument;
use App\Services\ProgramEngine\DocumentService;
use App\Services\ProgramEngine\Exceptions\DocumentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 *  GET    /api/programs/{p}/documents?group=
 *  POST   /api/programs/{p}/documents        multipart: document_type, files[]
 *  DELETE /api/programs/{p}/documents/{id}
 *  GET    /api/programs/{p}/documents/{id}/download
 */
class DocumentController extends Controller
{
    use ResolvesProgramProcess;

    public function __construct(private readonly DocumentService $documents) {}

    public function index(Request $request, Program $engineProgram)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }

        if (! $process->applicantApproved()) {
            return response()->json(['status' => 'success', 'data' => [], 'locked' => true, 'reason' => 'pending_approval']);
        }

        $group = $request->query('group') ?: $request->query('stage');

        return response()->json(['status' => 'success', 'data' => $this->documents->describe($process, $group ?: null)]);
    }

    public function store(Request $request, Program $engineProgram)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }

        $validator = Validator::make($request->all(), [
            'document_type' => ['required', 'string'],
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['required', 'file'],
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $created = $this->documents->store($process, $request->input('document_type'), $request->file('files'), 'participant', $request->user());
        } catch (DocumentException $e) {
            return response()->json(['status' => 'error', 'code' => $e->errorCode, 'message' => $e->getMessage()], $e->status);
        }

        return response()->json(['status' => 'success', 'data' => array_map(fn ($d) => $this->documents->serialize($d), $created)], 201);
    }

    public function destroy(Request $request, Program $engineProgram, string $id)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }
        $doc = ProgramDocument::where('program_process_id', $process->id)->find($id);
        if (! $doc) {
            return response()->json(['status' => 'error', 'message' => 'Documento no encontrado.'], 404);
        }

        try {
            $this->documents->delete($doc, $request->user(), null, byParticipant: true);
        } catch (DocumentException $e) {
            return response()->json(['status' => 'error', 'code' => $e->errorCode, 'message' => $e->getMessage()], $e->status);
        }
        if ($doc->file_path && $this->documents->disk()->exists($doc->file_path)) {
            $this->documents->disk()->delete($doc->file_path);
        }

        return response()->json(['status' => 'success']);
    }

    public function download(Request $request, Program $engineProgram, string $id)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }
        $doc = ProgramDocument::where('program_process_id', $process->id)->find($id);
        if (! $doc || ! $doc->file_path || ! $this->documents->disk()->exists($doc->file_path)) {
            return response()->json(['status' => 'error', 'message' => 'Archivo no disponible.'], 404);
        }

        return $this->documents->disk()->download($doc->file_path, $doc->original_filename ?? basename($doc->file_path));
    }
}
