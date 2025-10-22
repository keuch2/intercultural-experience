<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;

class ParticipantTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los programas activos
        $allPrograms = Program::where('is_active', 1)->get();
        
        if ($allPrograms->isEmpty()) {
            $this->command->error('No hay programas activos en el sistema. Por favor crea al menos un programa primero.');
            return;
        }
        
        $this->command->info("📋 Encontrados {$allPrograms->count()} programas activos");

        // Estados disponibles (según enum en BD)
        $statuses = ['pending', 'in_review', 'approved', 'rejected'];
        
        // Etapas por programa
        $stages = [
            'Work & Travel USA' => ['Inscripción', 'Selección Job Offer', 'Confirmación', 'Proceso Visa', 'Pre-Viaje'],
            'Au Pair USA' => ['Inscripción', 'Creación Perfil', 'Matching', 'Proceso Visa', 'Training'],
            'Teachers Program' => ['Inscripción', 'Validación MEC', 'Job Fair', 'Matching Escuela', 'Proceso Visa'],
            'default' => ['Inscripción', 'Evaluación', 'Documentación', 'Aprobación', 'Preparación'],
        ];

        // Datos de participantes paraguayos
        $participants = [
            // WORK & TRAVEL
            [
                'full_name' => 'Juan Carlos Martínez López',
                'birth_date' => '2002-03-15',
                'cedula' => '4.567.890',
                'passport_number' => 'AB123456',
                'passport_expiry' => '2027-06-30',
                'phone' => '+595 981 234 567',
                'address' => 'Av. España 1234',
                'city' => 'Asunción',
                'status' => 'approved',
                'current_stage' => 'Confirmación',
                'progress_percentage' => 60,
            ],
            [
                'full_name' => 'María Belén Rodríguez',
                'birth_date' => '2001-07-22',
                'cedula' => '5.123.456',
                'passport_number' => 'CD789012',
                'passport_expiry' => '2028-01-15',
                'phone' => '+595 982 345 678',
                'address' => 'Calle Palma 567',
                'city' => 'Asunción',
                'status' => 'in_review',
                'current_stage' => 'Selección Job Offer',
                'progress_percentage' => 40,
            ],
            [
                'full_name' => 'Luis Fernando Benítez',
                'birth_date' => '2003-01-10',
                'cedula' => '6.234.567',
                'passport_number' => 'EF345678',
                'passport_expiry' => '2029-03-20',
                'phone' => '+595 983 456 789',
                'address' => 'Av. Mariscal López 890',
                'city' => 'Asunción',
                'status' => 'pending',
                'current_stage' => 'Inscripción',
                'progress_percentage' => 10,
            ],
            
            // AU PAIR
            [
                'full_name' => 'Ana Sofía González',
                'birth_date' => '2000-05-18',
                'cedula' => '3.987.654',
                'passport_number' => 'GH901234',
                'passport_expiry' => '2026-11-25',
                'phone' => '+595 984 567 890',
                'address' => 'Barrio San Vicente 123',
                'city' => 'Fernando de la Mora',
                'status' => 'approved',
                'current_stage' => 'Matching',
                'progress_percentage' => 75,
            ],
            [
                'full_name' => 'Claudia Patricia Valdez',
                'birth_date' => '2002-09-30',
                'cedula' => '4.876.543',
                'passport_number' => 'IJ567890',
                'passport_expiry' => '2027-04-10',
                'phone' => '+595 985 678 901',
                'address' => 'Villa Morra 456',
                'city' => 'Asunción',
                'status' => 'in_review',
                'current_stage' => 'Creación Perfil',
                'progress_percentage' => 45,
            ],
            [
                'full_name' => 'Gabriela Fernández',
                'birth_date' => '2001-12-05',
                'cedula' => '5.765.432',
                'passport_number' => 'KL123456',
                'passport_expiry' => '2028-08-15',
                'phone' => '+595 986 789 012',
                'address' => 'Lambaré Centro 789',
                'city' => 'Lambaré',
                'status' => 'approved',
                'current_stage' => 'Training',
                'progress_percentage' => 100,
            ],
            
            // TEACHERS
            [
                'full_name' => 'Roberto Carlos Acosta',
                'birth_date' => '1992-06-14',
                'cedula' => '2.345.678',
                'passport_number' => 'MN234567',
                'passport_expiry' => '2026-12-30',
                'phone' => '+595 987 890 123',
                'address' => 'San Lorenzo Centro 321',
                'city' => 'San Lorenzo',
                'status' => 'approved',
                'current_stage' => 'Job Fair',
                'progress_percentage' => 65,
            ],
            [
                'full_name' => 'Patricia Beatriz Duarte',
                'birth_date' => '1995-02-28',
                'cedula' => '3.456.789',
                'passport_number' => 'OP345678',
                'passport_expiry' => '2027-07-20',
                'phone' => '+595 988 901 234',
                'address' => 'Capiatá Km 18',
                'city' => 'Capiatá',
                'status' => 'in_review',
                'current_stage' => 'Validación MEC',
                'progress_percentage' => 35,
            ],
            
            // INTERN/TRAINEE
            [
                'full_name' => 'Diego Alejandro Rojas',
                'birth_date' => '1999-11-08',
                'cedula' => '4.567.891',
                'passport_number' => 'QR456789',
                'passport_expiry' => '2028-05-10',
                'phone' => '+595 989 012 345',
                'address' => 'Luque Centro 654',
                'city' => 'Luque',
                'status' => 'pending',
                'current_stage' => 'Inscripción',
                'progress_percentage' => 15,
            ],
            [
                'full_name' => 'Carla Vanessa Giménez',
                'birth_date' => '2000-04-25',
                'cedula' => '5.678.902',
                'passport_number' => 'ST567890',
                'passport_expiry' => '2029-02-15',
                'phone' => '+595 971 123 456',
                'address' => 'Ñemby Centro 987',
                'city' => 'Ñemby',
                'status' => 'approved',
                'current_stage' => 'Documentación',
                'progress_percentage' => 70,
            ],
            
            // HIGHER EDUCATION
            [
                'full_name' => 'Fernando José Vera',
                'birth_date' => '2001-08-19',
                'cedula' => '6.789.013',
                'passport_number' => 'UV678901',
                'passport_expiry' => '2027-10-25',
                'phone' => '+595 972 234 567',
                'address' => 'Mariano R. Alonso 147',
                'city' => 'Mariano Roque Alonso',
                'status' => 'in_review',
                'current_stage' => 'Evaluación',
                'progress_percentage' => 50,
            ],
            [
                'full_name' => 'Natalia Soledad Cabrera',
                'birth_date' => '2002-10-12',
                'cedula' => '7.890.124',
                'passport_number' => 'WX789012',
                'passport_expiry' => '2028-12-05',
                'phone' => '+595 973 345 678',
                'address' => 'Limpio Centro 258',
                'city' => 'Limpio',
                'status' => 'rejected',
                'current_stage' => 'Evaluación',
                'progress_percentage' => 25,
            ],
            
            // WORK & STUDY
            [
                'full_name' => 'Marcos Antonio Silva',
                'birth_date' => '2000-01-30',
                'cedula' => '8.901.235',
                'passport_number' => 'YZ890123',
                'passport_expiry' => '2026-09-18',
                'phone' => '+595 974 456 789',
                'address' => 'Villa Elisa Centro 369',
                'city' => 'Villa Elisa',
                'status' => 'approved',
                'current_stage' => 'Aprobación',
                'progress_percentage' => 80,
            ],
            
            // LANGUAGE PROGRAM
            [
                'full_name' => 'Lorena Elizabeth Ovelar',
                'birth_date' => '2003-03-07',
                'cedula' => '9.012.346',
                'passport_number' => 'AA901234',
                'passport_expiry' => '2029-06-22',
                'phone' => '+595 975 567 890',
                'address' => 'Itauguá Centro 741',
                'city' => 'Itauguá',
                'status' => 'pending',
                'current_stage' => 'Inscripción',
                'progress_percentage' => 20,
            ],
            [
                'full_name' => 'Sebastián Ariel Mendoza',
                'birth_date' => '2001-06-21',
                'cedula' => '1.123.457',
                'passport_number' => 'BB012345',
                'passport_expiry' => '2027-03-30',
                'phone' => '+595 976 678 901',
                'address' => 'San Antonio Centro 852',
                'city' => 'San Antonio',
                'status' => 'in_review',
                'current_stage' => 'Evaluación',
                'progress_percentage' => 55,
            ],
        ];

        $createdCount = 0;
        $programIndex = 0;

        foreach ($participants as $index => $participantData) {
            // Asignar programa de forma cíclica
            $program = $allPrograms[$programIndex % $allPrograms->count()];

            // Crear o encontrar usuario
            $email = strtolower(str_replace(' ', '.', $participantData['full_name'])) . '@participante.ie.com.py';
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $participantData['full_name'],
                    'password' => bcrypt('password123'),
                    'role' => 'user',
                ]
            );

            // Crear aplicación
            $application = Application::create([
                'user_id' => $user->id,
                'program_id' => $program->id,
                'full_name' => $participantData['full_name'],
                'birth_date' => $participantData['birth_date'],
                'cedula' => $participantData['cedula'],
                'passport_number' => $participantData['passport_number'],
                'passport_expiry' => $participantData['passport_expiry'],
                'phone' => $participantData['phone'],
                'address' => $participantData['address'],
                'city' => $participantData['city'],
                'country' => 'Paraguay',
                'status' => $participantData['status'],
                'current_stage' => $participantData['current_stage'],
                'progress_percentage' => $participantData['progress_percentage'],
                'total_cost' => $program->cost ?? 2000,
                'amount_paid' => $this->calculateAmountPaid($program->cost ?? 2000, $participantData['progress_percentage']),
                'applied_at' => Carbon::now()->subDays(rand(1, 90)),
                'started_at' => $participantData['status'] !== 'pending' ? Carbon::now()->subDays(rand(1, 60)) : null,
                'completed_at' => $participantData['progress_percentage'] >= 100 ? Carbon::now()->subDays(rand(1, 30)) : null,
            ]);

            $createdCount++;
            $programIndex++;
        }

        $this->command->info("✅ {$createdCount} participantes de prueba creados exitosamente");
        $this->command->info("📊 Programas utilizados: {$allPrograms->count()}");
        $this->command->info("🎯 Estados incluidos: pending, in_review, approved, rejected");
    }

    /**
     * Calcula el monto pagado basado en el progreso
     */
    private function calculateAmountPaid($totalCost, $progressPercentage)
    {
        // Simular pagos parciales basados en el progreso
        if ($progressPercentage >= 80) {
            return $totalCost * 0.9; // 90% pagado
        } elseif ($progressPercentage >= 50) {
            return $totalCost * 0.6; // 60% pagado
        } elseif ($progressPercentage >= 25) {
            return $totalCost * 0.3; // 30% pagado
        } else {
            return $totalCost * 0.1; // Solo inscripción (10%)
        }
    }
}
