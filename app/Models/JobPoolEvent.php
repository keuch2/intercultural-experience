<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPoolEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['job_pool_offer_id', 'program_process_id', 'event_type', 'actor_id', 'actor_type', 'payload', 'created_at'];

    protected $casts = ['payload' => 'array', 'created_at' => 'datetime'];

    public const LABELS = [
        'offer_published' => 'Oferta publicada', 'offer_updated' => 'Oferta editada', 'offer_pdf_replaced' => 'PDF reemplazado',
        'offer_paused' => 'Oferta pausada', 'offer_reactivated' => 'Oferta reactivada', 'offer_closed' => 'Oferta cerrada',
        'offer_deleted' => 'Oferta eliminada', 'selected' => 'Oferta seleccionada', 'released' => 'Asignación liberada',
        'reassigned' => 'Oferta reasignada', 'positions_exhausted' => 'Cupos agotados',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(JobPoolOffer::class, 'job_pool_offer_id');
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProgramProcess::class, 'program_process_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getLabelAttribute(): string
    {
        return self::LABELS[$this->event_type] ?? $this->event_type;
    }
}
