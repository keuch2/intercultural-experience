<?php
// Test de escritura en storage

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "=== Test de Escritura en Storage ===\n\n";

// Test 1: Verificar configuración
echo "1. Configuración de Storage:\n";
echo "   Default disk: " . config('filesystems.default') . "\n";
echo "   Public disk root: " . config('filesystems.disks.public.root') . "\n\n";

// Test 2: Verificar permisos
$storageDir = storage_path('app/public/programs');
echo "2. Permisos del directorio:\n";
echo "   Path: $storageDir\n";
echo "   Existe: " . (is_dir($storageDir) ? 'Sí' : 'No') . "\n";
echo "   Escribible: " . (is_writable($storageDir) ? 'Sí' : 'No') . "\n";
echo "   Permisos: " . substr(sprintf('%o', fileperms($storageDir)), -4) . "\n\n";

// Test 3: Intentar crear archivo de prueba
echo "3. Test de escritura:\n";
$testContent = "Test " . time();
$testFilename = 'test_' . time() . '.txt';

try {
    $path = Storage::disk('public')->put('programs/' . $testFilename, $testContent);
    echo "   Storage::put() retornó: " . ($path ? 'true' : 'false') . "\n";
    
    $fullPath = storage_path('app/public/programs/' . $testFilename);
    echo "   Archivo existe: " . (file_exists($fullPath) ? 'Sí' : 'No') . "\n";
    
    if (file_exists($fullPath)) {
        echo "   Contenido: " . file_get_contents($fullPath) . "\n";
        echo "   Tamaño: " . filesize($fullPath) . " bytes\n";
        unlink($fullPath);
        echo "   Archivo de prueba eliminado\n";
    }
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. Test con storeAs (simulando upload):\n";
try {
    $testFilename2 = 'test_storeas_' . time() . '.txt';
    $tempFile = tmpfile();
    fwrite($tempFile, "Test storeAs content");
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    // Simular UploadedFile
    $uploadedFile = new \Illuminate\Http\UploadedFile(
        $tempPath,
        'test.txt',
        'text/plain',
        null,
        true
    );
    
    $path = $uploadedFile->storeAs('public/programs', $testFilename2);
    echo "   storeAs() retornó: $path\n";
    
    $fullPath = storage_path('app/' . $path);
    echo "   Archivo existe: " . (file_exists($fullPath) ? 'Sí' : 'No') . "\n";
    
    if (file_exists($fullPath)) {
        echo "   Tamaño: " . filesize($fullPath) . " bytes\n";
        unlink($fullPath);
        echo "   Archivo de prueba eliminado\n";
    }
    
    fclose($tempFile);
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Fin del Test ===\n";
