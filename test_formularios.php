<?php

/**
 * Script de testing rápido para verificar formularios dinámicos
 * Ejecutar: php test_formularios.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Program;
use App\Models\Application;

echo "\n=================================\n";
echo "  TEST: PROGRAMAS Y FORMULARIOS\n";
echo "=================================\n\n";

// Test 1: Verificar programas
echo "✅ TEST 1: Verificar nombres de programas\n";
echo "-------------------------------------------\n";
$programs = Program::all();
foreach ($programs as $program) {
    $hasUSA = str_contains($program->name, 'USA');
    $icon = $hasUSA ? '❌' : '✅';
    echo "$icon {$program->name}\n";
}

// Verificar que no exista "Super Programa"
$superProgram = Program::where('name', 'LIKE', '%Super Programa%')->first();
if ($superProgram) {
    echo "❌ PROBLEMA: 'Super Programa' aún existe\n";
} else {
    echo "✅ 'Super Programa' eliminado correctamente\n";
}

echo "\n";

// Test 2: Verificar relaciones
echo "✅ TEST 2: Verificar relaciones de modelos\n";
echo "-------------------------------------------\n";

$participant = Application::first();
if ($participant) {
    echo "Participante de prueba: {$participant->full_name}\n";
    
    // Test relaciones
    try {
        $workTravel = $participant->workTravelData;
        echo "✅ Relación workTravelData: OK\n";
    } catch (Exception $e) {
        echo "❌ Error en workTravelData: {$e->getMessage()}\n";
    }
    
    try {
        $auPair = $participant->auPairData;
        echo "✅ Relación auPairData: OK\n";
    } catch (Exception $e) {
        echo "❌ Error en auPairData: {$e->getMessage()}\n";
    }
    
    try {
        $teacher = $participant->teacherData;
        echo "✅ Relación teacherData: OK\n";
    } catch (Exception $e) {
        echo "❌ Error en teacherData: {$e->getMessage()}\n";
    }
} else {
    echo "⚠️  No hay participantes en la base de datos\n";
}

echo "\n";

// Test 3: Verificar vistas
echo "✅ TEST 3: Verificar vistas de formularios\n";
echo "-------------------------------------------\n";

$views = [
    'admin.participants.forms.work_travel',
    'admin.participants.forms.au_pair',
    'admin.participants.forms.teacher',
];

foreach ($views as $view) {
    if (view()->exists($view)) {
        echo "✅ Vista existe: $view\n";
    } else {
        echo "❌ Vista NO existe: $view\n";
    }
}

echo "\n";

// Test 4: Resumen
echo "✅ TEST 4: Resumen Final\n";
echo "-------------------------------------------\n";
echo "Total de programas: " . Program::count() . "\n";
echo "Programas activos: " . Program::where('is_active', true)->count() . "\n";
echo "Programas IE: " . Program::where('main_category', 'IE')->count() . "\n";
echo "Programas YFU: " . Program::where('main_category', 'YFU')->count() . "\n";

echo "\n=================================\n";
echo "  FIN DE LOS TESTS\n";
echo "=================================\n\n";
