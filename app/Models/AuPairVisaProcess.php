<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuPairVisaProcess extends Model
{
    protected $fillable = [
        'au_pair_process_id',
        'visa_email_sent',
        'visa_form_path',
        'visa_photo_path',
        'consular_fee_paid',
        'appointment_scheduled',
        'documents_sent_for_appointment',
        'appointment_date',
        'appointment_time',
        'embassy',
        'ds160_path',
        'ds2019_path',
        'participation_letter_path',
        'appointment_instructions_path',
        'document_check_completed',
        'document_check_completed_at',
        'interview_result',
        'interview_result_notes',
        'departure_datetime',
        'arrival_usa_datetime',
        'flight_info',
        'pre_departure_orientation_date',
        'pre_departure_orientation_completed',
    ];

    protected $casts = [
        'visa_email_sent' => 'boolean',
        'consular_fee_paid' => 'boolean',
        'appointment_scheduled' => 'boolean',
        'documents_sent_for_appointment' => 'boolean',
        'appointment_date' => 'date',
        'document_check_completed' => 'boolean',
        'document_check_completed_at' => 'datetime',
        'departure_datetime' => 'datetime',
        'arrival_usa_datetime' => 'datetime',
        'flight_info' => 'array',
        'pre_departure_orientation_date' => 'date',
        'pre_departure_orientation_completed' => 'boolean',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(AuPairProcess::class, 'au_pair_process_id');
    }

    public function getInterviewResultLabelAttribute(): string
    {
        return match($this->interview_result) {
            'pending' => 'Pendiente',
            'approved' => 'Aprobada',
            'denied' => 'Denegada',
            'administrative_process' => 'Proceso Administrativo',
            default => '-',
        };
    }

    public function getInterviewResultColorAttribute(): string
    {
        return match($this->interview_result) {
            'approved' => 'success',
            'denied' => 'danger',
            'administrative_process' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Progress percentage (simple count of completed steps)
     */
    public function getProgressAttribute(): int
    {
        $steps = [
            $this->visa_email_sent,
            $this->consular_fee_paid,
            $this->appointment_scheduled,
            $this->documents_sent_for_appointment,
            $this->document_check_completed,
            $this->interview_result !== 'pending',
            $this->pre_departure_orientation_completed,
        ];
        $completed = collect($steps)->filter()->count();
        return (int) round(($completed / count($steps)) * 100);
    }
}
