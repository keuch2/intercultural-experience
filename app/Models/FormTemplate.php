<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'icon',
        'template_data',
        'default_settings',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'template_data' => 'array',
        'default_settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Categorías disponibles
     */
    public const CATEGORIES = [
        'academic' => 'Académico',
        'volunteer' => 'Voluntariado',
        'internship' => 'Prácticas',
        'language' => 'Idiomas',
        'cultural' => 'Cultural',
        'research' => 'Investigación',
        'work_study' => 'Work & Study',
        'au_pair' => 'Au Pair',
        'summer' => 'Programas de Verano',
        'general' => 'General',
    ];

    /**
     * Incrementar contador de uso
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Crear formulario desde plantilla
     */
    public function createFormFromTemplate(Program $program, array $overrides = [])
    {
        $templateData = $this->template_data;
        $defaultSettings = $this->default_settings ?? [];

        // Combinar configuraciones por defecto con overrides
        $formData = array_merge([
            'name' => $this->name . ' - ' . $program->name,
            'description' => $this->description,
            'version' => '1.0',
            'requires_signature' => $defaultSettings['requires_signature'] ?? false,
            'requires_parent_signature' => $defaultSettings['requires_parent_signature'] ?? false,
            'min_age' => $defaultSettings['min_age'] ?? null,
            'max_age' => $defaultSettings['max_age'] ?? null,
            'terms_and_conditions' => $defaultSettings['terms_and_conditions'] ?? null,
        ], $overrides);

        // Crear el formulario
        $form = ProgramForm::create([
            'program_id' => $program->id,
            'name' => $formData['name'],
            'description' => $formData['description'],
            'version' => $formData['version'],
            'requires_signature' => $formData['requires_signature'],
            'requires_parent_signature' => $formData['requires_parent_signature'],
            'min_age' => $formData['min_age'],
            'max_age' => $formData['max_age'],
            'terms_and_conditions' => $formData['terms_and_conditions'],
            'sections' => $templateData['sections'] ?? [],
            'is_active' => false,
            'is_published' => false,
        ]);

        // Crear los campos
        if (isset($templateData['fields'])) {
            foreach ($templateData['fields'] as $fieldData) {
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
        }

        // Incrementar contador de uso
        $this->incrementUsage();

        return $form;
    }

    /**
     * Obtener plantillas por categoría
     */
    public static function getByCategory(string $category)
    {
        return static::where('category', $category)
            ->where('is_active', true)
            ->orderBy('usage_count', 'desc')
            ->get();
    }

    /**
     * Obtener plantillas populares
     */
    public static function getPopular(int $limit = 5)
    {
        return static::where('is_active', true)
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Accessors
     */
    public function getCategoryNameAttribute()
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getFieldsCountAttribute()
    {
        return count($this->template_data['fields'] ?? []);
    }

    public function getSectionsCountAttribute()
    {
        return count($this->template_data['sections'] ?? []);
    }
}
