<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Crear permisos básicos para todos los módulos
        $this->createPermissions();
        
        // Crear roles predefinidos
        $this->createRoles();

        $this->command->info('Roles y permisos creados exitosamente');
    }

    private function createPermissions()
    {
        $modules = [
            'users' => 'Usuarios',
            'programs' => 'Programas',
            'applications' => 'Aplicaciones',
            'assignments' => 'Asignaciones',
            'institutions' => 'Instituciones',
            'currencies' => 'Monedas',
            'reports' => 'Reportes',
            'finance' => 'Finanzas',
            'rewards' => 'Recompensas',
            'support_tickets' => 'Tickets de Soporte',
            'activity_logs' => 'Registros de Actividad',
            'roles' => 'Roles y Permisos',
            'settings' => 'Configuraciones',
        ];

        foreach ($modules as $module => $displayName) {
            Permission::createCrudPermissions($module, $displayName);
            
            // Permisos adicionales específicos
            $additionalPermissions = $this->getAdditionalPermissions($module, $displayName);
            foreach ($additionalPermissions as $action => $display) {
                Permission::updateOrCreate([
                    'name' => "{$module}.{$action}",
                ], [
                    'display_name' => $display,
                    'module' => $module,
                    'action' => $action,
                    'description' => "Permite {$display} en {$displayName}",
                    'is_active' => true,
                ]);
            }
        }
    }

    private function getAdditionalPermissions(string $module, string $displayName): array
    {
        $additionalPermissions = [
            'users' => [
                'impersonate' => 'Impersonar Usuarios',
                'export' => 'Exportar Usuarios',
                'reset_password' => 'Resetear Contraseñas',
            ],
            'programs' => [
                'assign' => 'Asignar Programas',
                'export' => 'Exportar Programas',
                'duplicate' => 'Duplicar Programas',
            ],
            'applications' => [
                'approve' => 'Aprobar Aplicaciones',
                'reject' => 'Rechazar Aplicaciones',
                'export' => 'Exportar Aplicaciones',
            ],
            'reports' => [
                'export' => 'Exportar Reportes',
                'advanced' => 'Reportes Avanzados',
            ],
            'finance' => [
                'approve_transactions' => 'Aprobar Transacciones',
                'export' => 'Exportar Finanzas',
            ],
            'activity_logs' => [
                'cleanup' => 'Limpiar Registros',
                'export' => 'Exportar Logs',
            ],
            'settings' => [
                'system' => 'Configuraciones del Sistema',
                'backup' => 'Respaldos',
            ],
        ];

        return $additionalPermissions[$module] ?? [];
    }

    private function createRoles()
    {
        // Super Admin (rol de sistema)
        Role::updateOrCreate([
            'name' => 'super_admin',
        ], [
            'display_name' => 'Super Administrador',
            'description' => 'Acceso completo a todas las funciones del sistema. Este rol no puede ser eliminado.',
            'permissions' => Permission::pluck('name')->toArray(),
            'is_active' => true,
            'is_system' => true,
            'sort_order' => 1,
        ]);

        // Admin General
        $adminPermissions = Permission::whereIn('module', [
            'users', 'programs', 'applications', 'assignments', 'institutions',
            'currencies', 'reports', 'finance', 'rewards', 'support_tickets'
        ])->pluck('name')->toArray();
        
        Role::updateOrCreate([
            'name' => 'admin',
        ], [
            'display_name' => 'Administrador',
            'description' => 'Acceso a la mayoría de funciones administrativas',
            'permissions' => $adminPermissions,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 2,
        ]);

        // Coordinador de Programas
        $coordinatorPermissions = Permission::whereIn('module', [
            'programs', 'applications', 'assignments', 'users'
        ])->whereIn('action', ['view', 'create', 'edit', 'assign', 'approve', 'reject'])
        ->pluck('name')->toArray();
        
        Role::updateOrCreate([
            'name' => 'program_coordinator',
        ], [
            'display_name' => 'Coordinador de Programas',
            'description' => 'Gestiona programas, aplicaciones y asignaciones de usuarios',
            'permissions' => $coordinatorPermissions,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 3,
        ]);

        // Operador Financiero
        $financePermissions = Permission::whereIn('module', [
            'finance', 'currencies', 'reports'
        ])->pluck('name')->toArray();
        
        // Agregar permisos de solo lectura para usuarios y programas
        $financePermissions = array_merge($financePermissions, [
            'users.view', 'programs.view', 'applications.view'
        ]);
        
        Role::updateOrCreate([
            'name' => 'finance_operator',
        ], [
            'display_name' => 'Operador Financiero',
            'description' => 'Gestiona aspectos financieros, monedas y reportes financieros',
            'permissions' => $financePermissions,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 4,
        ]);

        // Soporte
        $supportPermissions = Permission::whereIn('module', [
            'support_tickets', 'users'
        ])->whereIn('action', ['view', 'edit', 'create'])
        ->pluck('name')->toArray();
        
        // Agregar permisos de solo lectura
        $supportPermissions = array_merge($supportPermissions, [
            'programs.view', 'applications.view', 'assignments.view'
        ]);
        
        Role::updateOrCreate([
            'name' => 'support_agent',
        ], [
            'display_name' => 'Agente de Soporte',
            'description' => 'Gestiona tickets de soporte y consultas de usuarios',
            'permissions' => $supportPermissions,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 5,
        ]);

        // Solo Lectura
        $readOnlyPermissions = Permission::where('action', 'view')
            ->pluck('name')->toArray();
        
        Role::updateOrCreate([
            'name' => 'read_only',
        ], [
            'display_name' => 'Solo Lectura',
            'description' => 'Acceso de solo lectura a todos los módulos',
            'permissions' => $readOnlyPermissions,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 6,
        ]);
    }
}
