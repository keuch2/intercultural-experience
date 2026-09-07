<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPlacement extends Model
{
    public const STATUSES = [
        'pending' => 'Pendiente', 'in_progress' => 'En proceso', 'documents_complete' => 'Documentación completa',
        'ds_shipped' => 'DS-2019 enviado', 'completed' => 'Completado', 'cancelled' => 'Cancelado',
    ];

    protected $fillable = [
        'program_process_id', 'job_pool_assignment_id', 'sponsor_id', 'status', 'acceptance_date', 'program_start_date',
        'program_end_date', 'terms_accepted_at', 'sevis_number', 'ds2019_number', 'ds_tracking_carrier', 'ds_tracking_number',
        'ds_received_at', 'notes',
    ];

    protected $casts = [
        'acceptance_date' => 'date', 'program_start_date' => 'date', 'program_end_date' => 'date',
        'terms_accepted_at' => 'datetime', 'ds_received_at' => 'date',
    ];

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
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
