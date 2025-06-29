<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'description',
        'version',
        'is_active',
        'is_published',
        'settings',
        'sections',
        'terms_and_conditions',
        'requires_signature',
        'requires_parent_signature',
        'min_age',
        'max_age',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'requires_signature' => 'boolean',
        'requires_parent_signature' => 'boolean',
        'settings' => 'array',
        'sections' => 'array',
    ];

    /**
     * Relación con el programa
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Relación con los campos del formulario
     */
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    /**
     * Relación con las respuestas del formulario
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    /**
     * Obtener campos agrupados por sección
     */
    public function getFieldsBySectionAttribute()
    {
        return $this->fields->groupBy('section_name');
    }

    /**
     * Obtener la estructura completa del formulario
     */
    public function getFormStructure()
    {
        $sections = $this->sections ?? [];
        $fields = $this->fields;

        $structure = [];
        
        // Si hay secciones definidas, organizarlas
        if (!empty($sections)) {
            foreach ($sections as $section) {
                $sectionFields = $fields->where('section_name', $section['name']);
                $structure[] = [
                    'name' => $section['name'],
                    'title' => $section['title'] ?? $section['name'],
                    'description' => $section['description'] ?? null,
                    'fields' => $sectionFields->values()
                ];
            }
        } else {
            // Si no hay secciones, agrupar por section_name de los campos
            $fieldsBySection = $fields->groupBy('section_name');
            foreach ($fieldsBySection as $sectionName => $sectionFields) {
                $structure[] = [
                    'name' => $sectionName ?? 'general',
                    'title' => ucfirst($sectionName ?? 'Información General'),
                    'description' => null,
                    'fields' => $sectionFields->values()
                ];
            }
        }

        return $structure;
    }

    /**
     * Validar si un usuario puede llenar este formulario
     */
    public function canUserFillForm(User $user): bool
    {
        if (!$this->is_active || !$this->is_published) {
            return false;
        }

        // Validar edad si está configurada
        if ($this->min_age || $this->max_age) {
            $userAge = $user->birth_date ? $user->birth_date->age : null;
            
            if ($this->min_age && $userAge < $this->min_age) {
                return false;
            }
            
            if ($this->max_age && $userAge > $this->max_age) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener la versión activa de un formulario para un programa
     */
    public static function getActiveFormForProgram(int $programId): ?self
    {
        return static::where('program_id', $programId)
            ->where('is_active', true)
            ->where('is_published', true)
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Clonar formulario para nueva versión
     */
    public function cloneToNewVersion(string $newVersion): self
    {
        $newForm = $this->replicate();
        $newForm->version = $newVersion;
        $newForm->is_active = false;
        $newForm->is_published = false;
        $newForm->save();

        // Clonar campos
        foreach ($this->fields as $field) {
            $newField = $field->replicate();
            $newField->program_form_id = $newForm->id;
            $newField->save();
        }

        return $newForm;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeForProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }
}
