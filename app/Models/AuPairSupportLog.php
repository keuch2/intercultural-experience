<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuPairSupportLog extends Model
{
    protected $fillable = [
        'au_pair_process_id',
        'log_type',
        'title',
        'description',
        'log_date',
        'follow_up_number',
        'severity',
        'resolution',
        'resolved_at',
        'logged_by',
    ];

    protected $casts = [
        'log_date' => 'date',
        'resolved_at' => 'datetime',
        'follow_up_number' => 'integer',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(AuPairProcess::class, 'au_pair_process_id');
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function getLogTypeLabelAttribute(): string
    {
        return match($this->log_type) {
            'arrival_followup' => 'Seguimiento de Llegada',
            'monthly_followup' => 'Seguimiento Mensual',
            'incident' => 'Incidente',
            'experience_evaluation' => 'Evaluación de Experiencia',
            default => '-',
        };
    }

    public function getLogTypeColorAttribute(): string
    {
        return match($this->log_type) {
            'arrival_followup' => 'info',
            'monthly_followup' => 'primary',
            'incident' => 'danger',
            'experience_evaluation' => 'success',
            default => 'secondary',
        };
    }

    public function getSeverityLabelAttribute(): string
    {
        return match($this->severity) {
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            'critical' => 'Crítica',
            default => '-',
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark',
            default => 'secondary',
        };
    }
}
