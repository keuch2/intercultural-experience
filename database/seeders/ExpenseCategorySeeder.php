<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Gastos de Oficina',
                'code' => 'OFFICE',
                'description' => 'Suministros de oficina, material de escritorio, equipos de oficina',
                'color' => '#28a745',
                'budget_limit' => 500000.00, // 500k PYG
                'requires_approval' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Viajes y Transporte',
                'code' => 'TRAVEL',
                'description' => 'Gastos de viaje, transporte, alojamiento para programas',
                'color' => '#007bff',
                'budget_limit' => 2000000.00, // 2M PYG
                'requires_approval' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Marketing y Publicidad',
                'code' => 'MARKETING',
                'description' => 'Campañas publicitarias, materiales promocionales, eventos',
                'color' => '#fd7e14',
                'budget_limit' => 1500000.00, // 1.5M PYG
                'requires_approval' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Tecnología e IT',
                'code' => 'TECHNOLOGY',
                'description' => 'Software, hardware, servicios de IT, hosting',
                'color' => '#6f42c1',
                'budget_limit' => 1000000.00, // 1M PYG
                'requires_approval' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Servicios Profesionales',
                'code' => 'PROFESSIONAL',
                'description' => 'Consultorías, servicios legales, contables, traducción',
                'color' => '#dc3545',
                'budget_limit' => 3000000.00, // 3M PYG
                'requires_approval' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Capacitación y Desarrollo',
                'code' => 'TRAINING',
                'description' => 'Cursos, seminarios, certificaciones para staff',
                'color' => '#20c997',
                'budget_limit' => 800000.00, // 800k PYG
                'requires_approval' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Seguros y Servicios',
                'code' => 'INSURANCE',
                'description' => 'Seguros para programas, servicios bancarios, comisiones',
                'color' => '#ffc107',
                'budget_limit' => 1200000.00, // 1.2M PYG
                'requires_approval' => true,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Gastos Operacionales',
                'code' => 'OPERATIONAL',
                'description' => 'Servicios públicos, alquiler, mantenimiento',
                'color' => '#6c757d',
                'budget_limit' => 2500000.00, // 2.5M PYG
                'requires_approval' => false,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Eventos y Actividades',
                'code' => 'EVENTS',
                'description' => 'Organización de eventos, actividades para participantes',
                'color' => '#e83e8c',
                'budget_limit' => 1800000.00, // 1.8M PYG
                'requires_approval' => true,
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Gastos Varios',
                'code' => 'MISCELLANEOUS',
                'description' => 'Gastos que no encajan en otras categorías',
                'color' => '#17a2b8',
                'budget_limit' => null, // Sin límite
                'requires_approval' => true,
                'is_active' => true,
                'sort_order' => 10,
            ],
        ];

        foreach ($categories as $categoryData) {
            ExpenseCategory::updateOrCreate(
                ['code' => $categoryData['code']],
                $categoryData
            );
        }

        $this->command->info('Categorías de egresos creadas exitosamente');
    }
}
