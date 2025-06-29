<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'name' => 'Intercultural Experience in Spain',
                'description' => 'Programa de intercambio cultural en universidades españolas con enfoque en arte y cultura.',
                'country' => 'España',
                'category' => 'Universitario',
                'is_active' => true,
            ],
            [
                'name' => 'Intercultural Experience in France',
                'description' => 'Programa de inmersión lingüística y cultural en ciudades francesas.',
                'country' => 'Francia',
                'category' => 'Lingüístico',
                'is_active' => true,
            ],
            [
                'name' => 'Intercultural Experience in Germany',
                'description' => 'Programa de intercambio técnico y profesional en empresas alemanas.',
                'country' => 'Alemania',
                'category' => 'Profesional',
                'is_active' => true,
            ],
            [
                'name' => 'Intercultural Experience in Japan',
                'description' => 'Programa de inmersión cultural y educativa en Japón.',
                'country' => 'Japón',
                'category' => 'Cultural',
                'is_active' => true,
            ],
            [
                'name' => 'Intercultural Experience in Canada',
                'description' => 'Programa de trabajo y estudio en universidades canadienses.',
                'country' => 'Canadá',
                'category' => 'Trabajo y Estudio',
                'is_active' => true,
            ],
            [
                'name' => 'Intercultural Experience in Australia',
                'description' => 'Programa de intercambio académico en universidades australianas.',
                'country' => 'Australia',
                'category' => 'Académico',
                'is_active' => false,
            ],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}
