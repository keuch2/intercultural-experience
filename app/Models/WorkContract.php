<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'job_offer_id',
        'employer_id',
        'contract_number',
        'contract_type',
        'position_title',
        'job_description',
        'work_location_city',
        'work_location_state',
        'work_location_zip',
        'work_location_address',
        'start_date',
        'end_date',
        'duration_weeks',
        'is_flexible_dates',
        'hourly_rate',
        'hours_per_week',
        'overtime_rate',
        'estimated_total_earnings',
        'payment_frequency',
        'provides_housing',
        'housing_cost_per_week',
        'housing_details',
        'provides_meals',
        'meals_details',
        'provides_transportation',
        'transportation_details',
        'deductions',
        'total_deductions_per_week',
        'contract_pdf_path',
        'job_offer_letter_path',
        'contract_signed',
        'signed_at',
        'signature_path',
        'status',
        'cancellation_reason',
        'cancellation_date',
        'employer_verified',
        'position_verified',
        'verified_by',
        'verification_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cancellation_date' => 'date',
        'signed_at' => 'datetime',
        'verification_date' => 'datetime',
        'deductions' => 'array',
        'is_flexible_dates' => 'boolean',
        'provides_housing' => 'boolean',
        'provides_meals' => 'boolean',
        'provides_transportation' => 'boolean',
        'contract_signed' => 'boolean',
        'employer_verified' => 'boolean',
        'position_verified' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'housing_cost_per_week' => 'decimal:2',
        'total_deductions_per_week' => 'decimal:2',
        'estimated_total_earnings' => 'decimal:2',
    ];

    /**
     * Get the user that owns the contract.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job offer associated with this contract.
     */
    public function jobOffer()
    {
        return $this->belongsTo(JobOffer::class);
    }

    /**
     * Get the employer for this contract.
     */
    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    /**
     * Get the admin who verified this contract.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Calculate net weekly earnings.
     */
    public function getNetWeeklyEarningsAttribute()
    {
        $gross = $this->hourly_rate * $this->hours_per_week;
        $deductions = $this->total_deductions_per_week ?? 0;
        
        if ($this->provides_housing && $this->housing_cost_per_week) {
            $deductions += $this->housing_cost_per_week;
        }
        
        return $gross - $deductions;
    }

    /**
     * Calculate total estimated earnings.
     */
    public function calculateTotalEarnings()
    {
        return $this->net_weekly_earnings * $this->duration_weeks;
    }

    /**
     * Check if contract is active.
     */
    public function isActive()
    {
        return $this->status === 'active' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    /**
     * Check if contract can be signed.
     */
    public function canBeSigned()
    {
        return $this->status === 'pending_signature' && 
               !$this->contract_signed &&
               $this->employer_verified &&
               $this->position_verified;
    }

    /**
     * Sign the contract.
     */
    public function sign($signaturePath = null)
    {
        $this->update([
            'contract_signed' => true,
            'signed_at' => now(),
            'signature_path' => $signaturePath,
            'status' => 'active'
        ]);
    }

    /**
     * Cancel the contract.
     */
    public function cancel($reason)
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancellation_date' => now()
        ]);
    }

    /**
     * Scope for active contracts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for pending signature.
     */
    public function scopePendingSignature($query)
    {
        return $query->where('status', 'pending_signature');
    }

    /**
     * Scope for verified contracts.
     */
    public function scopeVerified($query)
    {
        return $query->where('employer_verified', true)
                    ->where('position_verified', true);
    }
}
