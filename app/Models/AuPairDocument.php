<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuPairDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'au_pair_process_id',
        'document_type',
        'stage',
        'uploaded_by_type',
        'file_path',
        'original_filename',
        'file_size',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'is_required',
        'min_count',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'is_required' => 'boolean',
        'file_size' => 'integer',
        'min_count' => 'integer',
        'sort_order' => 'integer',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(AuPairProcess::class, 'au_pair_process_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(int $reviewerId): bool
    {
        return $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(int $reviewerId, string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    // Scopes
    public function scopeForStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            default => 'Desconocido',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Document type definitions with labels and metadata
     */
    public static function documentTypes(): array
    {
        return [
            // Admission
            'cedula' => ['label' => 'Cédula de Identidad', 'stage' => 'admission', 'required' => true, 'sort' => 1],
            'passport' => ['label' => 'Pasaporte', 'stage' => 'admission', 'required' => false, 'sort' => 2],
            'drivers_license' => ['label' => 'Licencia de Conducir', 'stage' => 'admission', 'required' => false, 'sort' => 3],
            'profile_photo' => ['label' => 'Foto de Perfil', 'stage' => 'admission', 'required' => true, 'sort' => 4],
            'enrollment_form' => ['label' => 'Formulario de Inscripción (firmado)', 'stage' => 'admission', 'required' => true, 'sort' => 5],

            // Application - Payment 1
            'psych_test' => ['label' => 'Test Psicológico', 'stage' => 'application_payment1', 'required' => true, 'sort' => 10],
            'child_photos' => ['label' => '12 Fotos con Niños', 'stage' => 'application_payment1', 'required' => true, 'sort' => 11, 'min_count' => 12],
            'presentation_video' => ['label' => 'Video de Presentación', 'stage' => 'application_payment1', 'required' => true, 'sort' => 12],
            'cover_letter' => ['label' => 'Carta de Presentación', 'stage' => 'application_payment1', 'required' => true, 'sort' => 13],
            'vaccination_card' => ['label' => 'Libreta de Vacunas', 'stage' => 'application_payment1', 'required' => true, 'sort' => 14],
            'certifications' => ['label' => 'Certificaciones/Especializaciones/Talleres', 'stage' => 'application_payment1', 'required' => false, 'sort' => 15],
            'police_record' => ['label' => 'Antecedentes Policiales', 'stage' => 'application_payment1', 'required' => true, 'sort' => 16],
            'bachelor_degree' => ['label' => 'Título de Bachiller', 'stage' => 'application_payment1', 'required' => true, 'sort' => 17],
            'passport_doc' => ['label' => 'Pasaporte', 'stage' => 'application_payment1', 'required' => true, 'sort' => 18],
            'drivers_license_app' => ['label' => 'Licencia de Conducir', 'stage' => 'application_payment1', 'required' => true, 'sort' => 19],
            'previous_visas' => ['label' => 'Visas Anteriores', 'stage' => 'application_payment1', 'required' => false, 'sort' => 20],
            'english_test_pdf' => ['label' => 'Resultado Test de Inglés (PDF)', 'stage' => 'application_payment1', 'required' => true, 'sort' => 21],

            // Application - Payment 2
            'character_ref' => ['label' => 'Character Reference Form', 'stage' => 'application_payment2', 'required' => true, 'sort' => 30, 'min_count' => 2],
            'childcare_ref' => ['label' => 'Childcare Reference Form', 'stage' => 'application_payment2', 'required' => true, 'sort' => 31, 'min_count' => 3],
            'physician_report' => ['label' => 'Physician Report', 'stage' => 'application_payment2', 'required' => true, 'sort' => 32],
            'interviewer_report' => ['label' => "Interviewer's Report", 'stage' => 'application_payment2', 'required' => true, 'sort' => 33],
            'au_pair_agreement' => ['label' => 'Au Pair Agreement', 'stage' => 'application_payment2', 'required' => true, 'sort' => 34],

            // Visa
            'visa_form' => ['label' => 'Formulario de Solicitud de Visa', 'stage' => 'visa', 'required' => true, 'sort' => 40],
            'visa_photo' => ['label' => 'Foto Tipo Carnet (Visa USA)', 'stage' => 'visa', 'required' => true, 'sort' => 41],
            'ds160' => ['label' => 'DS-160', 'stage' => 'visa', 'required' => true, 'sort' => 42, 'uploaded_by' => 'staff'],
            'ds2019' => ['label' => 'DS-2019', 'stage' => 'visa', 'required' => true, 'sort' => 43, 'uploaded_by' => 'staff'],
            'participation_letter' => ['label' => 'Carta de Participación del Programa', 'stage' => 'visa', 'required' => true, 'sort' => 44, 'uploaded_by' => 'staff'],
            'appointment_instructions' => ['label' => 'Instrucciones de Cita', 'stage' => 'visa', 'required' => true, 'sort' => 45, 'uploaded_by' => 'staff'],
        ];
    }

    /**
     * Get doc types for a specific stage
     */
    public static function documentTypesForStage(string $stage): array
    {
        return array_filter(self::documentTypes(), fn($def) => $def['stage'] === $stage);
    }
}
