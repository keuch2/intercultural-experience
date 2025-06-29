<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Verificar si el usuario ya existe
        $adminExists = User::where('email', 'admin@example.com')->exists();
        
        if (!$adminExists) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '123456789',
                'nationality' => 'España',
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Usuario administrador creado con éxito.');
        } else {
            $this->command->info('El usuario administrador ya existe.');
        }
        
        $this->command->info('Email: admin@example.com');
        $this->command->info('Contraseña: admin123');
    }
}
