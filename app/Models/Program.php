<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Program extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'name',
        'description',
        'country',
        'main_category',
        'subcategory',
        'image',
        'is_active',
        'location', 
        'start_date', 
        'end_date', 
        'application_deadline',
        'duration', 
        'credits', 
        'capacity', 
        'available_spots', 
        'image_url', 
        'cost', 
        'currency_id', 
        'institution_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date', 
        'application_deadline' => 'date',
        'cost' => 'decimal:4',
    ];

    /**
     * Accessors que se agregan automáticamente a las respuestas JSON
     * Para sincronización con React Native
     */
    protected $appends = [
        'image_url',
        'status',
        'available_slots',
    ];

    // Relationships
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    
    public function requisites()
    {
        return $this->hasMany(ProgramRequisite::class)->orderBy('order');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function forms()
    {
        return $this->hasMany(ProgramForm::class);
    }

    public function activeForm()
    {
        return $this->hasOne(ProgramForm::class)->where('is_active', true)->where('is_published', true);
    }

    public function assignments()
    {
        return $this->hasMany(ProgramAssignment::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToManyThrough(
            User::class,
            ProgramAssignment::class,
            'program_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function activeAssignments()
    {
        return $this->hasMany(ProgramAssignment::class)->active();
    }

    // Accessors
    public function getFormattedCostAttribute()
    {
        if (!$this->currency) {
            return number_format($this->cost, 2);
        }
        
        return $this->currency->symbol . ' ' . number_format($this->cost, 2);
    }

    public function getCostInPygAttribute()
    {
        if (!$this->currency) {
            return $this->cost;
        }
        
        return $this->currency->convertToPyg($this->cost);
    }

    public function getFormattedCostInPygAttribute()
    {
        return '₲ ' . number_format($this->cost_in_pyg, 0);
    }

    /**
     * Get the full URL for the program image
     * Accessor para sincronizar con React Native que espera 'image_url'
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        
        // Si ya es una URL completa, retornarla
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        
        // Construir URL completa con config('app.url') para evitar problemas de CORS
        $baseUrl = config('app.url');
        
        // La imagen en BD ya incluye el path relativo desde storage/
        // Ejemplo: "programs/1760927704_Results_copy_4.png"
        return $baseUrl . '/storage/' . $this->image;
    }

    /**
     * Get the status as string
     * Accessor para sincronizar con React Native que espera 'status' en lugar de 'is_active'
     */
    public function getStatusAttribute()
    {
        return $this->is_active ? 'active' : 'inactive';
    }

    /**
     * Get available slots (alias para sincronizar con React Native)
     */
    public function getAvailableSlotsAttribute()
    {
        // Si ya existe el campo available_spots en BD, usarlo
        if (isset($this->attributes['available_slots'])) {
            return $this->attributes['available_slots'];
        }
        
        // Si no, calcularlo dinámicamente
        return $this->getAvailableSpots();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeIe($query)
    {
        return $query->where('main_category', 'IE');
    }

    public function scopeYfu($query)
    {
        return $query->where('main_category', 'YFU');
    }

    public function scopeByMainCategory($query, $category)
    {
        return $query->where('main_category', $category);
    }

    public function scopeBySubcategory($query, $subcategory)
    {
        return $query->where('subcategory', $subcategory);
    }

    // Métodos auxiliares para asignaciones
    public function getAssignmentStats()
    {
        return ProgramAssignment::getAssignmentStats($this->id);
    }

    public function hasAssignmentFor($userId)
    {
        return $this->assignments()
            ->where('user_id', $userId)
            ->exists();
    }

    public function getAssignmentFor($userId)
    {
        return $this->assignments()
            ->where('user_id', $userId)
            ->first();
    }

    public function getAvailableSpots()
    {
        $acceptedAssignments = $this->assignments()
            ->where('status', ProgramAssignment::STATUS_ACCEPTED)
            ->count();
        
        return max(0, $this->capacity - $acceptedAssignments);
    }

    public function canAcceptMoreParticipants()
    {
        return $this->getAvailableSpots() > 0;
    }
}