<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope para roles activos
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
        return $query->orderBy('sort_order')->orderBy('display_name');
    }

    /**
     * Relación con usuarios
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Verificar si el rol tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Verificar si el rol tiene alguno de los permisos dados
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return !empty(array_intersect($permissions, $this->permissions));
    }

    /**
     * Verificar si el rol tiene todos los permisos dados
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return empty(array_diff($permissions, $this->permissions));
    }

    /**
     * Agregar permiso al rol
     */
    public function givePermission(string $permission): self
    {
        if (!$this->hasPermission($permission)) {
            $permissions = $this->permissions ?? [];
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }

        return $this;
    }

    /**
     * Remover permiso del rol
     */
    public function revokePermission(string $permission): self
    {
        if ($this->hasPermission($permission)) {
            $permissions = $this->permissions ?? [];
            $this->permissions = array_values(array_diff($permissions, [$permission]));
            $this->save();
        }

        return $this;
    }

    /**
     * Sincronizar permisos del rol
     */
    public function syncPermissions(array $permissions): self
    {
        $this->permissions = array_values(array_unique($permissions));
        $this->save();

        return $this;
    }

    /**
     * Obtener permisos agrupados por módulo
     */
    public function getPermissionsByModule(): array
    {
        if (!$this->permissions) {
            return [];
        }

        $grouped = [];
        foreach ($this->permissions as $permission) {
            $parts = explode('.', $permission);
            if (count($parts) >= 2) {
                $module = $parts[0];
                $action = $parts[1];
                
                if (!isset($grouped[$module])) {
                    $grouped[$module] = [];
                }
                $grouped[$module][] = $action;
            }
        }

        return $grouped;
    }

    /**
     * Método estático para obtener roles para selects
     */
    public static function getForSelect()
    {
        return static::active()->ordered()->pluck('display_name', 'id');
    }

    /**
     * Verificar si es un rol de sistema (no se puede eliminar)
     */
    public function isSystemRole(): bool
    {
        return $this->is_system;
    }

    /**
     * Obtener el número de usuarios asignados al rol
     */
    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }
}
