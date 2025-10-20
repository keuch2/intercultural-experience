<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder para crear usuarios agentes de prueba
 */
class AgentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear agente principal
        $agent = User::firstOrCreate(
            ['email' => 'agent@interculturalexperience.com'],
            [
                'name' => 'María González - Agente IE',
                'password' => Hash::make('AgentIE2025!'),
                'role' => 'agent',
                'phone' => '+1234567890',
                'country' => 'United States',
                'city' => 'New York',
                'nationality' => 'American',
                'birth_date' => '1990-05-15',
                'email_verified_at' => now(),
            ]
        );

        // Crear agente secundario
        $agent2 = User::firstOrCreate(
            ['email' => 'agent2@interculturalexperience.com'],
            [
                'name' => 'Carlos Rodríguez - Agente IE',
                'password' => Hash::make('AgentIE2025!'),
                'role' => 'agent',
                'phone' => '+0987654321',
                'country' => 'Spain',
                'city' => 'Madrid',
                'nationality' => 'Spanish',
                'birth_date' => '1988-08-20',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Agentes creados exitosamente:');
        $this->command->info("   Email: {$agent->email} | Password: AgentIE2025!");
        $this->command->info("   Email: {$agent2->email} | Password: AgentIE2025!");
    }
}
