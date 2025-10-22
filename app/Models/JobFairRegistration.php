<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobFairRegistration extends Model
{
    protected $fillable = [
        'job_fair_event_id',
        'user_id',
        'school_id',
        'participant_type',
        'registration_number',
        'registered_at',
        'registration_status',
        'payment_required',
        'amount_paid',
        'payment_date',
        'payment_reference',
        'payment_status',
        'submitted_documents',
        'documents_verified',
        'documents_verified_at',
        'preferred_schools',
        'interview_slots',
        'checked_in_at',
        'interviews_completed',
        'interviewed_with',
        'offers_received',
        'offers_from',
        'placement_successful',
        'placed_at_school_id',
        'placement_date',
        'satisfaction_rating',
        'feedback'
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'payment_date' => 'datetime',
        'documents_verified_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'placement_date' => 'date',
        'submitted_documents' => 'array',
        'preferred_schools' => 'array',
        'interview_slots' => 'array',
        'interviewed_with' => 'array',
        'offers_from' => 'array',
        'payment_required' => 'boolean',
        'documents_verified' => 'boolean',
        'placement_successful' => 'boolean',
        'amount_paid' => 'decimal:2',
    ];

    /**
     * Get the job fair event.
     */
    public function jobFairEvent()
    {
        return $this->belongsTo(JobFairEvent::class);
    }

    /**
     * Get the user (teacher).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the school.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the school where placed.
     */
    public function placedAtSchool()
    {
        return $this->belongsTo(School::class, 'placed_at_school_id');
    }

    /**
     * Check if registration is confirmed.
     */
    public function isConfirmed()
    {
        return $this->registration_status === 'confirmed' &&
               (!$this->payment_required || $this->payment_status === 'paid') &&
               $this->documents_verified;
    }

    /**
     * Check if payment is pending.
     */
    public function isPaymentPending()
    {
        return $this->payment_required && 
               $this->payment_status === 'pending';
    }

    /**
     * Check if documents are pending.
     */
    public function areDocumentsPending()
    {
        return !$this->documents_verified && 
               !empty($this->jobFairEvent->required_documents);
    }

    /**
     * Confirm the registration.
     */
    public function confirm()
    {
        if (!$this->isPaymentPending() && !$this->areDocumentsPending()) {
            $this->update(['registration_status' => 'confirmed']);
            return true;
        }
        return false;
    }

    /**
     * Process payment.
     */
    public function processPayment($amount, $reference)
    {
        $this->update([
            'amount_paid' => $amount,
            'payment_date' => now(),
            'payment_reference' => $reference,
            'payment_status' => 'paid'
        ]);

        // Auto-confirm if documents are also verified
        if ($this->documents_verified) {
            $this->confirm();
        }
    }

    /**
     * Verify documents.
     */
    public function verifyDocuments()
    {
        $this->update([
            'documents_verified' => true,
            'documents_verified_at' => now()
        ]);

        // Auto-confirm if payment is also complete
        if (!$this->isPaymentPending()) {
            $this->confirm();
        }
    }

    /**
     * Check in at event.
     */
    public function checkIn()
    {
        $this->update([
            'checked_in_at' => now(),
            'registration_status' => 'attended'
        ]);
    }

    /**
     * Record an interview.
     */
    public function recordInterview($schoolId)
    {
        $interviewed = $this->interviewed_with ?? [];
        if (!in_array($schoolId, $interviewed)) {
            $interviewed[] = $schoolId;
            $this->update([
                'interviewed_with' => $interviewed,
                'interviews_completed' => count($interviewed)
            ]);
        }
    }

    /**
     * Record an offer.
     */
    public function recordOffer($schoolId)
    {
        $offers = $this->offers_from ?? [];
        if (!in_array($schoolId, $offers)) {
            $offers[] = $schoolId;
            $this->update([
                'offers_from' => $offers,
                'offers_received' => count($offers)
            ]);
        }
    }

    /**
     * Accept placement.
     */
    public function acceptPlacement($schoolId)
    {
        $this->update([
            'placement_successful' => true,
            'placed_at_school_id' => $schoolId,
            'placement_date' => now()
        ]);
    }

    /**
     * Scope for confirmed registrations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('registration_status', 'confirmed');
    }

    /**
     * Scope for attended registrations.
     */
    public function scopeAttended($query)
    {
        return $query->where('registration_status', 'attended');
    }

    /**
     * Scope for successful placements.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('placement_successful', true);
    }

    /**
     * Scope for teacher registrations.
     */
    public function scopeTeachers($query)
    {
        return $query->where('participant_type', 'teacher');
    }

    /**
     * Scope for school registrations.
     */
    public function scopeSchools($query)
    {
        return $query->where('participant_type', 'school');
    }
}
