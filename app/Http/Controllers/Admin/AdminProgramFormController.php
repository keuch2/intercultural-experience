<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramForm;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\FormTemplate;
use Illuminate\Http\Request;

class AdminProgramFormController extends Controller
{
    /**
     * Mostrar formularios de un programa
     */
    public function index(Program $program)
    {
        $forms = $program->forms()->with('fields')->orderBy('version', 'desc')->paginate(10);
        
        return view('admin.programs.forms.index', compact('program', 'forms'));
    }

    /**
     * Mostrar formulario para crear nuevo formulario
     */
    public function create(Program $program)
    {
        // Obtener plantillas disponibles
        $templates = FormTemplate::active()
            ->orderBy('category')
            ->orderBy('usage_count', 'desc')
            ->get()
            ->groupBy('category');

        $categories = FormTemplate::CATEGORIES;
        $fieldTypes = FormField::FIELD_TYPES;
        
        return view('admin.programs.forms.create', compact('program', 'templates', 'categories', 'fieldTypes'));
    }

    /**
     * Almacenar nuevo formulario
     */
    public function store(Request $request, Program $program)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'version' => 'required|string|max:10',
            'terms_and_conditions' => 'nullable|string',
            'requires_signature' => 'boolean',
            'requires_parent_signature' => 'boolean',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|min:0',
            'sections' => 'nullable|string', // JSON string from form builder
            'fields' => 'nullable|string', // JSON string from form builder
        ]);

        // Decodificar datos JSON del form builder
        $sectionsData = $request->sections ? json_decode($request->sections, true) : [];
        $fieldsData = $request->fields ? json_decode($request->fields, true) : [];

        // Validar que hay al menos un campo
        if (empty($fieldsData)) {
            return redirect()->back()
                ->withErrors(['fields' => 'Debe agregar al menos un campo al formulario.'])
                ->withInput();
        }

        // Crear el formulario
        $form = ProgramForm::create([
            'program_id' => $program->id,
            'name' => $request->name,
            'description' => $request->description,
            'version' => $request->version,
            'terms_and_conditions' => $request->terms_and_conditions,
            'requires_signature' => $request->boolean('requires_signature'),
            'requires_parent_signature' => $request->boolean('requires_parent_signature'),
            'min_age' => $request->min_age,
            'max_age' => $request->max_age,
            'sections' => $sectionsData,
            'is_active' => false,
            'is_published' => $request->boolean('save_as_draft') ? false : false, // Por defecto como borrador
        ]);

        // Crear los campos
        foreach ($fieldsData as $fieldData) {
            FormField::create([
                'program_form_id' => $form->id,
                'section_name' => $fieldData['section_name'] ?? 'general',
                'field_key' => $fieldData['field_key'],
                'field_label' => $fieldData['field_label'],
                'field_type' => $fieldData['field_type'],
                'description' => $fieldData['description'] ?? null,
                'placeholder' => $fieldData['placeholder'] ?? null,
                'options' => $fieldData['options'] ?? null,
                'validation_rules' => $fieldData['validation_rules'] ?? null,
                'is_required' => $fieldData['is_required'] ?? false,
                'is_visible' => $fieldData['is_visible'] ?? true,
                'sort_order' => $fieldData['sort_order'] ?? 0,
                'conditional_logic' => $fieldData['conditional_logic'] ?? null,
            ]);
        }

        $message = $request->boolean('save_as_draft') ? 
            'Formulario guardado como borrador exitosamente.' : 
            'Formulario creado exitosamente.';

        return redirect()->route('admin.programs.forms.show', [$program, $form])
            ->with('success', $message);
    }

    /**
     * Mostrar formulario específico
     */
    public function show(Program $program, ProgramForm $form)
    {
        $form->load('fields');
        $structure = $form->getFormStructure();
        $submissions = $form->submissions()->with('user')->latest()->paginate(10);
        
        return view('admin.programs.forms.show', compact('program', 'form', 'structure', 'submissions'));
    }

    /**
     * Mostrar formulario para editar
     */
    public function edit(Program $program, ProgramForm $form)
    {
        $form->load('fields');
        $fieldTypes = FormField::FIELD_TYPES;
        
        return view('admin.programs.forms.edit', compact('program', 'form', 'fieldTypes'));
    }

    /**
     * Actualizar formulario
     */
    public function update(Request $request, Program $program, ProgramForm $form)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|min:0',
            'sections' => 'nullable|string',
            'fields' => 'nullable|string',
        ]);

        // Actualizar información básica del formulario
        $form->update([
            'name' => $request->name,
            'description' => $request->description,
            'terms_and_conditions' => $request->terms_and_conditions,
            'min_age' => $request->min_age,
            'max_age' => $request->max_age,
        ]);

        // Actualizar campos si se proporcionaron
        if ($request->filled('fields')) {
            $fieldsData = json_decode($request->fields, true);
            $sectionsData = json_decode($request->sections, true);
            
            if ($fieldsData && is_array($fieldsData)) {
                // Eliminar campos existentes
                $form->fields()->delete();
                
                // Crear nuevos campos
                foreach ($fieldsData as $index => $fieldData) {
                    $form->fields()->create([
                        'field_type' => $fieldData['field_type'],
                        'field_label' => $fieldData['field_label'],
                        'field_key' => $fieldData['field_key'] ?? $fieldData['field_name'] ?? $fieldData['field_label'],
                        'description' => $fieldData['description'] ?? null,
                        'placeholder' => $fieldData['placeholder'] ?? null,
                        'is_required' => $fieldData['is_required'] ?? false,
                        'options' => !empty($fieldData['options']) ? $fieldData['options'] : null,
                        'section_name' => $fieldData['section_name'] ?? 'default',
                        'sort_order' => $fieldData['order'] ?? $index,
                        'validation_rules' => $this->generateValidationRules($fieldData),
                    ]);
                }
            }
        }

        return redirect()->route('admin.programs.forms.show', [$program, $form])
            ->with('success', 'Formulario actualizado exitosamente.');
    }

    /**
     * Obtener título de sección
     */
    private function getSectionTitle($sectionsData, $sectionName)
    {
        if (!$sectionsData) return ucfirst(str_replace('_', ' ', $sectionName));
        
        foreach ($sectionsData as $section) {
            if ($section['name'] === $sectionName) {
                return $section['title'];
            }
        }
        
        return ucfirst(str_replace('_', ' ', $sectionName));
    }

    /**
     * Obtener descripción de sección
     */
    private function getSectionDescription($sectionsData, $sectionName)
    {
        if (!$sectionsData) return null;
        
        foreach ($sectionsData as $section) {
            if ($section['name'] === $sectionName) {
                return $section['description'] ?? null;
            }
        }
        
        return null;
    }

    /**
     * Generar reglas de validación para un campo
     */
    private function generateValidationRules($fieldData)
    {
        $rules = [];
        
        if ($fieldData['is_required'] ?? false) {
            $rules[] = 'required';
        }
        
        switch ($fieldData['field_type']) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'url':
                $rules[] = 'url';
                break;
            case 'file':
                $rules[] = 'file';
                break;
        }
        
        return implode('|', $rules);
    }

    /**
     * Activar/desactivar formulario
     */
    public function toggleActive(Program $program, ProgramForm $form)
    {
        // Si se va a activar, desactivar otros formularios del mismo programa
        if (!$form->is_active) {
            ProgramForm::where('program_id', $program->id)->update(['is_active' => false]);
        }

        $form->update(['is_active' => !$form->is_active]);

        $status = $form->is_active ? 'activado' : 'desactivado';
        return redirect()->back()->with('success', "Formulario {$status} exitosamente.");
    }

    /**
     * Publicar/despublicar formulario
     */
    public function togglePublished(Program $program, ProgramForm $form)
    {
        $form->update(['is_published' => !$form->is_published]);

        $status = $form->is_published ? 'publicado' : 'despublicado';
        return redirect()->back()->with('success', "Formulario {$status} exitosamente.");
    }

    /**
     * Clonar formulario para nueva versión
     */
    public function clone(Request $request, Program $program, ProgramForm $form)
    {
        $request->validate([
            'new_version' => 'required|string|max:10|unique:program_forms,version,NULL,id,program_id,' . $program->id,
        ]);

        $newForm = $form->cloneToNewVersion($request->new_version);

        return redirect()->route('admin.programs.forms.show', [$program, $newForm])
            ->with('success', 'Nueva versión del formulario creada exitosamente.');
    }

    /**
     * Eliminar formulario
     */
    public function destroy(Program $program, ProgramForm $form)
    {
        // No permitir eliminar si tiene respuestas
        if ($form->submissions()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar un formulario que tiene respuestas.');
        }

        $form->delete();

        return redirect()->route('admin.programs.forms.index', $program)
            ->with('success', 'Formulario eliminado exitosamente.');
    }

    /**
     * Vista previa del formulario
     */
    public function preview(Program $program, ProgramForm $form)
    {
        $form->load('fields');
        $structure = $form->getFormStructure();
        
        return view('admin.programs.forms.preview', compact('program', 'form', 'structure'));
    }

    /**
     * Gestionar respuestas del formulario
     */
    public function submissions(Program $program, ProgramForm $form)
    {
        $submissions = $form->submissions()
            ->with(['user', 'reviewer'])
            ->latest()
            ->paginate(15);
        
        return view('admin.programs.forms.submissions', compact('program', 'form', 'submissions'));
    }

    /**
     * Ver respuesta específica
     */
    public function showSubmission(Program $program, ProgramForm $form, FormSubmission $submission)
    {
        $submission->load(['user', 'reviewer']);
        $structure = $form->getFormStructure();
        
        return view('admin.programs.forms.submission', compact('program', 'form', 'submission', 'structure'));
    }

    /**
     * Revisar respuesta
     */
    public function reviewSubmission(Request $request, Program $program, ProgramForm $form, FormSubmission $submission)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'review_notes' => 'nullable|string',
        ]);

        if ($request->status === 'approved') {
            $submission->approve(auth()->user(), $request->review_notes);
        } else {
            $submission->reject(auth()->user(), $request->review_notes);
        }

        return redirect()->back()->with('success', 'Respuesta revisada exitosamente.');
    }

    /**
     * Crear formulario desde plantilla
     */
    public function createFromTemplate(Request $request, Program $program)
    {
        $request->validate([
            'template_id' => 'required|exists:form_templates,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $template = FormTemplate::findOrFail($request->template_id);
        
        // Crear formulario desde plantilla
        $overrides = array_filter([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $form = $template->createFormFromTemplate($program, $overrides);

        return redirect()->route('admin.programs.forms.show', [$program, $form])
            ->with('success', 'Formulario creado desde plantilla exitosamente.');
    }

    /**
     * Obtener datos de plantilla via AJAX
     */
    public function getTemplateData(FormTemplate $template)
    {
        return response()->json([
            'template' => $template,
            'sections' => $template->template_data['sections'] ?? [],
            'fields' => $template->template_data['fields'] ?? [],
            'settings' => $template->default_settings ?? []
        ]);
    }
}
