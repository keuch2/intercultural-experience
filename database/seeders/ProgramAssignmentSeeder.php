<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Program;
use App\Models\ProgramAssignment;
use App\Models\Notification;
use Carbon\Carbon;

class ProgramAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios regulares
        $users = User::where('role', 'user')->get();
        
        // Obtener programas activos
        $programs = Program::where('is_active', true)->get();
        
        // Obtener el primer admin para usar como assignedBy
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin || $users->isEmpty() || $programs->isEmpty()) {
            $this->command->info('No hay suficientes usuarios, programas o admins para crear asignaciones.');
            return;
        }

        // Crear asignaciones de ejemplo
        $assignmentData = [
            // Usuario 1 - Múltiples asignaciones en diferentes estados
            [
                'user_index' => 0,
                'program_index' => 0,
                'status' => ProgramAssignment::STATUS_ASSIGNED,
                'assignment_notes' => 'Te hemos asignado este programa basado en tu perfil académico. ¡Es una excelente oportunidad!',
                'is_priority' => true,
                'application_deadline' => Carbon::now()->addDays(15),
                'can_apply' => true,
            ],
            [
                'user_index' => 0,
                'program_index' => 1,
                'status' => ProgramAssignment::STATUS_APPLIED,
                'assignment_notes' => 'Programa de intercambio cultural. Por favor completa todos los requisitos.',
                'applied_at' => Carbon::now()->subDays(5),
                'application_deadline' => Carbon::now()->addDays(30),
                'can_apply' => true,
            ],
            
            // Usuario 2 - Diferentes estados
            [
                'user_index' => 1,
                'program_index' => 0,
                'status' => ProgramAssignment::STATUS_UNDER_REVIEW,
                'assignment_notes' => 'Tu aplicación está siendo revisada por nuestro equipo académico.',
                'applied_at' => Carbon::now()->subDays(10),
                'reviewed_at' => Carbon::now()->subDays(2),
                'application_deadline' => Carbon::now()->addDays(20),
                'can_apply' => false,
            ],
            [
                'user_index' => 1,
                'program_index' => 2,
                'status' => ProgramAssignment::STATUS_ACCEPTED,
                'assignment_notes' => '¡Felicitaciones! Has sido aceptado en este programa.',
                'admin_notes' => 'Excelente expediente académico y motivación.',
                'applied_at' => Carbon::now()->subDays(20),
                'reviewed_at' => Carbon::now()->subDays(15),
                'accepted_at' => Carbon::now()->subDays(10),
                'can_apply' => false,
            ],
            
            // Usuario 3 - Asignación vencida
            [
                'user_index' => 2,
                'program_index' => 1,
                'status' => ProgramAssignment::STATUS_ASSIGNED,
                'assignment_notes' => 'Programa de verano disponible. Fecha límite próxima.',
                'application_deadline' => Carbon::now()->subDays(5), // Vencida
                'can_apply' => true,
                'is_priority' => false,
            ],
            
            // Usuario 4 - Programa completado
            [
                'user_index' => 3,
                'program_index' => 0,
                'status' => ProgramAssignment::STATUS_COMPLETED,
                'assignment_notes' => 'Programa de intercambio completado exitosamente.',
                'admin_notes' => 'Participación destacada durante todo el programa.',
                'applied_at' => Carbon::now()->subDays(90),
                'reviewed_at' => Carbon::now()->subDays(85),
                'accepted_at' => Carbon::now()->subDays(80),
                'completed_at' => Carbon::now()->subDays(10),
                'can_apply' => false,
            ],
            
            // Usuario 5 - Aplicación rechazada
            [
                'user_index' => 4,
                'program_index' => 2,
                'status' => ProgramAssignment::STATUS_REJECTED,
                'assignment_notes' => 'Programa de alto nivel académico.',
                'admin_notes' => 'No cumple con los requisitos mínimos de GPA.',
                'applied_at' => Carbon::now()->subDays(30),
                'reviewed_at' => Carbon::now()->subDays(25),
                'can_apply' => false,
            ],
            
            // Más asignaciones para varios usuarios
            [
                'user_index' => 5,
                'program_index' => 1,
                'status' => ProgramAssignment::STATUS_ASSIGNED,
                'assignment_notes' => 'Oportunidad de voluntariado internacional.',
                'application_deadline' => Carbon::now()->addDays(25),
                'can_apply' => true,
            ],
            
            [
                'user_index' => 6,
                'program_index' => 3,
                'status' => ProgramAssignment::STATUS_APPLIED,
                'assignment_notes' => 'Programa de investigación científica.',
                'applied_at' => Carbon::now()->subDays(7),
                'application_deadline' => Carbon::now()->addDays(40),
                'can_apply' => true,
            ],
        ];

        foreach ($assignmentData as $data) {
            // Verificar que los índices existen
            if (!isset($users[$data['user_index']]) || !isset($programs[$data['program_index']])) {
                continue;
            }

            $user = $users[$data['user_index']];
            $program = $programs[$data['program_index']];

            // Verificar que no existe ya una asignación
            $existingAssignment = ProgramAssignment::where('user_id', $user->id)
                ->where('program_id', $program->id)
                ->first();

            if ($existingAssignment) {
                continue;
            }

            // Crear la asignación
            $assignment = ProgramAssignment::create([
                'user_id' => $user->id,
                'program_id' => $program->id,
                'assigned_by' => $admin->id,
                'status' => $data['status'],
                'assignment_notes' => $data['assignment_notes'],
                'admin_notes' => $data['admin_notes'] ?? null,
                'assigned_at' => Carbon::now()->subDays(rand(1, 30)),
                'applied_at' => $data['applied_at'] ?? null,
                'reviewed_at' => $data['reviewed_at'] ?? null,
                'accepted_at' => $data['accepted_at'] ?? null,
                'completed_at' => $data['completed_at'] ?? null,
                'application_deadline' => $data['application_deadline'] ?? null,
                'can_apply' => $data['can_apply'] ?? true,
                'is_priority' => $data['is_priority'] ?? false,
                'program_data' => [
                    'special_requirements' => $this->getSpecialRequirements($program->category),
                    'assigned_reason' => $this->getAssignmentReason(),
                ],
            ]);

            // Crear notificación de asignación
            $this->createAssignmentNotification($assignment);

            $this->command->info("Asignación creada: {$user->name} -> {$program->name} ({$data['status']})");
        }

        // Crear algunas asignaciones adicionales aleatorias
        $this->createRandomAssignments($users, $programs, $admin, 10);
    }

    /**
     * Crear asignaciones aleatorias adicionales
     */
    private function createRandomAssignments($users, $programs, $admin, $count)
    {
        $statuses = [
            ProgramAssignment::STATUS_ASSIGNED,
            ProgramAssignment::STATUS_APPLIED,
            ProgramAssignment::STATUS_UNDER_REVIEW,
        ];

        for ($i = 0; $i < $count; $i++) {
            $user = $users->random();
            $program = $programs->random();
            
            // Verificar que no existe ya una asignación
            $existingAssignment = ProgramAssignment::where('user_id', $user->id)
                ->where('program_id', $program->id)
                ->first();

            if ($existingAssignment) {
                continue;
            }

            $status = $statuses[array_rand($statuses)];
            $isPriority = rand(1, 4) === 1; // 25% de probabilidad de ser prioritario
            $hasDeadline = rand(1, 3) !== 1; // 66% de probabilidad de tener deadline

            $assignedAt = Carbon::now()->subDays(rand(1, 60));
            $appliedAt = null;
            $reviewedAt = null;
            
            if ($status === ProgramAssignment::STATUS_APPLIED) {
                $appliedAt = $assignedAt->copy()->addDays(rand(1, 10));
            } elseif ($status === ProgramAssignment::STATUS_UNDER_REVIEW) {
                $appliedAt = $assignedAt->copy()->addDays(rand(1, 10));
                $reviewedAt = $appliedAt->copy()->addDays(rand(1, 5));
            }

            $assignment = ProgramAssignment::create([
                'user_id' => $user->id,
                'program_id' => $program->id,
                'assigned_by' => $admin->id,
                'status' => $status,
                'assignment_notes' => $this->getRandomAssignmentNote($program->category),
                'assigned_at' => $assignedAt,
                'applied_at' => $appliedAt,
                'reviewed_at' => $reviewedAt,
                'application_deadline' => $hasDeadline ? 
                    Carbon::now()->addDays(rand(-5, 45)) : null,
                'can_apply' => $status === ProgramAssignment::STATUS_ASSIGNED,
                'is_priority' => $isPriority,
                'program_data' => [
                    'special_requirements' => $this->getSpecialRequirements($program->category),
                    'assigned_reason' => $this->getAssignmentReason(),
                ],
            ]);

            $this->createAssignmentNotification($assignment);

            $this->command->info("Asignación aleatoria: {$user->name} -> {$program->name} ({$status})");
        }
    }

    /**
     * Crear notificación de asignación
     */
    private function createAssignmentNotification($assignment)
    {
        Notification::create([
            'user_id' => $assignment->user_id,
            'title' => 'Nuevo Programa Asignado',
            'message' => "Te han asignado el programa: {$assignment->program->name}. " .
                        "Puedes comenzar tu aplicación desde la app móvil.",
            'category' => 'program_assignment',
            'is_read' => rand(1, 3) === 1, // 33% de probabilidad de estar leída
        ]);
    }

    /**
     * Obtener requisitos especiales según categoría
     */
    private function getSpecialRequirements($category)
    {
        $requirements = [
            'academic' => ['Promedio mínimo 8.5', 'Carta de recomendación académica', 'Ensayo de motivación'],
            'volunteer' => ['Experiencia previa en voluntariado', 'Certificado de antecedentes penales', 'Vacunas requeridas'],
            'internship' => ['CV actualizado', 'Carta de presentación', 'Portfolio de trabajos'],
            'language' => ['Nivel mínimo B1', 'Prueba de idioma', 'Entrevista oral'],
            'cultural' => ['Interés en diversidad cultural', 'Flexibilidad de horarios', 'Seguro de viaje'],
            'research' => ['Propuesta de investigación', 'Supervisor académico', 'Financiamiento aprobado'],
        ];

        return $requirements[$category] ?? ['Documentos básicos', 'Entrevista personal'];
    }

    /**
     * Obtener razón de asignación aleatoria
     */
    private function getAssignmentReason()
    {
        $reasons = [
            'Perfil académico compatible',
            'Experiencia previa relevante',
            'Recomendación institucional',
            'Alta demanda del programa',
            'Especialización en el área',
            'Disponibilidad de cupo',
            'Requisitos cumplidos',
            'Interés expresado anteriormente',
        ];

        return $reasons[array_rand($reasons)];
    }

    /**
     * Obtener nota de asignación aleatoria
     */
    private function getRandomAssignmentNote($category)
    {
        $notes = [
            'academic' => [
                'Excelente oportunidad para tu desarrollo académico.',
                'Programa altamente competitivo con beneficios únicos.',
                'Recomendamos aplicar cuanto antes por la alta demanda.',
            ],
            'volunteer' => [
                'Oportunidad de impacto social significativo.',
                'Experiencia que transformará tu perspectiva global.',
                'Contribuye al desarrollo comunitario internacional.',
            ],
            'internship' => [
                'Experiencia práctica en empresa líder del sector.',
                'Oportunidad de networking profesional internacional.',
                'Desarrollo de habilidades técnicas avanzadas.',
            ],
            'default' => [
                'Programa seleccionado especialmente para tu perfil.',
                'Oportunidad única de crecimiento personal y profesional.',
                'Te invitamos a ser parte de esta experiencia transformadora.',
            ],
        ];

        $categoryNotes = $notes[$category] ?? $notes['default'];
        return $categoryNotes[array_rand($categoryNotes)];
    }
}
