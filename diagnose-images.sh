#!/bin/bash

echo "=== Diagnóstico de Imágenes - Intercultural Experience ==="
echo ""

echo "1. Verificando symlink..."
ls -la public/storage
echo ""

echo "2. Verificando directorio destino..."
ls -lad storage/app/public/programs/
echo ""

echo "3. Verificando permisos de archivos..."
ls -la storage/app/public/programs/ | head -10
echo ""

echo "4. Verificando propietario del proceso Apache..."
ps aux | grep -E "httpd|apache" | grep -v grep | head -3
echo ""

echo "5. Probando acceso HTTP a imagen de prueba..."
TEST_IMAGE=$(ls storage/app/public/programs/*.jpg 2>/dev/null | head -1 | xargs basename)
if [ -n "$TEST_IMAGE" ]; then
    echo "Probando: $TEST_IMAGE"
    curl -I "http://localhost/intercultural-experience/public/storage/programs/$TEST_IMAGE" 2>&1 | grep -E "HTTP|Content-Type"
else
    echo "No se encontraron imágenes para probar"
fi
echo ""

echo "6. Verificando .htaccess en public/..."
if [ -f public/.htaccess ]; then
    echo "Archivo .htaccess existe"
    grep -E "RewriteEngine|RewriteRule" public/.htaccess | head -5
else
    echo "Archivo .htaccess NO existe"
fi
echo ""

echo "7. Verificando atributos extendidos (macOS)..."
xattr storage/app/public/programs/*.jpg 2>/dev/null | head -5
echo ""

echo "8. Verificando logs de Laravel..."
tail -20 storage/logs/laravel.log | grep -E "Image|storage|403" | tail -5
echo ""

echo "=== Fin del diagnóstico ==="
