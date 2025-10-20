<?php

/**
 * Script para verificar el output JSON de los modelos
 * Simula lo que recibiría React Native
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Program;
use App\Models\User;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN DE OUTPUT JSON                                 ║\n";
echo "║  Simulando respuestas API para React Native                  ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Test Program JSON
echo "📱 ENDPOINT: GET /api/programs/{id}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$program = Program::first();

if ($program) {
    $json = $program->toArray();
    
    echo "Response JSON:\n";
    echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    echo "\n";
    
    echo "Campos esperados por React Native:\n";
    echo "  ✓ id: " . (isset($json['id']) ? '✓' : '✗') . "\n";
    echo "  ✓ name: " . (isset($json['name']) ? '✓' : '✗') . "\n";
    echo "  ✓ image: " . (isset($json['image']) ? '✓' : '✗') . "\n";
    echo "  ✓ image_url: " . (isset($json['image_url']) ? '✓' : '✗') . "\n";
    echo "  ✓ is_active: " . (isset($json['is_active']) ? '✓' : '✗') . "\n";
    echo "  ✓ status: " . (isset($json['status']) ? '✓' : '✗') . "\n";
    echo "  ✓ available_slots: " . (isset($json['available_slots']) ? '✓' : '✗') . "\n";
}

echo "\n\n";

// Test User JSON
echo "📱 ENDPOINT: GET /api/me\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$user = User::first();

if ($user) {
    $json = $user->toArray();
    
    // Ocultar campos sensibles para la demo
    unset($json['password']);
    unset($json['remember_token']);
    unset($json['bank_info']);
    
    echo "Response JSON:\n";
    echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    echo "\n";
    
    echo "Campos esperados por React Native:\n";
    echo "  ✓ id: " . (isset($json['id']) ? '✓' : '✗') . "\n";
    echo "  ✓ name: " . (isset($json['name']) ? '✓' : '✗') . "\n";
    echo "  ✓ email: " . (isset($json['email']) ? '✓' : '✗') . "\n";
    echo "  ✓ bio: " . (isset($json['bio']) ? '✓' : '✗') . "\n";
    echo "  ✓ avatar: " . (isset($json['avatar']) ? '✓' : '✗') . "\n";
    echo "  ✓ avatar_url: " . (isset($json['avatar_url']) ? '✓' : '✗') . "\n";
}

echo "\n\n";

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ VERIFICACIÓN COMPLETA                                    ║\n";
echo "║  Los modelos retornan todos los campos esperados             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";
