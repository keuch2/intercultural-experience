<?php
// Página de prueba para diagnosticar problemas de imágenes

$storageDir = __DIR__ . '/../storage/app/public/programs';
$publicStorageDir = __DIR__ . '/storage/programs';

echo "<h1>Diagnóstico de Imágenes</h1>";

echo "<h2>1. Verificación de Directorios</h2>";
echo "<p><strong>Storage real:</strong> " . realpath($storageDir) . "</p>";
echo "<p><strong>Existe:</strong> " . (is_dir($storageDir) ? 'Sí' : 'No') . "</p>";
echo "<p><strong>Permisos:</strong> " . substr(sprintf('%o', fileperms($storageDir)), -4) . "</p>";

echo "<h2>2. Verificación de Symlink</h2>";
echo "<p><strong>Public storage:</strong> " . $publicStorageDir . "</p>";
echo "<p><strong>Es symlink:</strong> " . (is_link($publicStorageDir) ? 'Sí' : 'No') . "</p>";
if (is_link($publicStorageDir)) {
    echo "<p><strong>Apunta a:</strong> " . readlink($publicStorageDir) . "</p>";
}

echo "<h2>3. Imágenes Disponibles</h2>";
$images = glob($storageDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
echo "<p>Total de imágenes: " . count($images) . "</p>";

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Archivo</th><th>Tamaño</th><th>Permisos</th><th>URL</th><th>Test</th></tr>";

foreach (array_slice($images, 0, 5) as $image) {
    $filename = basename($image);
    $size = filesize($image);
    $perms = substr(sprintf('%o', fileperms($image)), -4);
    $url = '/intercultural-experience/public/storage/programs/' . $filename;
    
    echo "<tr>";
    echo "<td>$filename</td>";
    echo "<td>" . number_format($size) . " bytes</td>";
    echo "<td>$perms</td>";
    echo "<td><a href='$url' target='_blank'>$url</a></td>";
    echo "<td><img src='$url' style='max-width:100px; max-height:100px;' onerror=\"this.parentElement.innerHTML='❌ Error'\"></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>4. Test de Acceso Directo</h2>";
if (!empty($images)) {
    $testImage = basename($images[0]);
    $testUrl = '/intercultural-experience/public/storage/programs/' . $testImage;
    echo "<p>Probando: <a href='$testUrl' target='_blank'>$testUrl</a></p>";
    echo "<img src='$testUrl' style='max-width: 400px; border: 2px solid #ccc;' onerror=\"alert('Error 403 o similar al cargar imagen')\">";
}

echo "<h2>5. Headers de Prueba</h2>";
echo "<pre>";
echo "SERVER_SOFTWARE: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "</pre>";
?>
