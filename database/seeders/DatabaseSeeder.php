<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create an admin user with fixed email for easy access
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('Intercultural'),
        ]);

        // Create a regular user with fixed email for easy access
        User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('Intercultural'),
        ]);

        // Create 5 additional regular users
        User::factory(5)->create();
        
        // Create 2 additional admin users
        User::factory(2)->admin()->create();

        // Ejecutar los seeders adicionales
        $this->call([
            ProgramSeeder::class,
            ProgramRequisiteSeeder::class,
            ApplicationSeeder::class,
            UserProgramRequisiteSeeder::class,
            ApplicationDocumentSeeder::class,
            SupportTicketSeeder::class,
            NotificationSeeder::class,
            PointRewardSeeder::class,
            RedemptionSeeder::class,
            // NEW: Audit Phase 2-3 Seeders
            SponsorSeeder::class,
            HostCompanySeeder::class,
        ]);
    }
}
