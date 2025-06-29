<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'nationality', 
        'birth_date', 'address', 'bank_info', 'email_verified_at'
    ];

    protected $casts = [
        'bank_info' => 'array',
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function points()
    {
        return $this->hasMany(Point::class);
    }

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function programAssignments()
    {
        return $this->hasMany(ProgramAssignment::class);
    }

    public function assignedPrograms()
    {
        return $this->belongsToManyThrough(
            Program::class,
            ProgramAssignment::class,
            'user_id',
            'id',
            'id',
            'program_id'
        );
    }

    public function activeAssignments()
    {
        return $this->hasMany(ProgramAssignment::class)->active();
    }

    public function assignmentsAssignedByMe()
    {
        return $this->hasMany(ProgramAssignment::class, 'assigned_by');
    }

    // Métodos auxiliares para asignaciones
    public function hasAssignmentFor($programId)
    {
        return $this->programAssignments()
            ->where('program_id', $programId)
            ->exists();
    }

    public function getAssignmentFor($programId)
    {
        return $this->programAssignments()
            ->where('program_id', $programId)
            ->first();
    }

    public function canApplyTo($programId)
    {
        $assignment = $this->getAssignmentFor($programId);
        return $assignment && $assignment->canBeAppliedTo();
    }

    public function getActiveAssignmentsCount()
    {
        return $this->activeAssignments()->count();
    }
}
