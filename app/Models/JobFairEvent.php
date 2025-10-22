<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobFairEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_name',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'event_type',
        'venue_name',
        'venue_address',
        'city',
        'state',
        'country',
        'virtual_platform',
        'meeting_link',
        'meeting_id',
        'meeting_password',
        'max_participants',
        'registered_participants',
        'max_schools',
        'registered_schools',
        'registration_opens',
        'registration_closes',
        'status',
        'cancellation_reason',
        'requires_mec_validation',
        'requires_payment',
        'registration_fee',
        'required_documents',
        'total_interviews',
        'total_offers',
        'successful_placements'
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'registration_opens' => 'datetime',
        'registration_closes' => 'datetime',
        'required_documents' => 'array',
        'requires_mec_validation' => 'boolean',
        'requires_payment' => 'boolean',
        'registration_fee' => 'decimal:2',
    ];

    /**
     * Get the registrations for this event.
     */
    public function registrations()
    {
        return $this->hasMany(JobFairRegistration::class);
    }

    /**
     * Get teacher registrations.
     */
    public function teacherRegistrations()
    {
        return $this->registrations()->where('participant_type', 'teacher');
    }

    /**
     * Get school registrations.
     */
    public function schoolRegistrations()
    {
        return $this->registrations()->where('participant_type', 'school');
    }

    /**
     * Get confirmed registrations.
     */
    public function confirmedRegistrations()
    {
        return $this->registrations()->where('registration_status', 'confirmed');
    }

    /**
     * Check if registration is open.
     */
    public function isRegistrationOpen()
    {
        $now = now();
        return $this->status === 'registration_open' &&
               $now >= $this->registration_opens &&
               $now <= $this->registration_closes &&
               $this->hasAvailableSpots();
    }

    /**
     * Check if has available spots.
     */
    public function hasAvailableSpots()
    {
        return !$this->max_participants || 
               $this->registered_participants < $this->max_participants;
    }

    /**
     * Check if has available school spots.
     */
    public function hasAvailableSchoolSpots()
    {
        return !$this->max_schools || 
               $this->registered_schools < $this->max_schools;
    }

    /**
     * Check if event is upcoming.
     */
    public function isUpcoming()
    {
        return $this->event_date > now()->toDateString();
    }

    /**
     * Check if event is past.
     */
    public function isPast()
    {
        return $this->event_date < now()->toDateString();
    }

    /**
     * Check if event is today.
     */
    public function isToday()
    {
        return $this->event_date->isToday();
    }

    /**
     * Register a participant.
     */
    public function registerParticipant($userId = null, $schoolId = null, $type = 'teacher')
    {
        if ($type === 'teacher' && !$this->hasAvailableSpots()) {
            return false;
        }
        
        if ($type === 'school' && !$this->hasAvailableSchoolSpots()) {
            return false;
        }

        $registration = $this->registrations()->create([
            'user_id' => $userId,
            'school_id' => $schoolId,
            'participant_type' => $type,
            'registration_number' => $this->generateRegistrationNumber(),
            'registered_at' => now(),
            'registration_status' => 'pending',
            'payment_required' => $this->requires_payment,
        ]);

        // Update counters
        if ($type === 'teacher') {
            $this->increment('registered_participants');
        } elseif ($type === 'school') {
            $this->increment('registered_schools');
        }

        return $registration;
    }

    /**
     * Generate unique registration number.
     */
    private function generateRegistrationNumber()
    {
        $prefix = 'JF' . $this->event_date->format('Ymd');
        $count = $this->registrations()->count() + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cancel the event.
     */
    public function cancel($reason)
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason
        ]);

        // Notify all registered participants
        $this->registrations()->update([
            'registration_status' => 'cancelled'
        ]);
    }

    /**
     * Calculate success rate.
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_interviews == 0) {
            return 0;
        }
        
        return round(($this->successful_placements / $this->total_interviews) * 100, 2);
    }

    /**
     * Scope for upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>', now())
                    ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope for open registration.
     */
    public function scopeOpenForRegistration($query)
    {
        return $query->where('status', 'registration_open')
                    ->where('registration_closes', '>', now());
    }

    /**
     * Scope for virtual events.
     */
    public function scopeVirtual($query)
    {
        return $query->whereIn('event_type', ['virtual', 'hybrid']);
    }
}
