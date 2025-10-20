<?php
// Crear archivo: create_admin.php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    $user = User::create([
        'name' => 'Administrador Principal',
        'email' => 'boris@dedoff.com',
        'password' => Hash::make('Marfuli.00'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    
    echo "Usuario administrador creado exitosamente!\n";
    echo "Email: boris@dedoff.com\n";
    echo "Contraseña: Marfuli.00\n";
    echo "ID del usuario: " . $user->id . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>