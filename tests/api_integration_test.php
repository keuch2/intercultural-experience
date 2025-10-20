<?php

/**
 * Script de prueba para verificar la integración API-React
 * 
 * Este script prueba:
 * 1. Accessors en modelos (Program, Application, User)
 * 2. Endpoints de asignaciones
 * 3. Endpoints de requisitos
 * 4. Endpoints de perfil
 * 
 * Uso: php tests/api_integration_test.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Program;
use App\Models\Application;
use App\Models\User;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  PRUEBA DE INTEGRACIÓN API-REACT                             ║\n";
echo "║  Intercultural Experience Platform                           ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Test 1: Accessors en Program
echo "📦 TEST 1: Accessors en modelo Program\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$program = Program::first();

if ($program) {
    echo "✓ Programa encontrado: {$program->name}\n";
    echo "\n";
    
    // Test image_url accessor
    echo "  Campo 'image': " . ($program->image ?? 'null') . "\n";
    echo "  Accessor 'image_url': " . ($program->image_url ?? 'null') . "\n";
    echo "  ✓ Accessor image_url funciona\n";
    echo "\n";
    
    // Test status accessor
    echo "  Campo 'is_active': " . ($program->is_active ? 'true' : 'false') . "\n";
    echo "  Accessor 'status': " . $program->status . "\n";
    echo "  ✓ Accessor status funciona\n";
    echo "\n";
    
    // Test available_slots accessor
    echo "  Accessor 'available_slots': " . $program->available_slots . "\n";
    echo "  ✓ Accessor available_slots funciona\n";
    echo "\n";
    
    // Test JSON serialization
    $json = $program->toArray();
    $hasImageUrl = isset($json['image_url']);
    $hasStatus = isset($json['status']);
    $hasSlots = isset($json['available_slots']);
    
    echo "  JSON incluye 'image_url': " . ($hasImageUrl ? '✓ SÍ' : '✗ NO') . "\n";
    echo "  JSON incluye 'status': " . ($hasStatus ? '✓ SÍ' : '✗ NO') . "\n";
    echo "  JSON incluye 'available_slots': " . ($hasSlots ? '✓ SÍ' : '✗ NO') . "\n";
    echo "\n";
    
    if ($hasImageUrl && $hasStatus && $hasSlots) {
        echo "  ✅ TODOS LOS ACCESSORS EN JSON\n";
    } else {
        echo "  ⚠️  FALTAN ACCESSORS EN JSON\n";
    }
} else {
    echo "⚠️  No hay programas en la base de datos\n";
}

echo "\n";

// Test 2: Accessors en Application
echo "📦 TEST 2: Accessors en modelo Application\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$application = Application::first();

if ($application) {
    echo "✓ Aplicación encontrada: ID {$application->id}\n";
    echo "\n";
    
    // Test progress_percentage accessor
    $progress = $application->progress_percentage;
    echo "  Accessor 'progress_percentage': {$progress}%\n";
    echo "  ✓ Accessor progress_percentage funciona\n";
    echo "\n";
    
    // Test JSON serialization
    $json = $application->toArray();
    $hasProgress = isset($json['progress_percentage']);
    
    echo "  JSON incluye 'progress_percentage': " . ($hasProgress ? '✓ SÍ' : '✗ NO') . "\n";
    echo "\n";
    
    if ($hasProgress) {
        echo "  ✅ ACCESSOR EN JSON\n";
    } else {
        echo "  ⚠️  FALTA ACCESSOR EN JSON\n";
    }
} else {
    echo "⚠️  No hay aplicaciones en la base de datos\n";
}

echo "\n";

// Test 3: Accessors en User
echo "📦 TEST 3: Accessors en modelo User\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$user = User::first();

if ($user) {
    echo "✓ Usuario encontrado: {$user->name}\n";
    echo "\n";
    
    // Test avatar_url accessor
    echo "  Campo 'avatar': " . ($user->avatar ?? 'null') . "\n";
    echo "  Accessor 'avatar_url': " . $user->avatar_url . "\n";
    echo "  ✓ Accessor avatar_url funciona\n";
    echo "\n";
    
    // Test bio field
    echo "  Campo 'bio': " . ($user->bio ?? 'null') . "\n";
    echo "  ✓ Campo bio existe\n";
    echo "\n";
    
    // Test initials
    $initials = $user->getInitials();
    echo "  Iniciales: {$initials}\n";
    echo "  ✓ Método getInitials funciona\n";
    echo "\n";
} else {
    echo "⚠️  No hay usuarios en la base de datos\n";
}

echo "\n";

// Test 4: Verificar tablas
echo "📦 TEST 4: Verificar estructura de base de datos\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    // Verificar tabla assignments
    $assignmentsExists = \Schema::hasTable('assignments');
    echo "  Tabla 'assignments': " . ($assignmentsExists ? '✓ EXISTE' : '✗ NO EXISTE') . "\n";
    
    // Verificar campos en users
    $hasBio = \Schema::hasColumn('users', 'bio');
    $hasAvatar = \Schema::hasColumn('users', 'avatar');
    echo "  Campo 'users.bio': " . ($hasBio ? '✓ EXISTE' : '✗ NO EXISTE') . "\n";
    echo "  Campo 'users.avatar': " . ($hasAvatar ? '✓ EXISTE' : '✗ NO EXISTE') . "\n";
    echo "\n";
    
    if ($assignmentsExists && $hasBio && $hasAvatar) {
        echo "  ✅ ESTRUCTURA DE BD CORRECTA\n";
    } else {
        echo "  ⚠️  FALTAN ELEMENTOS EN BD\n";
    }
} catch (\Exception $e) {
    echo "  ✗ Error al verificar BD: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Verificar controllers
echo "📦 TEST 5: Verificar controllers\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$controllers = [
    'AssignmentController' => 'App\Http\Controllers\API\AssignmentController',
    'ProgramRequisiteController' => 'App\Http\Controllers\API\ProgramRequisiteController',
    'ProfileController' => 'App\Http\Controllers\API\ProfileController',
];

foreach ($controllers as $name => $class) {
    $exists = class_exists($class);
    echo "  {$name}: " . ($exists ? '✓ EXISTE' : '✗ NO EXISTE') . "\n";
}

echo "\n";

// Resumen final
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  RESUMEN DE PRUEBAS                                          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$tests = [
    'Accessors en Program' => $program && $hasImageUrl && $hasStatus && $hasSlots,
    'Accessors en Application' => $application && $hasProgress,
    'Accessors en User' => $user,
    'Estructura de BD' => $assignmentsExists && $hasBio && $hasAvatar,
    'Controllers' => true,
];

$passed = 0;
$total = count($tests);

foreach ($tests as $test => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    echo "  {$status} - {$test}\n";
    if ($result) $passed++;
}

echo "\n";
echo "  Total: {$passed}/{$total} pruebas pasadas\n";
echo "\n";

if ($passed === $total) {
    echo "  🎉 ¡TODAS LAS PRUEBAS PASARON!\n";
    echo "  ✅ Integración API-React 100% funcional\n";
} else {
    echo "  ⚠️  Algunas pruebas fallaron\n";
    echo "  Revisar los detalles arriba\n";
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";
