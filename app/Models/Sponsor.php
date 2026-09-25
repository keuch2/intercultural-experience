<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sponsor extends Model
{
    protected $fillable = [
        'name',
        'code',
        'country',
        'contact_email',
        'contact_phone',
        'terms_and_conditions',
        'website',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación con JobOffers
     */
    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class);
    }

    /** Ofertas del Pool (motor) asociadas a este sponsor. */
    public function poolOffers(): HasMany
    {
        return $this->hasMany(JobPoolOffer::class);
    }

    /** Job Placements (motor) que viajaron con este sponsor: constancia histórica, nunca se pierde. */
    public function jobPlacements(): HasMany
    {
        return $this->hasMany(JobPlacement::class);
    }

    /** Tiene historial (ofertas legacy o placements): no se puede borrar, solo desactivar. */
    public function hasHistory(): bool
    {
        return $this->jobOffers()->exists() || $this->jobPlacements()->exists() || $this->poolOffers()->exists();
    }

    /**
     * Scope: Solo sponsors activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filtrar por código
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope: Filtrar por país
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Método: Obtener total de job offers activas
     */
    public function activeJobOffersCount(): int
    {
        return $this->jobOffers()->where('status', 'available')->count();
    }
}
