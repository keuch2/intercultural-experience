<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use Illuminate\Support\Facades\DB;

class ProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'name' => 'Work & Travel',
                'description' => 'Programa de trabajo temporal de verano en Estados Unidos para estudiantes universitarios. Permite trabajar legalmente durante 3-4 meses en temporada alta con visa J1.',
                'main_category' => 'IE',
                'subcategory' => 'Work and Travel',
                'country' => 'USA, Canadá',
                'duration' => '3-4 meses',
                'cost' => 2400.00,
                'is_active' => 1,
            ],
            [
                'name' => 'Au Pair',
                'description' => 'Programa cultural de cuidado de niños en familias estadounidenses. Duración de 12 meses, con posibilidad de extensión. Incluye alojamiento, comida y estipendio semanal.',
                'main_category' => 'IE',
                'subcategory' => 'Au Pair',
                'country' => 'USA, Australia, Nueva Zelanda',
                'duration' => '12 meses',
                'cost' => 1800.00,
                'is_active' => 1,
            ],
            [
                'name' => 'Teachers Program',
                'description' => 'Programa de intercambio para profesores calificados. Enseña en escuelas estadounidenses con visa J1. Requiere certificación docente y validación del MEC.',
                'main_category' => 'IE',
                'subcategory' => 'Teacher\'s Program',
                'country' => 'USA, UK, Canadá',
                'duration' => '1-3 años',
                'cost' => 3000.00,
                'is_active' => 1,
            ],
            [
                'name' => 'Intern & Trainee',
                'description' => 'Programa de prácticas profesionales y capacitación en empresas estadounidenses. Ideal para estudiantes y recién graduados que buscan experiencia internacional.',
                'main_category' => 'IE',
                'subcategory' => 'Internship',
                'country' => 'USA, Europa, Asia',
                'duration' => '6-18 meses',
                'cost' => 2500.00,
                'is_active' => 1,
            ],
            [
                'name' => 'Higher Education - Study Abroad',
                'description' => 'Programas de educación superior en universidades estadounidenses. Incluye programas de pregrado, posgrado y certificaciones profesionales.',
                'main_category' => 'IE',
                'subcategory' => 'Study Abroad',
                'country' => 'USA, UK, Canadá, Australia',
                'duration' => 'Variable',
                'cost' => 5000.00,
                'is_active' => 1,
            ],
            [
                'name' => 'Work & Study',
                'description' => 'Combina estudios de idiomas o certificaciones con trabajo a tiempo parcial. Ideal para estudiantes que buscan mejorar su inglés mientras trabajan.',
                'main_category' => 'IE',
                'subcategory' => 'Work and Travel',
                'country' => 'Irlanda, Malta, Australia',
                'duration' => '6-12 meses',
                'cost' => 2800.00,
                'is_active' => 1,
            ],
            [
                'name' => 'Language Program',
                'description' => 'Programas intensivos de inmersión en inglés. Aprende en un ambiente internacional con actividades culturales y sociales.',
                'main_category' => 'IE',
                'subcategory' => 'Language Exchange',
                'country' => 'USA, UK, Canadá, Malta',
                'duration' => '2-12 meses',
                'cost' => 2000.00,
                'is_active' => 1,
            ],
        ];

        foreach ($programs as $programData) {
            Program::updateOrCreate(
                ['name' => $programData['name']],
                $programData
            );
        }

        $this->command->info('✅ 7 programas creados/actualizados exitosamente');
        
        // Mostrar resumen
        $this->command->info('📊 Programas en el sistema:');
        Program::all()->each(function($p) {
            $this->command->line("  - {$p->name} (\${$p->cost})");
        });
    }
}
