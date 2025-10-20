<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisaStatusHistory extends Model
{
    protected $table = 'visa_status_history';

    protected $fillable = [
        'visa_process_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Relación con VisaProcess
     */
    public function visaProcess(): BelongsTo
    {
        return $this->belongsTo(VisaProcess::class);
    }

    /**
     * Relación con User (quien hizo el cambio)
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope: Ordenar por fecha descendente
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('changed_at', 'desc');
    }

    /**
     * Scope: Filtrar por proceso de visa
     */
    public function scopeByVisaProcess($query, $visaProcessId)
    {
        return $query->where('visa_process_id', $visaProcessId);
    }

    /**
     * Scope: Cambios realizados por un usuario específico
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('changed_by', $userId);
    }

    /**
     * Scope: Cambios en un rango de fechas
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('changed_at', [$startDate, $endDate]);
    }

    /**
     * Método: Obtener duración en el estado anterior (en días)
     */
    public function getDurationInPreviousStatus(): ?int
    {
        $previousChange = self::where('visa_process_id', $this->visa_process_id)
            ->where('changed_at', '<', $this->changed_at)
            ->orderBy('changed_at', 'desc')
            ->first();

        if (!$previousChange) {
            return null;
        }

        return $previousChange->changed_at->diffInDays($this->changed_at);
    }

    /**
     * Método: Obtener historial completo de un proceso
     */
    public static function getFullHistory(int $visaProcessId): array
    {
        $history = self::where('visa_process_id', $visaProcessId)
            ->orderBy('changed_at', 'asc')
            ->with('changedBy')
            ->get();

        return $history->map(function ($item) {
            return [
                'id' => $item->id,
                'from_status' => $item->from_status,
                'to_status' => $item->to_status,
                'changed_by' => $item->changedBy->name ?? 'Sistema',
                'changed_at' => $item->changed_at->format('Y-m-d H:i:s'),
                'notes' => $item->notes,
                'duration_days' => $item->getDurationInPreviousStatus(),
            ];
        })->toArray();
    }
}
