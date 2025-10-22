<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'iso2',
        'region',
        'flag_emoji',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Relación many-to-many con Programs
     */
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_country')
            ->withPivot(['is_primary', 'display_order', 'specific_locations'])
            ->withTimestamps()
            ->orderBy('program_country.display_order');
    }

    /**
     * Scope para países activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por display_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Scope para filtrar por región
     */
    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Obtener el nombre completo con emoji
     */
    public function getFullNameAttribute(): string
    {
        return ($this->flag_emoji ? $this->flag_emoji . ' ' : '') . $this->name;
    }

    /**
     * Obtener programas donde este país es el principal
     */
    public function primaryPrograms(): BelongsToMany
    {
        return $this->programs()->wherePivot('is_primary', true);
    }
}
