<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Sponsor;
use App\Services\ProgramEngine\ModuleCatalog;
use App\Services\ProgramEngine\ProgramDefinition;
use Illuminate\Database\Seeder;

/**
 * Work & Travel USA sobre el motor de programas (spec: "Proceso Work & Travel IE APP.docx").
 * Idempotente: actualiza por slug / keys.
 */
class WorkTravelProgramSeeder extends Seeder
{
    public const SLUG = 'work-travel';

    public function run(): void
    {
        $program = $this->resolveProgram();

        $program->forceFill([
            'slug' => self::SLUG,
            'engine_enabled' => true,
            'is_available_in_app' => true,
            'is_active' => true,
            'modules' => [
                ModuleCatalog::ENGLISH_TEST, ModuleCatalog::VISA, ModuleCatalog::JOB_POOL,
                ModuleCatalog::PLACEMENT, ModuleCatalog::SUPPORT, ModuleCatalog::RESOURCES,
            ],
            'rules' => array_merge($program->rules ?? [], [
                'min_english_level' => 'B1',
                'max_english_attempts' => 3,
                'job_pool_allow_reselect' => false,
                'support_log_types' => ['arrival_followup', 'program_followup', 'incident', 'employer_change', 'final_evaluation', 'participant_report'],
                'visa_sections' => ['c1', 'c2', 'c3', 'c4', 'c5', 'c6'],
            ]),
            'onboarding' => $program->onboarding ?: [
                'title' => 'Postulá a Work & Travel USA',
                'intro' => 'Trabajá legalmente en Estados Unidos durante tus vacaciones de verano con visa J1, viví una experiencia cultural única y mejorá tu inglés.',
                'steps' => [
                    ['title' => 'Admisión', 'body' => 'Completá tus datos y subí tus documentos de admisión.'],
                    ['title' => 'Test de inglés y documentos', 'body' => 'Rendí tu evaluación de inglés (mínimo B1) y, con el pago de inscripción, subí la documentación de aplicación.'],
                    ['title' => 'Pool de ofertas y viaje', 'body' => 'Elegí tu oferta laboral entre las disponibles, completá tu Job Placement y seguí tu proceso de visa J1 hasta viajar.'],
                ],
                'terms_text' => 'Declaro que la información brindada es verídica. Entiendo que IE actúa como agente del programa, que el pago de inscripción no es reembolsable y que IE no garantiza la aprobación de la visa.',
                'requires_adult' => true,
            ],
        ])->save();

        $this->seedStages($program);
        $this->seedGates($program);
        $this->seedChecklist($program);
        $this->seedDocuments($program);
        $this->seedResources($program);
        $this->seedSponsors();

        ProgramDefinition::forget($program);
        $this->command?->info("Work & Travel configurado (program_id={$program->id}, slug=".self::SLUG.').');
    }

    private function resolveProgram(): Program
    {
        if ($existing = Program::where('slug', self::SLUG)->first()) {
            return $existing;
        }

        $legacy = Program::where('main_category', 'IE')
            ->whereIn('subcategory', ['Work and Travel', 'Work & Travel'])
            ->orderBy('id')
            ->first();
        if ($legacy) {
            return $legacy;
        }

        return Program::create([
            'name' => 'Work & Travel USA',
            'slug' => self::SLUG,
            'description' => 'Programa de trabajo temporal de verano en Estados Unidos para estudiantes universitarios, con visa J1.',
            'country' => 'Estados Unidos',
            'main_category' => 'IE',
            'subcategory' => 'Work and Travel',
            'is_active' => true,
            'duration' => '3-4 meses',
        ]);
    }

    private function seedStages(Program $program): void
    {
        $stages = [
            ['key' => 'admission', 'label' => 'Admisión', 'sort_order' => 1, 'mobile_screen' => null,
                'guards' => ['require_docs_approved' => true]],
            ['key' => 'application', 'label' => 'Aplicación', 'sort_order' => 2, 'mobile_screen' => null,
                'guards' => ['require_docs_approved' => true, 'require_gates' => ['inscription'], 'require_checklist' => ['contract_signed'], 'require_english_min_level' => true]],
            ['key' => 'job_pool', 'label' => 'Pool de Ofertas', 'sort_order' => 3, 'mobile_screen' => 'JobPool',
                'guards' => ['require_docs_approved' => false, 'require_job_assignment' => true]],
            ['key' => 'placement', 'label' => 'Job Placement', 'sort_order' => 4, 'mobile_screen' => 'JobPlacement',
                'guards' => ['require_docs_approved' => true, 'require_placement_complete' => true]],
            ['key' => 'visa', 'label' => 'Gestión de Visa J1', 'sort_order' => 5, 'mobile_screen' => 'ProgramVisa',
                'guards' => ['require_docs_approved' => true, 'require_visa_approved' => true]],
            ['key' => 'support', 'label' => 'Support', 'sort_order' => 6, 'mobile_screen' => 'ProgramSupport',
                'guards' => ['manual_only' => true]],
            ['key' => 'completed', 'label' => 'Completado', 'sort_order' => 7, 'is_terminal' => true, 'guards' => ['manual_only' => true]],
        ];
        foreach ($stages as $s) {
            $program->stages()->updateOrCreate(['key' => $s['key']], $s);
        }
    }

    private function seedGates(Program $program): void
    {
        $gates = [
            ['key' => 'inscription', 'label' => 'Pago de inscripción', 'sort_order' => 1, 'concept_match' => 'inscripci'],
            ['key' => 'program', 'label' => 'Pago del programa', 'sort_order' => 2, 'concept_match' => 'programa'],
        ];
        foreach ($gates as $g) {
            $program->paymentGates()->updateOrCreate(['key' => $g['key']], $g);
        }
    }

    private function seedChecklist(Program $program): void
    {
        $items = [
            ['key' => 'welcome_email_sent', 'label' => 'Correo de bienvenida enviado', 'stage_key' => 'application', 'item_type' => 'boolean', 'sort_order' => 1],
            ['key' => 'contract_signed', 'label' => 'Contrato firmado', 'stage_key' => 'application', 'item_type' => 'file', 'required_for_advance' => true, 'sort_order' => 2],
            ['key' => 'sponsor_profile_created', 'label' => 'Perfil en plataforma del Sponsor', 'stage_key' => 'application', 'item_type' => 'boolean', 'sort_order' => 3],
        ];
        foreach ($items as $i) {
            $program->checklistItems()->updateOrCreate(['key' => $i['key']], $i);
        }
    }

    private function seedDocuments(Program $program): void
    {
        $docs = [
            // A) Admisión = Au Pair sin foto de perfil
            ['key' => 'cedula', 'label' => 'Cédula de Identidad', 'stage_key' => 'admission', 'is_required' => true, 'sort_order' => 1],
            ['key' => 'passport', 'label' => 'Pasaporte', 'stage_key' => 'admission', 'is_required' => false, 'sort_order' => 2],
            ['key' => 'drivers_license', 'label' => 'Licencia de Conducir', 'stage_key' => 'admission', 'is_required' => false, 'sort_order' => 3],
            ['key' => 'enrollment_form', 'label' => 'Formulario de Inscripción (firmado)', 'stage_key' => 'admission', 'is_required' => true, 'sort_order' => 5],
            // B) Aplicación — habilitados con el pago de inscripción
            ['key' => 'university_certificate', 'label' => 'Constancia de universidad', 'stage_key' => 'application', 'is_required' => true, 'unlock_gate_key' => 'inscription', 'sort_order' => 10],
            ['key' => 'grades_certificate', 'label' => 'Certificado de notas', 'stage_key' => 'application', 'is_required' => true, 'unlock_gate_key' => 'inscription', 'sort_order' => 11],
            ['key' => 'curriculum', 'label' => 'Curriculum Vitae', 'stage_key' => 'application', 'is_required' => true, 'unlock_gate_key' => 'inscription', 'sort_order' => 12],
            ['key' => 'signed_contract', 'label' => 'Contrato firmado', 'stage_key' => 'application', 'is_required' => true, 'unlock_gate_key' => 'inscription', 'sort_order' => 13],
            ['key' => 'previous_visa', 'label' => 'Visa anterior (opcional)', 'stage_key' => 'application', 'is_required' => false, 'unlock_gate_key' => 'inscription', 'sort_order' => 14],
            // D) Placement — documentos según Sponsor (los carga IE)
            ['key' => 'sponsor_terms', 'label' => 'Términos y condiciones del Sponsor', 'stage_key' => 'placement', 'is_required' => true, 'uploaded_by' => 'staff', 'sort_order' => 20],
            ['key' => 'student_proof', 'label' => 'Student proof', 'stage_key' => 'placement', 'is_required' => true, 'uploaded_by' => 'staff', 'sort_order' => 21],
            ['key' => 'student_interview', 'label' => 'Student Interview', 'stage_key' => 'placement', 'is_required' => true, 'uploaded_by' => 'staff', 'sort_order' => 22],
            // E) Visa J1 = Au Pair
            ['key' => 'visa_form', 'label' => 'Formulario de Solicitud de Visa', 'stage_key' => 'visa', 'is_required' => true, 'sort_order' => 40],
            ['key' => 'visa_photo', 'label' => 'Foto Tipo Carnet (Visa USA)', 'stage_key' => 'visa', 'is_required' => true, 'sort_order' => 41],
            ['key' => 'ds160', 'label' => 'DS-160', 'stage_key' => 'visa', 'is_required' => true, 'uploaded_by' => 'staff', 'sort_order' => 42],
            ['key' => 'ds2019', 'label' => 'DS-2019', 'stage_key' => 'visa', 'is_required' => true, 'uploaded_by' => 'staff', 'sort_order' => 43],
            ['key' => 'participation_letter', 'label' => 'Carta de Participación del Programa', 'stage_key' => 'visa', 'is_required' => true, 'uploaded_by' => 'staff', 'sort_order' => 44],
            ['key' => 'appointment_instructions', 'label' => 'Instrucciones de Cita', 'stage_key' => 'visa', 'is_required' => true, 'uploaded_by' => 'staff', 'sort_order' => 45],
            ['key' => 'sevis_payment_receipt', 'label' => 'Comprobante de Pago SEVIS', 'stage_key' => 'visa', 'is_required' => true, 'uploaded_by' => 'staff', 'sort_order' => 46],
            ['key' => 'pre_departure_orientation', 'label' => 'Reconocimiento de Orientación Pre-partida', 'stage_key' => 'visa', 'is_required' => true, 'uploaded_by' => 'staff', 'section' => 'c6', 'sort_order' => 60],
        ];
        foreach ($docs as $d) {
            $program->documentRequirements()->updateOrCreate(['key' => $d['key']], array_merge(['uploaded_by' => 'participant', 'is_active' => true], $d));
        }
    }

    private function seedResources(Program $program): void
    {
        $titles = [
            'Guía Work & Travel', 'Tips entrevista Visa', 'Derechos laborales', 'Información SEVIS',
            'Travel Period', 'Llegada a USA', 'Housing', 'Check List de Viaje',
        ];
        foreach ($titles as $i => $title) {
            $program->resources()->firstOrCreate(['title' => $title], [
                'description' => null, 'icon' => 'fa-file-pdf', 'file_type' => 'PDF', 'is_active' => true, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function seedSponsors(): void
    {
        foreach ([['AAG', 'AAG'], ['AWA', 'AWA'], ['GH', 'GH']] as [$code, $name]) {
            Sponsor::firstOrCreate(['code' => $code], ['name' => $name, 'country' => 'USA', 'is_active' => true]);
        }
    }
}
