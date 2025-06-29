<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_form_id',
        'user_id',
        'application_id',
        'form_data',
        'status',
        'signature_data',
        'parent_signature_data',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'validation_errors',
        'form_version',
    ];

    protected $casts = [
        'form_data' => 'array',
        'validation_errors' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Estados disponibles
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Borrador',
        self::STATUS_SUBMITTED => 'Enviado',
        self::STATUS_REVIEWED => 'Revisado',
        self::STATUS_APPROVED => 'Aprobado',
        self::STATUS_REJECTED => 'Rechazado',
    ];

    /**
     * Relación con el formulario del programa
     */
    public function programForm(): BelongsTo
    {
        return $this->belongsTo(ProgramForm::class);
    }

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la aplicación
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relación con el revisor
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Obtener el valor de un campo específico
     */
    public function getFieldValue(string $fieldKey, $default = null)
    {
        return $this->form_data[$fieldKey] ?? $default;
    }

    /**
     * Establecer el valor de un campo específico
     */
    public function setFieldValue(string $fieldKey, $value): void
    {
        $formData = $this->form_data ?? [];
        $formData[$fieldKey] = $value;
        $this->form_data = $formData;
    }

    /**
     * Marcar como enviado
     */
    public function markAsSubmitted(): void
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Marcar como revisado
     */
    public function markAsReviewed(User $reviewer, string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REVIEWED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'review_notes' => $notes,
        ]);
    }

    /**
     * Aprobar la respuesta
     */
    public function approve(User $reviewer, string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'review_notes' => $notes,
        ]);
    }

    /**
     * Rechazar la respuesta
     */
    public function reject(User $reviewer, string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'review_notes' => $notes,
        ]);
    }

    /**
     * Validar los datos del formulario
     */
    public function validateFormData(): array
    {
        $errors = [];
        $formData = $this->form_data ?? [];
        
        foreach ($this->programForm->fields as $field) {
            $fieldKey = $field->field_key;
            $fieldValue = $formData[$fieldKey] ?? null;
            
            // Verificar si el campo debe mostrarse
            if (!$field->shouldShow($formData)) {
                continue;
            }
            
            // Obtener reglas de validación
            $rules = $field->getLaravelValidationRules();
            
            // Validar cada regla
            foreach ($rules as $rule) {
                $error = $this->validateFieldRule($fieldValue, $rule, $field);
                if ($error) {
                    $errors[$fieldKey][] = $error;
                }
            }
        }

        $this->validation_errors = $errors;
        $this->save();

        return $errors;
    }

    /**
     * Validar una regla específica para un campo
     */
    private function validateFieldRule($value, string $rule, FormField $field): ?string
    {
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $ruleValue = $ruleParts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    return "El campo {$field->field_label} es requerido.";
                }
                break;
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "El campo {$field->field_label} debe ser un email válido.";
                }
                break;
            case 'numeric':
                if ($value && !is_numeric($value)) {
                    return "El campo {$field->field_label} debe ser un número.";
                }
                break;
            case 'min':
                if ($value && strlen($value) < $ruleValue) {
                    return "El campo {$field->field_label} debe tener al menos {$ruleValue} caracteres.";
                }
                break;
            case 'max':
                if ($value && strlen($value) > $ruleValue) {
                    return "El campo {$field->field_label} no puede tener más de {$ruleValue} caracteres.";
                }
                break;
            case 'date':
                if ($value && !strtotime($value)) {
                    return "El campo {$field->field_label} debe ser una fecha válida.";
                }
                break;
        }

        return null;
    }

    /**
     * Verificar si la respuesta está completa
     */
    public function isComplete(): bool
    {
        $formData = $this->form_data ?? [];
        
        foreach ($this->programForm->fields as $field) {
            if (!$field->shouldShow($formData)) {
                continue;
            }
            
            if ($field->is_required) {
                $value = $formData[$field->field_key] ?? null;
                if (empty($value)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Obtener el porcentaje de completitud
     */
    public function getCompletionPercentage(): int
    {
        $formData = $this->form_data ?? [];
        $totalFields = 0;
        $completedFields = 0;
        
        foreach ($this->programForm->fields as $field) {
            if (!$field->shouldShow($formData)) {
                continue;
            }
            
            $totalFields++;
            $value = $formData[$field->field_key] ?? null;
            
            if (!empty($value)) {
                $completedFields++;
            }
        }

        return $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;
    }

    /**
     * Obtener el nombre del estado
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Verificar si puede ser editado
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDrafts($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProgram($query, int $programId)
    {
        return $query->whereHas('programForm', function($q) use ($programId) {
            $q->where('program_id', $programId);
        });
    }
}
