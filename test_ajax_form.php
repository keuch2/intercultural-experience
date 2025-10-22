<?php

/**
 * Test rápido del endpoint AJAX de formularios
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simular request
$request = Illuminate\Http\Request::create(
    '/admin/participants/18/program-form/work_travel',
    'GET'
);

try {
    $response = $kernel->handle($request);
    
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content Type: " . $response->headers->get('content-type') . "\n";
    echo "Response Length: " . strlen($response->getContent()) . " bytes\n\n";
    
    if ($response->getStatusCode() === 200) {
        echo "✅ Endpoint funciona correctamente\n";
        echo "\nPrimeros 200 caracteres de la respuesta:\n";
        echo substr($response->getContent(), 0, 200) . "...\n";
    } else {
        echo "❌ Error en el endpoint\n";
        echo "Respuesta:\n" . $response->getContent() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Excepción: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

$kernel->terminate($request, $response ?? null);
