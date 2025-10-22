<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthDeclaration extends Model
{
    protected $fillable = [
        'user_id',
        'has_diseases',
        'diseases_detail',
        'has_allergies',
        'allergies_detail',
        'has_dietary_restrictions',
        'dietary_restrictions_detail',
        'has_learning_disabilities',
        'learning_disabilities_detail',
        'has_physical_limitations',
        'physical_limitations_detail',
        'under_medical_treatment',
        'medical_treatment_detail',
        'takes_medication',
        'medication_detail',
        'can_lift_25_pounds',
        'allergic_to_pets',
        'pet_allergies_detail',
        'declaration_date'
    ];

    protected $casts = [
        'declaration_date' => 'date',
        'has_diseases' => 'boolean',
        'has_allergies' => 'boolean',
        'has_dietary_restrictions' => 'boolean',
        'has_learning_disabilities' => 'boolean',
        'has_physical_limitations' => 'boolean',
        'under_medical_treatment' => 'boolean',
        'takes_medication' => 'boolean',
        'can_lift_25_pounds' => 'boolean',
        'allergic_to_pets' => 'boolean',
    ];

    /**
     * Get the user that owns the health declaration.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
