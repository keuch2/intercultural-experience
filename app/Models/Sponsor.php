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
