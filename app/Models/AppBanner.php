<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Banner del Inicio de la app (público y/o participante), gestionado desde el admin.
 */
class AppBanner extends Model
{
    public const TYPES = ['image' => 'Imagen', 'text' => 'Texto'];

    public const AUDIENCES = ['all' => 'Todos', 'public' => 'Sin sesión (Inicio público)', 'participants' => 'Participantes (con sesión)'];

    public const LINK_TYPES = ['none' => 'Sin enlace', 'program' => 'Programa de la app', 'url' => 'Enlace web'];

    protected $fillable = [
        'type', 'title', 'subtitle', 'badge', 'image_path', 'background_color', 'button_label', 'link_type',
        'link_program_id', 'link_url', 'audience', 'program_id', 'sort_order', 'starts_at', 'ends_at', 'is_active', 'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function linkProgram(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'link_program_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /** Activos y dentro de su vigencia. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')->orderByDesc('id');
    }

    public function scopeForPublic(Builder $query): Builder
    {
        return $query->whereIn('audience', ['public', 'all'])->whereNull('program_id');
    }

    /** Para un participante: banners de participantes/todos, sin programa o de un programa al que postuló. */
    public function scopeForParticipant(Builder $query, User $user): Builder
    {
        $programIds = Application::where('user_id', $user->id)->visibleToParticipant()->pluck('program_id');

        return $query->whereIn('audience', ['participants', 'all'])
            ->where(fn ($q) => $q->whereNull('program_id')->orWhereIn('program_id', $programIds));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    /** Estado de vigencia para el admin. */
    public function getScheduleStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'expired';
        }

        return 'live';
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'badge' => $this->badge,
            'image_url' => $this->image_url,
            'background_color' => $this->background_color,
            'button_label' => $this->button_label,
            'link' => match ($this->link_type) {
                'program' => $this->link_program_id ? ['type' => 'program', 'program_id' => $this->link_program_id] : null,
                'url' => $this->link_url ? ['type' => 'url', 'url' => $this->link_url] : null,
                default => null,
            },
        ];
    }
}
