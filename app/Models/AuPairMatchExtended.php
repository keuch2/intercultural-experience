<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuPairMatchExtended extends Model
{
    protected $table = 'au_pair_matches_extended';

    protected $fillable = [
        'au_pair_process_id',
        'match_type',
        'match_date',
        'host_state',
        'host_city',
        'host_address',
        'host_mom_name',
        'host_dad_name',
        'host_email',
        'host_phone',
        'is_active',
        'ended_at',
        'end_reason',
        'sort_order',
    ];

    protected $casts = [
        'match_date' => 'date',
        'is_active' => 'boolean',
        'ended_at' => 'date',
        'sort_order' => 'integer',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(AuPairProcess::class, 'au_pair_process_id');
    }

    public function getMatchTypeLabelAttribute(): string
    {
        return match($this->match_type) {
            'initial' => 'Match Inicial',
            'rematch' => 'Rematch',
            'extension' => 'Extensión',
            default => '-',
        };
    }

    public function getMatchTypeColorAttribute(): string
    {
        return match($this->match_type) {
            'initial' => 'primary',
            'rematch' => 'warning',
            'extension' => 'info',
            default => 'secondary',
        };
    }

    public function getHostFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->host_address,
            $this->host_city,
            $this->host_state,
        ]));
    }
}
