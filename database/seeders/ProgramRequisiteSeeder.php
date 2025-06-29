<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\ProgramRequisite;
use Illuminate\Database\Seeder;

class ProgramRequisiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = Program::all();

        // Requisitos comunes para todos los programas
        $commonRequisites = [
            [
                'name' => 'Pasaporte',
                'description' => 'Copia escaneada del pasaporte vigente',
                'type' => 'document',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'name' => 'Curriculum Vitae',
                'description' => 'CV actualizado en formato PDF',
                'type' => 'document',
                'is_required' => true,
                'order' => 2,
            ],
            [
                'name' => 'Carta de Motivación',
                'description' => 'Carta explicando los motivos para participar en el programa',
                'type' => 'document',
                'is_required' => true,
                'order' => 3,
            ],
            [
                'name' => 'Pago de Inscripción',
                'description' => 'Comprobante de pago de la cuota de inscripción',
                'type' => 'payment',
                'is_required' => true,
                'order' => 4,
            ],
            [
                'name' => 'Completar Formulario de Salud',
                'description' => 'Formulario con información médica relevante',
                'type' => 'action',
                'is_required' => true,
                'order' => 5,
            ],
        ];

        // Requisitos específicos por país
        $countrySpecificRequisites = [
            'España' => [
                [
                    'name' => 'Certificado de Español',
                    'description' => 'Nivel mínimo B1 de español',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 6,
                ],
                [
                    'name' => 'Visa de Estudiante',
                    'description' => 'Copia de la visa de estudiante para España',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 7,
                ],
            ],
            'Francia' => [
                [
                    'name' => 'Certificado de Francés',
                    'description' => 'Nivel mínimo B1 de francés',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 6,
                ],
                [
                    'name' => 'Seguro Médico Internacional',
                    'description' => 'Comprobante de seguro médico con cobertura en Francia',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 7,
                ],
            ],
            'Alemania' => [
                [
                    'name' => 'Certificado de Alemán',
                    'description' => 'Nivel mínimo A2 de alemán',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 6,
                ],
                [
                    'name' => 'Carta de Recomendación',
                    'description' => 'Carta de recomendación académica o profesional',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 7,
                ],
                [
                    'name' => 'Pago de Matrícula',
                    'description' => 'Comprobante de pago de la matrícula del programa',
                    'type' => 'payment',
                    'is_required' => true,
                    'order' => 8,
                ],
            ],
            'Japón' => [
                [
                    'name' => 'Certificado de Inglés',
                    'description' => 'Nivel mínimo B2 de inglés',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 6,
                ],
                [
                    'name' => 'Foto Tamaño Pasaporte',
                    'description' => 'Foto reciente tamaño pasaporte',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 7,
                ],
                [
                    'name' => 'Completar Curso de Orientación',
                    'description' => 'Curso online de orientación cultural japonesa',
                    'type' => 'action',
                    'is_required' => false,
                    'order' => 8,
                ],
            ],
            'Canadá' => [
                [
                    'name' => 'Certificado de Inglés',
                    'description' => 'Nivel mínimo B2 de inglés (IELTS o TOEFL)',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 6,
                ],
                [
                    'name' => 'Permiso de Trabajo',
                    'description' => 'Copia del permiso de trabajo para Canadá',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 7,
                ],
                [
                    'name' => 'Historial Académico',
                    'description' => 'Historial académico oficial traducido',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 8,
                ],
            ],
            'Australia' => [
                [
                    'name' => 'Certificado de Inglés',
                    'description' => 'Nivel mínimo C1 de inglés (IELTS o Cambridge)',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 6,
                ],
                [
                    'name' => 'Visa de Estudiante',
                    'description' => 'Copia de la visa de estudiante para Australia',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 7,
                ],
                [
                    'name' => 'Seguro OSHC',
                    'description' => 'Comprobante de seguro Overseas Student Health Cover',
                    'type' => 'document',
                    'is_required' => true,
                    'order' => 8,
                ],
                [
                    'name' => 'Depósito de Garantía',
                    'description' => 'Comprobante de pago del depósito de garantía',
                    'type' => 'payment',
                    'is_required' => true,
                    'order' => 9,
                ],
            ],
        ];

        foreach ($programs as $program) {
            // Agregar requisitos comunes
            foreach ($commonRequisites as $requisite) {
                ProgramRequisite::create([
                    'program_id' => $program->id,
                    'name' => $requisite['name'],
                    'description' => $requisite['description'],
                    'type' => $requisite['type'],
                    'is_required' => $requisite['is_required'],
                    'order' => $requisite['order'],
                ]);
            }

            // Agregar requisitos específicos por país
            if (isset($countrySpecificRequisites[$program->country])) {
                foreach ($countrySpecificRequisites[$program->country] as $requisite) {
                    ProgramRequisite::create([
                        'program_id' => $program->id,
                        'name' => $requisite['name'],
                        'description' => $requisite['description'],
                        'type' => $requisite['type'],
                        'is_required' => $requisite['is_required'],
                        'order' => $requisite['order'],
                    ]);
                }
            }
        }
    }
}
