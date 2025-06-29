<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FormTemplate;

class FormTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Plantilla Work & Study
            [
                'name' => 'Formulario Work & Study',
                'description' => 'Formulario completo para programas de trabajo y estudio en el extranjero',
                'category' => 'work_study',
                'icon' => 'fas fa-briefcase',
                'template_data' => [
                    'sections' => [
                        [
                            'name' => 'informacion_personal',
                            'title' => 'Información Personal',
                            'description' => 'Datos básicos del participante'
                        ],
                        [
                            'name' => 'contacto_emergencia',
                            'title' => 'Contacto de Emergencia',
                            'description' => 'Información de contacto en caso de emergencia'
                        ],
                        [
                            'name' => 'experiencia_educativa',
                            'title' => 'Experiencia Educativa',
                            'description' => 'Historial académico y educativo'
                        ],
                        [
                            'name' => 'experiencia_laboral',
                            'title' => 'Experiencia Laboral',
                            'description' => 'Historial profesional y laboral'
                        ],
                        [
                            'name' => 'idiomas',
                            'title' => 'Competencias Lingüísticas',
                            'description' => 'Nivel de idiomas y certificaciones'
                        ],
                        [
                            'name' => 'motivacion',
                            'title' => 'Motivación y Objetivos',
                            'description' => 'Razones para participar y expectativas'
                        ]
                    ],
                    'fields' => [
                        // Información Personal
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'nombre_completo',
                            'field_label' => 'Nombre Completo',
                            'field_type' => 'text',
                            'placeholder' => 'Ingrese su nombre completo',
                            'is_required' => true,
                            'sort_order' => 1
                        ],
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'fecha_nacimiento',
                            'field_label' => 'Fecha de Nacimiento',
                            'field_type' => 'date',
                            'is_required' => true,
                            'sort_order' => 2
                        ],
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'genero',
                            'field_label' => 'Género',
                            'field_type' => 'select',
                            'options' => ['Masculino', 'Femenino', 'Otro', 'Prefiero no decir'],
                            'is_required' => true,
                            'sort_order' => 3
                        ],
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'nacionalidad',
                            'field_label' => 'Nacionalidad',
                            'field_type' => 'country',
                            'is_required' => true,
                            'sort_order' => 4
                        ],
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'numero_pasaporte',
                            'field_label' => 'Número de Pasaporte',
                            'field_type' => 'text',
                            'placeholder' => 'Número de pasaporte válido',
                            'is_required' => true,
                            'sort_order' => 5
                        ],
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'direccion',
                            'field_label' => 'Dirección Completa',
                            'field_type' => 'address',
                            'is_required' => true,
                            'sort_order' => 6
                        ],
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'telefono',
                            'field_label' => 'Número de Teléfono',
                            'field_type' => 'tel',
                            'placeholder' => '+595 XXX XXX XXX',
                            'is_required' => true,
                            'sort_order' => 7
                        ],
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'email',
                            'field_label' => 'Correo Electrónico',
                            'field_type' => 'email',
                            'placeholder' => 'correo@ejemplo.com',
                            'is_required' => true,
                            'sort_order' => 8
                        ],
                        
                        // Contacto de Emergencia
                        [
                            'section_name' => 'contacto_emergencia',
                            'field_key' => 'contacto_nombre',
                            'field_label' => 'Nombre del Contacto',
                            'field_type' => 'text',
                            'placeholder' => 'Nombre completo del contacto de emergencia',
                            'is_required' => true,
                            'sort_order' => 9
                        ],
                        [
                            'section_name' => 'contacto_emergencia',
                            'field_key' => 'contacto_relacion',
                            'field_label' => 'Relación',
                            'field_type' => 'select',
                            'options' => ['Padre', 'Madre', 'Hermano/a', 'Esposo/a', 'Amigo/a', 'Otro'],
                            'is_required' => true,
                            'sort_order' => 10
                        ],
                        [
                            'section_name' => 'contacto_emergencia',
                            'field_key' => 'contacto_telefono',
                            'field_label' => 'Teléfono del Contacto',
                            'field_type' => 'tel',
                            'placeholder' => '+595 XXX XXX XXX',
                            'is_required' => true,
                            'sort_order' => 11
                        ],
                        
                        // Experiencia Educativa
                        [
                            'section_name' => 'experiencia_educativa',
                            'field_key' => 'nivel_educativo',
                            'field_label' => 'Nivel Educativo Máximo',
                            'field_type' => 'select',
                            'options' => ['Secundaria', 'Terciario', 'Universitario', 'Postgrado', 'Maestría', 'Doctorado'],
                            'is_required' => true,
                            'sort_order' => 12
                        ],
                        [
                            'section_name' => 'experiencia_educativa',
                            'field_key' => 'institucion_educativa',
                            'field_label' => 'Institución Educativa',
                            'field_type' => 'text',
                            'placeholder' => 'Nombre de la institución',
                            'is_required' => true,
                            'sort_order' => 13
                        ],
                        [
                            'section_name' => 'experiencia_educativa',
                            'field_key' => 'carrera_campo',
                            'field_label' => 'Carrera o Campo de Estudio',
                            'field_type' => 'text',
                            'placeholder' => 'Área de especialización',
                            'is_required' => true,
                            'sort_order' => 14
                        ],
                        
                        // Experiencia Laboral
                        [
                            'section_name' => 'experiencia_laboral',
                            'field_key' => 'tiene_experiencia',
                            'field_label' => '¿Tiene experiencia laboral?',
                            'field_type' => 'boolean',
                            'is_required' => true,
                            'sort_order' => 15
                        ],
                        [
                            'section_name' => 'experiencia_laboral',
                            'field_key' => 'experiencia_detalle',
                            'field_label' => 'Describa su experiencia laboral',
                            'field_type' => 'textarea',
                            'placeholder' => 'Describa sus empleos anteriores, responsabilidades y logros...',
                            'is_required' => false,
                            'sort_order' => 16
                        ],
                        
                        // Idiomas
                        [
                            'section_name' => 'idiomas',
                            'field_key' => 'nivel_ingles',
                            'field_label' => 'Nivel de Inglés',
                            'field_type' => 'select',
                            'options' => ['Básico', 'Intermedio', 'Avanzado', 'Nativo'],
                            'is_required' => true,
                            'sort_order' => 17
                        ],
                        [
                            'section_name' => 'idiomas',
                            'field_key' => 'certificacion_ingles',
                            'field_label' => 'Certificación de Inglés',
                            'field_type' => 'select',
                            'options' => ['Ninguna', 'TOEFL', 'IELTS', 'Cambridge', 'Otro'],
                            'is_required' => false,
                            'sort_order' => 18
                        ],
                        [
                            'section_name' => 'idiomas',
                            'field_key' => 'otros_idiomas',
                            'field_label' => 'Otros Idiomas',
                            'field_type' => 'textarea',
                            'placeholder' => 'Mencione otros idiomas que domina y su nivel...',
                            'is_required' => false,
                            'sort_order' => 19
                        ],
                        
                        // Motivación
                        [
                            'section_name' => 'motivacion',
                            'field_key' => 'motivacion_participar',
                            'field_label' => '¿Por qué quiere participar en este programa?',
                            'field_type' => 'textarea',
                            'placeholder' => 'Explique sus motivaciones y expectativas...',
                            'is_required' => true,
                            'sort_order' => 20
                        ],
                        [
                            'section_name' => 'motivacion',
                            'field_key' => 'objetivos_profesionales',
                            'field_label' => 'Objetivos Profesionales',
                            'field_type' => 'textarea',
                            'placeholder' => 'Describa cómo este programa contribuirá a sus objetivos profesionales...',
                            'is_required' => true,
                            'sort_order' => 21
                        ]
                    ]
                ],
                'default_settings' => [
                    'requires_signature' => true,
                    'requires_parent_signature' => false,
                    'min_age' => 18,
                    'max_age' => 30,
                    'terms_and_conditions' => 'Al participar en este programa, acepto cumplir con todas las regulaciones y requisitos establecidos.'
                ]
            ],
            
            // Plantilla Au Pair
            [
                'name' => 'Formulario Au Pair',
                'description' => 'Formulario especializado para programas Au Pair',
                'category' => 'au_pair',
                'icon' => 'fas fa-baby',
                'template_data' => [
                    'sections' => [
                        [
                            'name' => 'informacion_personal',
                            'title' => 'Información Personal',
                            'description' => 'Datos básicos del participante'
                        ],
                        [
                            'name' => 'experiencia_ninos',
                            'title' => 'Experiencia con Niños',
                            'description' => 'Experiencia cuidando niños y referencias'
                        ],
                        [
                            'name' => 'habilidades',
                            'title' => 'Habilidades y Preferencias',
                            'description' => 'Habilidades especiales y preferencias de familia'
                        ]
                    ],
                    'fields' => [
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'nombre_completo',
                            'field_label' => 'Nombre Completo',
                            'field_type' => 'text',
                            'is_required' => true,
                            'sort_order' => 1
                        ],
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'fecha_nacimiento',
                            'field_label' => 'Fecha de Nacimiento',
                            'field_type' => 'date',
                            'is_required' => true,
                            'sort_order' => 2
                        ],
                        [
                            'section_name' => 'experiencia_ninos',
                            'field_key' => 'experiencia_cuidado',
                            'field_label' => 'Experiencia cuidando niños',
                            'field_type' => 'textarea',
                            'placeholder' => 'Describa su experiencia cuidando niños...',
                            'is_required' => true,
                            'sort_order' => 3
                        ],
                        [
                            'section_name' => 'habilidades',
                            'field_key' => 'puede_conducir',
                            'field_label' => '¿Puede conducir?',
                            'field_type' => 'boolean',
                            'is_required' => true,
                            'sort_order' => 4
                        ]
                    ]
                ],
                'default_settings' => [
                    'requires_signature' => true,
                    'requires_parent_signature' => true,
                    'min_age' => 18,
                    'max_age' => 26
                ]
            ],
            
            // Plantilla Académica
            [
                'name' => 'Formulario Académico',
                'description' => 'Formulario para programas académicos y de intercambio estudiantil',
                'category' => 'academic',
                'icon' => 'fas fa-graduation-cap',
                'template_data' => [
                    'sections' => [
                        [
                            'name' => 'informacion_personal',
                            'title' => 'Información Personal',
                            'description' => 'Datos básicos del estudiante'
                        ],
                        [
                            'name' => 'informacion_academica',
                            'title' => 'Información Académica',
                            'description' => 'Historial y planes académicos'
                        ]
                    ],
                    'fields' => [
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'nombre_completo',
                            'field_label' => 'Nombre Completo',
                            'field_type' => 'text',
                            'is_required' => true,
                            'sort_order' => 1
                        ],
                        [
                            'section_name' => 'informacion_academica',
                            'field_key' => 'universidad_actual',
                            'field_label' => 'Universidad Actual',
                            'field_type' => 'text',
                            'is_required' => true,
                            'sort_order' => 2
                        ],
                        [
                            'section_name' => 'informacion_academica',
                            'field_key' => 'promedio_academico',
                            'field_label' => 'Promedio Académico',
                            'field_type' => 'number',
                            'is_required' => true,
                            'sort_order' => 3
                        ]
                    ]
                ],
                'default_settings' => [
                    'requires_signature' => true,
                    'min_age' => 17,
                    'max_age' => 35
                ]
            ],
            
            // Plantilla Voluntariado
            [
                'name' => 'Formulario Voluntariado',
                'description' => 'Formulario para programas de voluntariado internacional',
                'category' => 'volunteer',
                'icon' => 'fas fa-hands-helping',
                'template_data' => [
                    'sections' => [
                        [
                            'name' => 'informacion_personal',
                            'title' => 'Información Personal',
                            'description' => 'Datos básicos del voluntario'
                        ],
                        [
                            'name' => 'motivacion_voluntariado',
                            'title' => 'Motivación para Voluntariado',
                            'description' => 'Razones y experiencia en voluntariado'
                        ]
                    ],
                    'fields' => [
                        [
                            'section_name' => 'informacion_personal',
                            'field_key' => 'nombre_completo',
                            'field_label' => 'Nombre Completo',
                            'field_type' => 'text',
                            'is_required' => true,
                            'sort_order' => 1
                        ],
                        [
                            'section_name' => 'motivacion_voluntariado',
                            'field_key' => 'experiencia_voluntariado',
                            'field_label' => 'Experiencia previa en voluntariado',
                            'field_type' => 'textarea',
                            'placeholder' => 'Describa su experiencia previa en voluntariado...',
                            'is_required' => false,
                            'sort_order' => 2
                        ],
                        [
                            'section_name' => 'motivacion_voluntariado',
                            'field_key' => 'areas_interes',
                            'field_label' => 'Áreas de Interés',
                            'field_type' => 'checkbox',
                            'options' => ['Educación', 'Salud', 'Medio Ambiente', 'Construcción', 'Animales', 'Deportes'],
                            'is_required' => true,
                            'sort_order' => 3
                        ]
                    ]
                ],
                'default_settings' => [
                    'requires_signature' => true,
                    'min_age' => 16
                ]
            ],
            
            // Plantilla General
            [
                'name' => 'Formulario Básico',
                'description' => 'Formulario básico para cualquier tipo de programa',
                'category' => 'general',
                'icon' => 'fas fa-file-alt',
                'template_data' => [
                    'sections' => [
                        [
                            'name' => 'informacion_basica',
                            'title' => 'Información Básica',
                            'description' => 'Datos esenciales del participante'
                        ]
                    ],
                    'fields' => [
                        [
                            'section_name' => 'informacion_basica',
                            'field_key' => 'nombre_completo',
                            'field_label' => 'Nombre Completo',
                            'field_type' => 'text',
                            'placeholder' => 'Ingrese su nombre completo',
                            'is_required' => true,
                            'sort_order' => 1
                        ],
                        [
                            'section_name' => 'informacion_basica',
                            'field_key' => 'email',
                            'field_label' => 'Correo Electrónico',
                            'field_type' => 'email',
                            'placeholder' => 'correo@ejemplo.com',
                            'is_required' => true,
                            'sort_order' => 2
                        ],
                        [
                            'section_name' => 'informacion_basica',
                            'field_key' => 'telefono',
                            'field_label' => 'Teléfono',
                            'field_type' => 'tel',
                            'placeholder' => '+595 XXX XXX XXX',
                            'is_required' => true,
                            'sort_order' => 3
                        ],
                        [
                            'section_name' => 'informacion_basica',
                            'field_key' => 'fecha_nacimiento',
                            'field_label' => 'Fecha de Nacimiento',
                            'field_type' => 'date',
                            'is_required' => true,
                            'sort_order' => 4
                        ]
                    ]
                ],
                'default_settings' => [
                    'requires_signature' => false,
                    'min_age' => 16
                ]
            ]
        ];

        foreach ($templates as $template) {
            FormTemplate::create($template);
        }
    }
}
