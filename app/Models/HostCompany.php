<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostCompany extends Model
{
    protected $fillable = [
        'name',
        'industry',
        'city',
        'state',
        'country',
        'address',
        'contact_person',
        'contact_email',
        'contact_phone',
        'rating',
        'total_participants',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'total_participants' => 'integer',
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
     * Scope: Solo empresas activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filtrar por ciudad
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope: Filtrar por estado
     */
    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Scope: Filtrar por industria
     */
    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    /**
     * Scope: Empresas con buen rating (>= 4.0)
     */
    public function scopeHighRated($query)
    {
        return $query->where('rating', '>=', 4.0);
    }

    /**
     * Método: Incrementar contador de participantes
     */
    public function incrementParticipants(): void
    {
        $this->increment('total_participants');
    }

    /**
     * Método: Actualizar rating promedio
     */
    public function updateRating(float $newRating): void
    {
        $this->update(['rating' => $newRating]);
    }
}
