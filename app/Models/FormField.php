<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_form_id',
        'section_name',
        'field_key',
        'field_label',
        'field_type',
        'description',
        'placeholder',
        'options',
        'validation_rules',
        'is_required',
        'is_visible',
        'sort_order',
        'conditional_logic',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'conditional_logic' => 'array',
        'is_required' => 'boolean',
        'is_visible' => 'boolean',
    ];

    /**
     * Tipos de campos disponibles
     */
    public const FIELD_TYPES = [
        'text' => 'Texto',
        'email' => 'Email',
        'tel' => 'Teléfono',
        'number' => 'Número',
        'date' => 'Fecha',
        'datetime' => 'Fecha y Hora',
        'textarea' => 'Área de Texto',
        'select' => 'Lista Desplegable',
        'radio' => 'Opción Única',
        'checkbox' => 'Casilla de Verificación',
        'file' => 'Archivo',
        'signature' => 'Firma',
        'boolean' => 'Sí/No',
        'address' => 'Dirección',
        'country' => 'País',
        'currency' => 'Moneda',
    ];

    /**
     * Reglas de validación disponibles
     */
    public const VALIDATION_RULES = [
        'required' => 'Requerido',
        'email' => 'Email válido',
        'numeric' => 'Solo números',
        'min' => 'Longitud mínima',
        'max' => 'Longitud máxima',
        'date' => 'Fecha válida',
        'regex' => 'Expresión regular',
        'in' => 'Valor permitido',
        'file_types' => 'Tipos de archivo',
        'file_size' => 'Tamaño máximo',
    ];

    /**
     * Relación con el formulario del programa
     */
    public function programForm(): BelongsTo
    {
        return $this->belongsTo(ProgramForm::class);
    }

    /**
     * Obtener las opciones formateadas para select, radio, checkbox
     */
    public function getFormattedOptions(): array
    {
        if (!$this->options || !in_array($this->field_type, ['select', 'radio', 'checkbox'])) {
            return [];
        }

        $options = $this->options;
        
        // Si las opciones están en formato simple ['option1', 'option2']
        if (array_keys($options) === range(0, count($options) - 1)) {
            return array_map(function($option) {
                return [
                    'value' => $option,
                    'label' => $option
                ];
            }, $options);
        }

        // Si las opciones están en formato clave-valor
        return collect($options)->map(function($label, $value) {
            return [
                'value' => $value,
                'label' => $label
            ];
        })->values()->toArray();
    }

    /**
     * Obtener las reglas de validación de Laravel
     */
    public function getLaravelValidationRules(): array
    {
        $rules = [];
        
        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        if ($this->validation_rules) {
            foreach ($this->validation_rules as $rule => $value) {
                switch ($rule) {
                    case 'email':
                        $rules[] = 'email';
                        break;
                    case 'numeric':
                        $rules[] = 'numeric';
                        break;
                    case 'min':
                        $rules[] = "min:$value";
                        break;
                    case 'max':
                        $rules[] = "max:$value";
                        break;
                    case 'date':
                        $rules[] = 'date';
                        break;
                    case 'regex':
                        $rules[] = "regex:$value";
                        break;
                    case 'in':
                        if (is_array($value)) {
                            $rules[] = 'in:' . implode(',', $value);
                        }
                        break;
                    case 'file_types':
                        if (is_array($value)) {
                            $rules[] = 'mimes:' . implode(',', $value);
                        }
                        break;
                    case 'file_size':
                        $rules[] = "max:$value"; // En KB
                        break;
                }
            }
        }

        // Reglas específicas por tipo de campo
        switch ($this->field_type) {
            case 'email':
                if (!in_array('email', $rules)) {
                    $rules[] = 'email';
                }
                break;
            case 'number':
                if (!in_array('numeric', $rules)) {
                    $rules[] = 'numeric';
                }
                break;
            case 'date':
            case 'datetime':
                if (!in_array('date', $rules)) {
                    $rules[] = 'date';
                }
                break;
            case 'file':
                $rules[] = 'file';
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
        }

        return $rules;
    }

    /**
     * Validar si el campo debe mostrarse según lógica condicional
     */
    public function shouldShow(array $formData): bool
    {
        if (!$this->is_visible) {
            return false;
        }

        if (!$this->conditional_logic) {
            return true;
        }

        $logic = $this->conditional_logic;
        $condition = $logic['condition'] ?? 'and'; // 'and' o 'or'
        $rules = $logic['rules'] ?? [];

        $results = [];
        foreach ($rules as $rule) {
            $fieldKey = $rule['field'] ?? '';
            $operator = $rule['operator'] ?? '=';
            $value = $rule['value'] ?? '';
            $fieldValue = $formData[$fieldKey] ?? null;

            switch ($operator) {
                case '=':
                    $results[] = $fieldValue == $value;
                    break;
                case '!=':
                    $results[] = $fieldValue != $value;
                    break;
                case '>':
                    $results[] = $fieldValue > $value;
                    break;
                case '<':
                    $results[] = $fieldValue < $value;
                    break;
                case 'contains':
                    $results[] = is_string($fieldValue) && str_contains($fieldValue, $value);
                    break;
                case 'empty':
                    $results[] = empty($fieldValue);
                    break;
                case 'not_empty':
                    $results[] = !empty($fieldValue);
                    break;
            }
        }

        if ($condition === 'or') {
            return in_array(true, $results);
        } else {
            return !in_array(false, $results);
        }
    }

    /**
     * Scopes
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeInSection($query, string $section)
    {
        return $query->where('section_name', $section);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
