<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'short_name',
        'description',
        'logo_path',
        'primary_color',
        'secondary_color',
        'contact_email',
        'contact_phone',
        'address',
        'website_url',
        'settings',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope para instituciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Relación con programas
     */
    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    /**
     * Obtener programas activos de la institución
     */
    public function activePrograms()
    {
        return $this->hasMany(Program::class)->where('is_active', true);
    }

    /**
     * Obtener estadísticas de la institución
     */
    public function getStats()
    {
        return [
            'total_programs' => $this->programs()->count(),
            'active_programs' => $this->activePrograms()->count(),
            'total_applications' => $this->programs()->withCount('applications')->get()->sum('applications_count'),
            'pending_applications' => $this->programs()
                ->whereHas('applications', function($query) {
                    $query->where('status', 'pending');
                })->count(),
            'total_assignments' => $this->programs()->withCount('assignments')->get()->sum('assignments_count'),
        ];
    }

    /**
     * Accessor para el logo URL completo
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        return null;
    }

    /**
     * Accessor para los colores como array
     */
    public function getColorsAttribute()
    {
        return [
            'primary' => $this->primary_color,
            'secondary' => $this->secondary_color,
        ];
    }

    /**
     * Método estático para obtener instituciones para selects
     */
    public static function getForSelect()
    {
        return static::active()->ordered()->pluck('name', 'id');
    }

    /**
     * Método para validar si el código es único
     */
    public static function isCodeUnique($code, $exceptId = null)
    {
        $query = static::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }
        return $query->doesntExist();
    }
}
