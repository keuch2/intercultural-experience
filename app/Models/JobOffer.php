<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOffer extends Model
{
    protected $fillable = [
        'job_offer_id',
        'sponsor_id',
        'host_company_id',
        'position',
        'description',
        'city',
        'state',
        'salary_min',
        'salary_max',
        'hours_per_week',
        'housing_type',
        'housing_cost',
        'total_slots',
        'available_slots',
        'required_english_level',
        'required_gender',
        'start_date',
        'end_date',
        'status',
        'requirements',
        'benefits',
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'housing_cost' => 'decimal:2',
        'total_slots' => 'integer',
        'available_slots' => 'integer',
        'hours_per_week' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relación con Sponsor
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    /**
     * Relación con HostCompany
     */
    public function hostCompany(): BelongsTo
    {
        return $this->belongsTo(HostCompany::class);
    }

    /**
     * Relación con Reservations
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(JobOfferReservation::class);
    }

    /**
     * Scope: Solo ofertas disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where('available_slots', '>', 0);
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
     * Scope: Filtrar por nivel de inglés
     */
    public function scopeByEnglishLevel($query, $level)
    {
        return $query->where('required_english_level', $level);
    }

    /**
     * Scope: Filtrar por género
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('required_gender', $gender)
            ->orWhere('required_gender', 'any');
    }

    /**
     * Scope: Filtrar por rango de fechas
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_date', '>=', $startDate)
            ->where('end_date', '<=', $endDate);
    }

    /**
     * Método: Verificar si hay cupos disponibles
     */
    public function hasAvailableSlots(): bool
    {
        return $this->available_slots > 0 && $this->status === 'available';
    }

    /**
     * Método: Reservar un cupo
     */
    public function reserveSlot(): bool
    {
        if (!$this->hasAvailableSlots()) {
            return false;
        }

        $this->decrement('available_slots');
        
        if ($this->available_slots === 0) {
            $this->update(['status' => 'full']);
        }

        return true;
    }

    /**
     * Método: Liberar un cupo (cancelación)
     */
    public function releaseSlot(): void
    {
        $this->increment('available_slots');
        
        if ($this->status === 'full' && $this->available_slots > 0) {
            $this->update(['status' => 'available']);
        }
    }

    /**
     * Método: Calcular score de matching con un participante
     */
    public function calculateMatchScore(User $user): int
    {
        $score = 0;

        // Nivel de inglés (40 puntos)
        if ($user->englishEvaluations()->exists()) {
            $bestEvaluation = $user->englishEvaluations()
                ->orderBy('score', 'desc')
                ->first();
            
            if ($this->meetsEnglishRequirement($bestEvaluation->cefr_level)) {
                $score += 40;
            }
        }

        // Género (20 puntos)
        if ($this->required_gender === 'any' || $user->gender === $this->required_gender) {
            $score += 20;
        }

        // Disponibilidad de fechas (30 puntos)
        // Aquí se podría verificar disponibilidad del usuario
        $score += 30;

        // Ubicación preferida (10 puntos)
        // Se podría verificar si el usuario tiene preferencia por esta ubicación
        $score += 10;

        return $score;
    }

    /**
     * Método: Verificar si cumple requisito de inglés
     */
    private function meetsEnglishRequirement(string $userLevel): bool
    {
        $levels = ['A2', 'B1', 'B1+', 'B2', 'C1', 'C2'];
        $requiredIndex = array_search($this->required_english_level, $levels);
        $userIndex = array_search($userLevel, $levels);

        return $userIndex !== false && $userIndex >= $requiredIndex;
    }

    /**
     * Método: Obtener ofertas recomendadas para un usuario
     */
    public static function getRecommendedForUser(User $user, int $limit = 10)
    {
        $offers = self::available()->get();
        
        $scored = $offers->map(function ($offer) use ($user) {
            return [
                'offer' => $offer,
                'score' => $offer->calculateMatchScore($user),
            ];
        });

        return $scored->sortByDesc('score')
            ->take($limit)
            ->pluck('offer');
    }
}
