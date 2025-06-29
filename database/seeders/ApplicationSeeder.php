<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        $programs = Program::all();
        $statuses = ['pending', 'in_review', 'approved', 'rejected'];
        
        // Asegurarse de que cada usuario tenga al menos una aplicación
        foreach ($users as $user) {
            // Seleccionar un programa aleatorio para el usuario
            $program = $programs->random();
            
            // Determinar un estado aleatorio para la aplicación
            $status = $statuses[array_rand($statuses)];
            
            // Crear fechas apropiadas según el estado
            $appliedAt = now()->subDays(rand(1, 60));
            $completedAt = null;
            
            if ($status !== 'pending') {
                $completedAt = (clone $appliedAt)->addDays(rand(3, 15));
            }
            
            // Crear la aplicación
            Application::create([
                'user_id' => $user->id,
                'program_id' => $program->id,
                'status' => $status,
                'applied_at' => $appliedAt,
                'completed_at' => $completedAt,
                'created_at' => $appliedAt,
                'updated_at' => $completedAt ?? $appliedAt,
            ]);
        }
        
        // Crear algunas aplicaciones adicionales para tener variedad
        for ($i = 0; $i < 15; $i++) {
            $user = $users->random();
            $program = $programs->random();
            $status = $statuses[array_rand($statuses)];
            
            $appliedAt = now()->subDays(rand(1, 90));
            $completedAt = null;
            
            if ($status !== 'pending') {
                $completedAt = (clone $appliedAt)->addDays(rand(3, 20));
            }
            
            Application::create([
                'user_id' => $user->id,
                'program_id' => $program->id,
                'status' => $status,
                'applied_at' => $appliedAt,
                'completed_at' => $completedAt,
                'created_at' => $appliedAt,
                'updated_at' => $completedAt ?? $appliedAt,
            ]);
        }
    }
}
