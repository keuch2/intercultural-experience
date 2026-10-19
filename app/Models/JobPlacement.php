<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPlacement extends Model
{
    /** Estados elegibles (ds_shipped queda solo como legado de datos viejos). */
    public const STATUSES = [
        'pending' => 'Pendiente', 'in_progress' => 'Oferta elegida · entrevistas', 'job_accepted' => 'Aceptación laboral confirmada',
        'documents_complete' => 'Documentación del Sponsor completa', 'completed' => 'DS-2019 emitido · Placement completo', 'cancelled' => 'Cancelado',
    ];

    public const INTERVIEW_PENDING = 'pending';

    public const INTERVIEW_APPROVED = 'approved';

    public const INTERVIEW_NOT_APPROVED = 'not_approved';

    public const INTERVIEW_NOT_APPLICABLE = 'not_applicable';

    public const INTERVIEW_RESULTS = [
        self::INTERVIEW_PENDING => 'Pendiente', self::INTERVIEW_APPROVED => 'Aprobado',
        self::INTERVIEW_NOT_APPROVED => 'No aprobado', self::INTERVIEW_NOT_APPLICABLE => 'No aplica',
    ];

    protected $fillable = [
        'program_process_id', 'job_pool_assignment_id', 'sponsor_id', 'status', 'acceptance_date',
        'sponsor_interview_date', 'sponsor_interview_result', 'employer_interview_date', 'employer_interview_result', 'job_accepted_at',
        'program_start_date',
        'program_end_date', 'terms_accepted_at', 'sevis_number', 'ds2019_number', 'ds_tracking_carrier', 'ds_tracking_number',
        'ds_received_at', 'notes',
    ];

    protected $casts = [
        'acceptance_date' => 'date', 'program_start_date' => 'date', 'program_end_date' => 'date',
        'terms_accepted_at' => 'datetime', 'ds_received_at' => 'date',
        'sponsor_interview_date' => 'date', 'employer_interview_date' => 'date', 'job_accepted_at' => 'date',
    ];

    /** Entrevistas superadas: cada una aprobada o "no aplica" (no ambas "no aplica"). */
    public function interviewsPassed(): bool
    {
        $ok = [self::INTERVIEW_APPROVED, self::INTERVIEW_NOT_APPLICABLE];
        $results = [$this->sponsor_interview_result ?? self::INTERVIEW_PENDING, $this->employer_interview_result ?? self::INTERVIEW_PENDING];

        return in_array($results[0], $ok, true) && in_array($results[1], $ok, true) && in_array(self::INTERVIEW_APPROVED, $results, true);
    }

    public function interviewFailed(): bool
    {
        return in_array(self::INTERVIEW_NOT_APPROVED, [$this->sponsor_interview_result, $this->employer_interview_result], true);
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProgramProcess::class, 'program_process_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(JobPoolAssignment::class, 'job_pool_assignment_id');
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ($this->status === 'ds_shipped' ? 'Documentación del Sponsor completa' : $this->status);
    }
}
