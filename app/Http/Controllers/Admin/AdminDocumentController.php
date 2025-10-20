<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = ApplicationDocument::with(['application.user', 'application.program'])
                                  ->orderBy('created_at', 'desc');

        // Filter by program type if specified
        if ($request->filled('program_type')) {
            $query->whereHas('application.program', function ($q) use ($request) {
                $q->where('main_category', $request->program_type);
            });
        }

        // Filter by status if specified
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by document type if specified
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('application.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $documents = $query->paginate(20);
        
        // Get distinct document types for filter
        $documentTypes = ApplicationDocument::distinct('document_type')
                                           ->pluck('document_type')
                                           ->filter()
                                           ->sort();

        $programType = $request->program_type;
        $pageTitle = $programType ? "Documentos {$programType}" : "Gestión de Documentos";

        return view('admin.documents.index', compact(
            'documents', 
            'documentTypes', 
            'programType',
            'pageTitle'
        ));
    }

    public function show(ApplicationDocument $document)
    {
        $document->load(['application.user', 'application.program']);
        
        return view('admin.documents.show', compact('document'));
    }

    public function verify(ApplicationDocument $document)
    {
        $document->update([
            'status' => 'verified',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Documento verificado exitosamente.');
    }

    public function reject(Request $request, ApplicationDocument $document)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Documento rechazado exitosamente.');
    }

    public function download(ApplicationDocument $document)
    {
        if (!Storage::exists($document->file_path)) {
            return back()->with('error', 'Archivo no encontrado.');
        }

        return Storage::download($document->file_path, $document->original_filename);
    }

    public function destroy(ApplicationDocument $document)
    {
        // Delete file from storage
        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Documento eliminado exitosamente.');
    }
}
