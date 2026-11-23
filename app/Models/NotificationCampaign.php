<?php

namespace App\Models;

use App\Support\NotificationCatalog;
use Illuminate\Database\Eloquent\Model;

/**
 * Aviso especial enviado por IE a un grupo de participantes (inmediato o programado).
 * `audience`: {mode: all|segment|users, program_ids[], application_statuses[], stage, season, user_ids[]}.
 */
class NotificationCampaign extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        'scheduled' => 'Programado', 'sending' => 'Enviando', 'sent' => 'Enviado', 'cancelled' => 'Cancelado',
    ];

    public const CHANNELS = ['push' => 'Aviso en la app + notificación push', 'in_app' => 'Solo aviso en la app'];

    public const LINK_TYPES = ['none' => 'Solo abrir el aviso', 'screen' => 'Abrir una sección de la app', 'program' => 'Abrir la ficha de un programa', 'url' => 'Abrir un enlace web'];

    protected $fillable = [
        'title', 'message', 'link_type', 'link_target', 'link_program_id', 'link_url', 'audience', 'channel',
        'is_important', 'scheduled_at', 'status', 'sent_at', 'cancelled_at', 'recipients_count', 'created_by',
    ];

    protected $casts = [
        'audience' => 'array',
        'is_important' => 'boolean',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function linkProgram()
    {
        return $this->belongsTo(Program::class, 'link_program_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'campaign_id');
    }

    public function scopeDue($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)->where(fn ($q) => $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()));
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    /** Datos del aviso para la app (a dónde lleva al tocarlo). */
    public function linkData(): array
    {
        return match ($this->link_type) {
            'screen' => $this->link_target ? ['target' => $this->link_target] : [],
            'program' => $this->link_program_id ? ['target' => 'program', 'program_id' => $this->link_program_id] : [],
            'url' => $this->link_url ? ['target' => 'url', 'url' => $this->link_url] : [],
            default => [],
        };
    }

    public function linkLabel(): ?string
    {
        return match ($this->link_type) {
            'screen' => NotificationCatalog::LINK_TARGETS[$this->link_target] ?? null,
            'program' => $this->linkProgram?->name ? 'Programa: '.$this->linkProgram->name : null,
            'url' => $this->link_url,
            default => null,
        };
    }
}
