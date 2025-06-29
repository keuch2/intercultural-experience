<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\ProgramForm;
use App\Models\FormField;

class WorkStudyFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar un programa para asociar el formulario (o crear uno de ejemplo)
        $program = Program::where('name', 'LIKE', '%Work%Study%')->first();
        
        if (!$program) {
            $program = Program::create([
                'name' => 'Work & Study Program',
                'description' => 'Programa de intercambio cultural Work & Study',
                'country' => 'Estados Unidos',
                'category' => 'Work & Study',
                'location' => 'Varios destinos',
                'duration' => '12 meses',
                'cost' => 5000.00,
                'currency_id' => 2, // USD
                'is_active' => true,
            ]);
        }

        // Crear el formulario
        $form = ProgramForm::create([
            'program_id' => $program->id,
            'name' => 'Formulario de Inscripción Work & Study',
            'description' => 'Formulario oficial para la inscripción al programa Work & Study',
            'version' => '1.0',
            'is_active' => true,
            'is_published' => true,
            'requires_signature' => true,
            'requires_parent_signature' => true,
            'min_age' => 18,
            'max_age' => 30,
            'sections' => [
                ['name' => 'personal_info', 'title' => 'Información Personal'],
                ['name' => 'contact_info', 'title' => 'Información de Contacto'],
                ['name' => 'academic_work', 'title' => 'Información Académica y Laboral'],
                ['name' => 'program_details', 'title' => 'Detalles del Programa'],
                ['name' => 'health_info', 'title' => 'Información de Salud'],
                ['name' => 'emergency_contact', 'title' => 'Contacto de Emergencia'],
                ['name' => 'terms_acceptance', 'title' => 'Términos y Condiciones'],
            ],
            'terms_and_conditions' => 'Los participantes deben cumplir con todos los requisitos del programa Work & Study según las condiciones establecidas por IE Intercultural Experience...'
        ]);

        // Campos del formulario basados en el PDF
        $fields = [
            // Información Personal
            [
                'section_name' => 'personal_info',
                'field_key' => 'full_name',
                'field_label' => 'Nombre y Apellido',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'section_name' => 'personal_info',
                'field_key' => 'birth_date',
                'field_label' => 'Fecha de Nacimiento',
                'field_type' => 'date',
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'section_name' => 'personal_info',
                'field_key' => 'age',
                'field_label' => 'Edad',
                'field_type' => 'number',
                'is_required' => true,
                'sort_order' => 3,
                'validation_rules' => ['min' => 18, 'max' => 30],
            ],
            [
                'section_name' => 'personal_info',
                'field_key' => 'ci_number',
                'field_label' => 'C.I. Nro.',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 4,
            ],
            [
                'section_name' => 'personal_info',
                'field_key' => 'passport_number',
                'field_label' => 'Pasaporte Nro.',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 5,
            ],
            [
                'section_name' => 'personal_info',
                'field_key' => 'passport_expiry',
                'field_label' => 'Vencimiento del Pasaporte',
                'field_type' => 'date',
                'is_required' => true,
                'sort_order' => 6,
            ],

            // Información de Contacto
            [
                'section_name' => 'contact_info',
                'field_key' => 'email',
                'field_label' => 'E-mail',
                'field_type' => 'email',
                'is_required' => true,
                'sort_order' => 7,
            ],
            [
                'section_name' => 'contact_info',
                'field_key' => 'address',
                'field_label' => 'Domicilio',
                'field_type' => 'textarea',
                'is_required' => true,
                'sort_order' => 8,
            ],
            [
                'section_name' => 'contact_info',
                'field_key' => 'city',
                'field_label' => 'Ciudad',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 9,
            ],
            [
                'section_name' => 'contact_info',
                'field_key' => 'phone',
                'field_label' => 'Número de teléfono',
                'field_type' => 'tel',
                'is_required' => true,
                'sort_order' => 10,
            ],
            [
                'section_name' => 'contact_info',
                'field_key' => 'mobile',
                'field_label' => 'Celular Nro.',
                'field_type' => 'tel',
                'is_required' => true,
                'sort_order' => 11,
            ],

            // Información Académica y Laboral
            [
                'section_name' => 'academic_work',
                'field_key' => 'current_study',
                'field_label' => 'Estudio actual',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 12,
            ],
            [
                'section_name' => 'academic_work',
                'field_key' => 'institution',
                'field_label' => 'Institución',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 13,
            ],
            [
                'section_name' => 'academic_work',
                'field_key' => 'current_job',
                'field_label' => 'Trabajo actual',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 14,
            ],
            [
                'section_name' => 'academic_work',
                'field_key' => 'work_address',
                'field_label' => 'Dirección laboral',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 15,
            ],
            [
                'section_name' => 'academic_work',
                'field_key' => 'work_phone',
                'field_label' => 'Teléfono de trabajo',
                'field_type' => 'tel',
                'is_required' => false,
                'sort_order' => 16,
            ],

            // Detalles del Programa
            [
                'section_name' => 'program_details',
                'field_key' => 'destination',
                'field_label' => 'Destino (país y ciudad)',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 17,
            ],
            [
                'section_name' => 'program_details',
                'field_key' => 'language_school',
                'field_label' => 'Escuela de Idiomas',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 18,
            ],
            [
                'section_name' => 'program_details',
                'field_key' => 'course_type',
                'field_label' => 'Tipo de Curso',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 19,
            ],
            [
                'section_name' => 'program_details',
                'field_key' => 'duration',
                'field_label' => 'Duración',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 20,
            ],
            [
                'section_name' => 'program_details',
                'field_key' => 'accommodation_type',
                'field_label' => 'Tipo de Alojamiento',
                'field_type' => 'radio',
                'options' => ['HOMESTAY', 'RESIDENCIA', 'SIN ALOJAMIENTO'],
                'is_required' => true,
                'sort_order' => 21,
            ],
            [
                'section_name' => 'program_details',
                'field_key' => 'accommodation_duration',
                'field_label' => 'Duración del Alojamiento',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 22,
            ],
            [
                'section_name' => 'program_details',
                'field_key' => 'course_start_date',
                'field_label' => 'Fecha de Inicio del Curso',
                'field_type' => 'date',
                'is_required' => true,
                'sort_order' => 23,
            ],
            [
                'section_name' => 'program_details',
                'field_key' => 'medical_insurance',
                'field_label' => 'Seguro Médico',
                'field_type' => 'radio',
                'options' => ['SI', 'NO'],
                'is_required' => true,
                'sort_order' => 24,
            ],
            [
                'section_name' => 'program_details',
                'field_key' => 'transfer_service',
                'field_label' => 'Transfer In/Out',
                'field_type' => 'radio',
                'options' => ['LLEGADA', 'SALIDA'],
                'is_required' => false,
                'sort_order' => 25,
            ],

            // Información de Salud
            [
                'section_name' => 'health_info',
                'field_key' => 'health_conditions',
                'field_label' => 'Indique si padece alguna enfermedad, tiene alergias o presenta restricciones alimenticias',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 26,
            ],
            [
                'section_name' => 'health_info',
                'field_key' => 'attention_disorders',
                'field_label' => 'Indique si tiene algún trastorno de atención o si requiere alguna atención especial',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 27,
            ],
            [
                'section_name' => 'health_info',
                'field_key' => 'medications',
                'field_label' => 'Indique si está tomando algún medicamento o si se encuentra bajo algún tipo de tratamiento',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 28,
            ],

            // Contacto de Emergencia
            [
                'section_name' => 'emergency_contact',
                'field_key' => 'emergency_phone',
                'field_label' => 'Teléfono de emergencia',
                'field_type' => 'tel',
                'is_required' => true,
                'sort_order' => 29,
            ],
            [
                'section_name' => 'emergency_contact',
                'field_key' => 'emergency_contact_name',
                'field_label' => 'Nombre del contacto de emergencia',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 30,
            ],
            [
                'section_name' => 'emergency_contact',
                'field_key' => 'emergency_email',
                'field_label' => 'Email de emergencia',
                'field_type' => 'email',
                'is_required' => true,
                'sort_order' => 31,
            ],

            // Términos y Condiciones
            [
                'section_name' => 'terms_acceptance',
                'field_key' => 'terms_accepted',
                'field_label' => 'Acepto los términos y condiciones del programa',
                'field_type' => 'boolean',
                'is_required' => true,
                'sort_order' => 32,
            ],
            [
                'section_name' => 'terms_acceptance',
                'field_key' => 'participant_signature',
                'field_label' => 'Firma del Participante',
                'field_type' => 'signature',
                'is_required' => true,
                'sort_order' => 33,
            ],
            [
                'section_name' => 'terms_acceptance',
                'field_key' => 'signature_clarification',
                'field_label' => 'Aclaración de Firma',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 34,
            ],
        ];

        // Crear todos los campos
        foreach ($fields as $fieldData) {
            FormField::create([
                'program_form_id' => $form->id,
                'section_name' => $fieldData['section_name'],
                'field_key' => $fieldData['field_key'],
                'field_label' => $fieldData['field_label'],
                'field_type' => $fieldData['field_type'],
                'description' => $fieldData['description'] ?? null,
                'placeholder' => $fieldData['placeholder'] ?? null,
                'options' => $fieldData['options'] ?? null,
                'validation_rules' => $fieldData['validation_rules'] ?? null,
                'is_required' => $fieldData['is_required'],
                'sort_order' => $fieldData['sort_order'],
            ]);
        }

        // Agregar campos adicionales para menores de edad
        FormField::create([
            'program_form_id' => $form->id,
            'section_name' => 'parent_consent',
            'field_key' => 'is_minor',
            'field_label' => '¿Es menor de 18 años?',
            'field_type' => 'boolean',
            'is_required' => true,
            'sort_order' => 35,
        ]);

        FormField::create([
            'program_form_id' => $form->id,
            'section_name' => 'parent_consent',
            'field_key' => 'parent_name',
            'field_label' => 'Nombre del padre/madre/tutor',
            'field_type' => 'text',
            'is_required' => false,
            'sort_order' => 36,
            'conditional_logic' => [
                'condition' => 'and',
                'rules' => [
                    ['field' => 'is_minor', 'operator' => '=', 'value' => true]
                ]
            ]
        ]);

        FormField::create([
            'program_form_id' => $form->id,
            'section_name' => 'parent_consent',
            'field_key' => 'parent_signature',
            'field_label' => 'Firma del padre/madre/tutor',
            'field_type' => 'signature',
            'is_required' => false,
            'sort_order' => 37,
            'conditional_logic' => [
                'condition' => 'and',
                'rules' => [
                    ['field' => 'is_minor', 'operator' => '=', 'value' => true]
                ]
            ]
        ]);

        $this->command->info('Formulario Work & Study creado exitosamente con ' . $form->fields()->count() . ' campos.');
    }
}
