<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApplicationDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar que la aplicación pertenezca al usuario autenticado
        $application = Application::where('id', $request->application_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        $documents = ApplicationDocument::where('application_id', $application->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB máximo
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar que la aplicación pertenezca al usuario autenticado
        $application = Application::where('id', $request->application_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        // Verificar que la aplicación esté en estado pendiente o en revisión
        if (!in_array($application->status, ['pending', 'in_review'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden subir documentos a una solicitud que ya ha sido finalizada.'
            ], 422);
        }
        
        // Guardar el archivo
        $filePath = $request->file('file')->store('documents/' . $application->id, 'public');
        $fileName = $request->file('file')->getClientOriginalName();
        
        // Crear el documento
        $document = new ApplicationDocument();
        $document->application_id = $application->id;
        $document->name = $request->name;
        $document->file_path = $filePath;
        $document->file_name = $fileName;
        $document->status = 'uploaded';
        $document->uploaded_at = now();
        $document->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Documento subido correctamente.',
            'data' => $document
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $document = ApplicationDocument::findOrFail($id);
        
        // Verificar que el documento pertenezca al usuario autenticado
        $application = Application::where('id', $document->application_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        return response()->json([
            'success' => true,
            'data' => $document,
            'download_url' => url('storage/' . $document->file_path)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $document = ApplicationDocument::findOrFail($id);
        
        // Verificar que el documento pertenezca al usuario autenticado
        $application = Application::where('id', $document->application_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        // Verificar que el documento no esté verificado
        if ($document->status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede actualizar un documento que ya ha sido verificado.'
            ], 422);
        }
        
        // Verificar que la aplicación esté en estado pendiente o en revisión
        if (!in_array($application->status, ['pending', 'in_review'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden actualizar documentos de una solicitud que ya ha sido finalizada.'
            ], 422);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'file' => 'sometimes|required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB máximo
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Actualizar nombre si se proporciona
        if ($request->has('name')) {
            $document->name = $request->name;
        }
        
        // Actualizar archivo si se proporciona
        if ($request->hasFile('file')) {
            // Eliminar archivo anterior
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            // Guardar nuevo archivo
            $filePath = $request->file('file')->store('documents/' . $application->id, 'public');
            $fileName = $request->file('file')->getClientOriginalName();
            
            $document->file_path = $filePath;
            $document->file_name = $fileName;
            $document->status = 'uploaded';
            $document->uploaded_at = now();
        }
        
        $document->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Documento actualizado correctamente.',
            'data' => $document
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $document = ApplicationDocument::findOrFail($id);
        
        // Verificar que el documento pertenezca al usuario autenticado
        $application = Application::where('id', $document->application_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        // Verificar que el documento no esté verificado
        if ($document->status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un documento que ya ha sido verificado.'
            ], 422);
        }
        
        // Verificar que la aplicación esté en estado pendiente o en revisión
        if (!in_array($application->status, ['pending', 'in_review'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden eliminar documentos de una solicitud que ya ha sido finalizada.'
            ], 422);
        }
        
        // Eliminar archivo
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        // Eliminar documento
        $document->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Documento eliminado correctamente.'
        ]);
    }
    
    /**
     * Verifica un documento (solo para administradores).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function verify($id)
    {
        $document = ApplicationDocument::findOrFail($id);
        
        if ($document->status !== 'uploaded') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden verificar documentos que han sido subidos.'
            ], 422);
        }
        
        $document->status = 'verified';
        $document->verified_at = now();
        $document->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Documento verificado correctamente.',
            'data' => $document
        ]);
    }
    
    /**
     * Rechaza un documento (solo para administradores).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'observations' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $document = ApplicationDocument::findOrFail($id);
        
        if ($document->status !== 'uploaded') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden rechazar documentos que han sido subidos.'
            ], 422);
        }
        
        $document->status = 'rejected';
        $document->observations = $request->observations;
        $document->verified_at = now();
        $document->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Documento rechazado correctamente.',
            'data' => $document
        ]);
    }
}
