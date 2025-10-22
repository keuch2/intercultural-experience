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

    /**
     * Show pending documents for review
     */
    public function pending(Request $request)
    {
        $query = ApplicationDocument::with(['application.user', 'application.program'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc');

        // Filtros
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->filled('program_type')) {
            $query->whereHas('application.program', function ($q) use ($request) {
                $q->where('main_category', $request->program_type);
            });
        }

        $documents = $query->paginate(20);
        
        $documentTypes = ApplicationDocument::where('status', 'pending')
            ->distinct('document_type')
            ->pluck('document_type')
            ->filter()
            ->sort();

        $stats = [
            'total' => ApplicationDocument::where('status', 'pending')->count(),
            'urgent' => ApplicationDocument::where('status', 'pending')
                ->where('created_at', '<', now()->subDays(3))
                ->count(),
        ];

        return view('admin.documents.pending', compact('documents', 'documentTypes', 'stats'));
    }

    /**
     * Show expired documents
     */
    public function expired(Request $request)
    {
        $query = ApplicationDocument::with(['application.user', 'application.program'])
            ->where('status', 'verified')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->orderBy('expiry_date', 'desc');

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        $documents = $query->paginate(20);
        
        // También documentos próximos a vencer (30 días)
        $expiringSoon = ApplicationDocument::with(['application.user', 'application.program'])
            ->where('status', 'verified')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get();

        return view('admin.documents.expired', compact('documents', 'expiringSoon'));
    }

    /**
     * Review a specific document
     */
    public function review($id)
    {
        $document = ApplicationDocument::with(['application.user', 'application.program'])->findOrFail($id);
        
        // Verificar que existe el archivo
        if (!Storage::exists($document->file_path)) {
            return back()->with('error', 'El archivo no existe en el servidor.');
        }

        // Obtener información del archivo
        $fileInfo = [
            'size' => Storage::size($document->file_path),
            'mime' => Storage::mimeType($document->file_path),
            'exists' => true,
        ];

        return view('admin.documents.review', compact('document', 'fileInfo'));
    }

    /**
     * Bulk approve documents
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:application_documents,id',
        ]);

        $count = ApplicationDocument::whereIn('id', $request->document_ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'verified',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

        return back()->with('success', "{$count} documento(s) aprobado(s) exitosamente.");
    }

    /**
     * Bulk reject documents
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:application_documents,id',
            'rejection_reason' => 'required|string|max:500',
        ]);

        $count = ApplicationDocument::whereIn('id', $request->document_ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

        return back()->with('success', "{$count} documento(s) rechazado(s) exitosamente.");
    }
}
