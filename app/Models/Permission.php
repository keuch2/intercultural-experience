<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'module',
        'action',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope para permisos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar por módulo
     */
    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Obtener permisos agrupados por módulo
     */
    public static function getGroupedByModule(): array
    {
        return static::active()
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module')
            ->map(function ($permissions) {
                return $permissions->map(function ($permission) {
                    return [
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'action' => $permission->action,
                        'description' => $permission->description,
                    ];
                });
            })
            ->toArray();
    }

    /**
     * Obtener lista de módulos únicos
     */
    public static function getModules(): array
    {
        return static::distinct('module')
            ->orderBy('module')
            ->pluck('module')
            ->toArray();
    }

    /**
     * Obtener lista de acciones únicas
     */
    public static function getActions(): array
    {
        return static::distinct('action')
            ->orderBy('action')
            ->pluck('action')
            ->toArray();
    }

    /**
     * Crear permisos CRUD para un módulo
     */
    public static function createCrudPermissions(string $module, string $displayModule): array
    {
        $permissions = [
            'view' => "Ver {$displayModule}",
            'create' => "Crear {$displayModule}",
            'edit' => "Editar {$displayModule}",
            'delete' => "Eliminar {$displayModule}",
        ];

        $created = [];
        foreach ($permissions as $action => $displayName) {
            $permission = static::updateOrCreate([
                'name' => "{$module}.{$action}",
            ], [
                'display_name' => $displayName,
                'module' => $module,
                'action' => $action,
                'description' => "Permite {$displayName} en el módulo {$displayModule}",
                'is_active' => true,
            ]);
            
            $created[] = $permission;
        }

        return $created;
    }

    /**
     * Verificar si un nombre de permiso es válido
     */
    public static function isValidPermissionName(string $name): bool
    {
        return preg_match('/^[a-z_]+\.[a-z_]+$/', $name);
    }
}
