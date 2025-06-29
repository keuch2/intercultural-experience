<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramForm;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    /**
     * Obtener el formulario activo de un programa
     */
    public function getProgramForm(Program $program)
    {
        try {
            $form = ProgramForm::getActiveFormForProgram($program->id);
            
            if (!$form) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay formulario activo disponible para este programa.'
                ], 404);
            }

            // Verificar si el usuario puede llenar el formulario
            $user = Auth::user();
            if (!$form->canUserFillForm($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No cumple con los requisitos para llenar este formulario.'
                ], 403);
            }

            // Obtener la estructura del formulario
            $structure = $form->getFormStructure();

            // Verificar si el usuario ya tiene una respuesta
            $existingSubmission = FormSubmission::where('program_form_id', $form->id)
                ->where('user_id', $user->id)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'form' => [
                        'id' => $form->id,
                        'name' => $form->name,
                        'description' => $form->description,
                        'version' => $form->version,
                        'requires_signature' => $form->requires_signature,
                        'requires_parent_signature' => $form->requires_parent_signature,
                        'min_age' => $form->min_age,
                        'max_age' => $form->max_age,
                        'terms_and_conditions' => $form->terms_and_conditions,
                    ],
                    'structure' => $structure,
                    'existing_submission' => $existingSubmission ? [
                        'id' => $existingSubmission->id,
                        'status' => $existingSubmission->status,
                        'form_data' => $existingSubmission->form_data,
                        'completion_percentage' => $existingSubmission->getCompletionPercentage(),
                        'can_edit' => $existingSubmission->canBeEdited(),
                        'submitted_at' => $existingSubmission->submitted_at,
                    ] : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar o actualizar respuesta del formulario (draft)
     */
    public function saveFormData(Request $request, Program $program)
    {
        try {
            $form = ProgramForm::getActiveFormForProgram($program->id);
            
            if (!$form) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay formulario activo disponible.'
                ], 404);
            }

            $user = Auth::user();
            
            // Validar datos básicos
            $validator = Validator::make($request->all(), [
                'form_data' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar o crear la respuesta
            $submission = FormSubmission::updateOrCreate(
                [
                    'program_form_id' => $form->id,
                    'user_id' => $user->id,
                ],
                [
                    'form_data' => $request->form_data,
                    'status' => FormSubmission::STATUS_DRAFT,
                    'form_version' => $form->version,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Datos guardados exitosamente',
                'data' => [
                    'submission_id' => $submission->id,
                    'status' => $submission->status,
                    'completion_percentage' => $submission->getCompletionPercentage(),
                    'is_complete' => $submission->isComplete(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar formulario (submit)
     */
    public function submitForm(Request $request, Program $program)
    {
        try {
            $form = ProgramForm::getActiveFormForProgram($program->id);
            
            if (!$form) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay formulario activo disponible.'
                ], 404);
            }

            $user = Auth::user();
            
            // Buscar la respuesta existente
            $submission = FormSubmission::where('program_form_id', $form->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el borrador del formulario.'
                ], 404);
            }

            if (!$submission->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este formulario ya no puede ser editado.'
                ], 403);
            }

            // Actualizar datos si se enviaron
            if ($request->has('form_data')) {
                $submission->form_data = $request->form_data;
            }

            // Validar que el formulario esté completo
            if (!$submission->isComplete()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El formulario no está completo. Faltan campos requeridos.',
                    'completion_percentage' => $submission->getCompletionPercentage()
                ], 422);
            }

            // Validar datos del formulario
            $validationErrors = $submission->validateFormData();
            if (!empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hay errores en el formulario.',
                    'errors' => $validationErrors
                ], 422);
            }

            // Guardar firmas si se enviaron
            if ($request->has('signature_data')) {
                $submission->signature_data = $request->signature_data;
            }

            if ($request->has('parent_signature_data')) {
                $submission->parent_signature_data = $request->parent_signature_data;
            }

            // Marcar como enviado
            $submission->markAsSubmitted();

            return response()->json([
                'success' => true,
                'message' => 'Formulario enviado exitosamente',
                'data' => [
                    'submission_id' => $submission->id,
                    'status' => $submission->status,
                    'submitted_at' => $submission->submitted_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener respuestas del usuario
     */
    public function getUserSubmissions()
    {
        try {
            $user = Auth::user();
            
            $submissions = FormSubmission::where('user_id', $user->id)
                ->with(['programForm.program'])
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $submissions->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'program' => [
                        'id' => $submission->programForm->program->id,
                        'name' => $submission->programForm->program->name,
                        'country' => $submission->programForm->program->country,
                    ],
                    'form' => [
                        'id' => $submission->programForm->id,
                        'name' => $submission->programForm->name,
                        'version' => $submission->programForm->version,
                    ],
                    'status' => $submission->status,
                    'status_name' => $submission->status_name,
                    'completion_percentage' => $submission->getCompletionPercentage(),
                    'can_edit' => $submission->canBeEdited(),
                    'submitted_at' => $submission->submitted_at,
                    'reviewed_at' => $submission->reviewed_at,
                    'review_notes' => $submission->review_notes,
                    'created_at' => $submission->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las respuestas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una respuesta específica
     */
    public function getSubmission(FormSubmission $submission)
    {
        try {
            $user = Auth::user();
            
            // Verificar que la respuesta pertenezca al usuario
            if ($submission->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver esta respuesta.'
                ], 403);
            }

            $submission->load(['programForm.program']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $submission->id,
                    'program' => [
                        'id' => $submission->programForm->program->id,
                        'name' => $submission->programForm->program->name,
                        'country' => $submission->programForm->program->country,
                    ],
                    'form' => [
                        'id' => $submission->programForm->id,
                        'name' => $submission->programForm->name,
                        'version' => $submission->programForm->version,
                    ],
                    'form_data' => $submission->form_data,
                    'status' => $submission->status,
                    'status_name' => $submission->status_name,
                    'completion_percentage' => $submission->getCompletionPercentage(),
                    'can_edit' => $submission->canBeEdited(),
                    'signature_data' => $submission->signature_data,
                    'parent_signature_data' => $submission->parent_signature_data,
                    'submitted_at' => $submission->submitted_at,
                    'reviewed_at' => $submission->reviewed_at,
                    'review_notes' => $submission->review_notes,
                    'validation_errors' => $submission->validation_errors,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la respuesta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar borrador de respuesta
     */
    public function deleteSubmission(FormSubmission $submission)
    {
        try {
            $user = Auth::user();
            
            // Verificar que la respuesta pertenezca al usuario
            if ($submission->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar esta respuesta.'
                ], 403);
            }

            // Solo permitir eliminar borradores
            if ($submission->status !== FormSubmission::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar borradores.'
                ], 403);
            }

            $submission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Borrador eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el borrador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener formulario activo de un programa (método simplificado)
     */
    public function getActiveForm(Program $program)
    {
        $form = $program->forms()
            ->where('is_active', true)
            ->where('is_published', true)
            ->with(['fields' => function($query) {
                $query->where('is_visible', true)->orderBy('sort_order');
            }])
            ->first();

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'No hay formularios activos para este programa'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $form
        ]);
    }

    /**
     * Obtener estructura de un formulario específico
     */
    public function getFormStructure(ProgramForm $form)
    {
        $form->load(['fields' => function($query) {
            $query->where('is_visible', true)->orderBy('sort_order');
        }]);

        return response()->json([
            'success' => true,
            'data' => [
                'form' => $form,
                'structure' => $form->getFormStructure()
            ]
        ]);
    }

    /**
     * Obtener formularios disponibles para un programa
     */
    public function getProgramForms(Program $program)
    {
        $forms = $program->forms()
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $forms
        ]);
    }

    /**
     * Validar datos del formulario
     */
    public function validateFormData(Request $request, ProgramForm $form)
    {
        try {
            $validator = Validator::make($request->all(), [
                'form_data' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'valid' => false,
                    'errors' => [['field_key' => 'form_data', 'message' => 'Datos del formulario requeridos']]
                ]);
            }

            // Crear una respuesta temporal para validar
            $tempSubmission = new FormSubmission([
                'program_form_id' => $form->id,
                'form_data' => $request->form_data,
                'user_id' => Auth::id(),
            ]);

            $errors = $tempSubmission->validateFormData();

            return response()->json([
                'valid' => empty($errors),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'errors' => [['field_key' => 'general', 'message' => 'Error de validación']]
            ]);
        }
    }

    /**
     * Subir archivo para formulario
     */
    public function uploadFile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // 10MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo inválido',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $path = $file->store('form-uploads', 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener países disponibles
     */
    public function getCountries()
    {
        $countries = [
            ['code' => 'PY', 'name' => 'Paraguay'],
            ['code' => 'AR', 'name' => 'Argentina'],
            ['code' => 'BR', 'name' => 'Brasil'],
            ['code' => 'UY', 'name' => 'Uruguay'],
            ['code' => 'BO', 'name' => 'Bolivia'],
            ['code' => 'CL', 'name' => 'Chile'],
            ['code' => 'PE', 'name' => 'Perú'],
            ['code' => 'CO', 'name' => 'Colombia'],
            ['code' => 'VE', 'name' => 'Venezuela'],
            ['code' => 'EC', 'name' => 'Ecuador'],
            ['code' => 'US', 'name' => 'Estados Unidos'],
            ['code' => 'CA', 'name' => 'Canadá'],
            ['code' => 'MX', 'name' => 'México'],
            ['code' => 'ES', 'name' => 'España'],
            ['code' => 'FR', 'name' => 'Francia'],
            ['code' => 'DE', 'name' => 'Alemania'],
            ['code' => 'IT', 'name' => 'Italia'],
            ['code' => 'PT', 'name' => 'Portugal'],
            ['code' => 'GB', 'name' => 'Reino Unido'],
            ['code' => 'AU', 'name' => 'Australia'],
            ['code' => 'NZ', 'name' => 'Nueva Zelanda'],
            ['code' => 'JP', 'name' => 'Japón'],
            ['code' => 'KR', 'name' => 'Corea del Sur'],
            ['code' => 'CN', 'name' => 'China'],
        ];

        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }

    /**
     * Obtener plantillas de formularios
     */
    public function getFormTemplates(Request $request)
    {
        $query = \App\Models\FormTemplate::active();

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        $templates = $query->orderBy('usage_count', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }
}
