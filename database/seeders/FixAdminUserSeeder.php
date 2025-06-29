<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FixAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Primero intentamos actualizar el usuario si existe
        $admin = User::where('email', 'admin@example.com')->first();
        
        if ($admin) {
            // Actualizar la contraseña del usuario existente
            $admin->password = Hash::make('admin123');
            $admin->role = 'admin';
            $admin->save();
            
            $this->command->info('Usuario administrador actualizado con éxito.');
        } else {
            // Si no existe, lo creamos
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
        }
        
        // Mostrar las credenciales
        $this->command->info('Email: admin@example.com');
        $this->command->info('Contraseña: admin123');
        
        // Verificar la contraseña en la base de datos (solo para diagnóstico)
        $user = User::where('email', 'admin@example.com')->first();
        if ($user) {
            $this->command->info('El hash de la contraseña es: ' . $user->password);
        }
    }
}
