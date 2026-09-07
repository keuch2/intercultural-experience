<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramVisaProcess extends Model
{
    protected $fillable = [
        'program_process_id',
        'visa_email_sent', 'visa_form_path', 'visa_photo_path', 'consular_fee_paid', 'appointment_scheduled',
        'documents_sent_for_appointment',
        'appointment_date', 'appointment_time', 'embassy',
        'ds160_path', 'ds2019_path', 'participation_letter_path', 'appointment_instructions_path',
        'document_check_completed', 'document_check_completed_at',
        'interview_result', 'interview_result_notes',
        'departure_datetime', 'arrival_usa_datetime', 'flight_info',
        'pre_departure_orientation_date', 'pre_departure_orientation_completed',
        'extra',
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
        'extra' => 'array',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProgramProcess::class, 'program_process_id');
    }

    /** Progreso 0..100 sobre los hitos principales (misma lógica que AuPairVisaProcess). */
    public function getProgressAttribute(): int
    {
        $steps = [
            (bool) $this->visa_email_sent,
            (bool) $this->consular_fee_paid,
            (bool) $this->appointment_scheduled,
            (bool) $this->document_check_completed,
            $this->interview_result === 'approved',
            $this->departure_datetime !== null,
            (bool) $this->pre_departure_orientation_completed,
        ];
        $done = count(array_filter($steps));

        return (int) round(($done / count($steps)) * 100);
    }
}
