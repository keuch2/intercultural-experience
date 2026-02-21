# Deploy a Producción - Intercultural Experience

## Servidor de Producción

| Dato | Valor |
|------|-------|
| **Host** | `200.58.105.22` |
| **Puerto SSH** | `5714` |
| **Usuario** | `root` |
| **Password** | `zyU-LwhtSeL3&3` |
| **Ruta aplicación** | `/home/ieorgpy/public_html/app` |
| **Repositorio** | `https://github.com/keuch2/intercultural-experience.git` |
| **Branch** | `main` |

---

## Conexión SSH

```bash
ssh -p5714 root@200.58.105.22
```

---

## Deploy Manual (paso a paso)

### 1. Conectarse al servidor

```bash
ssh -p5714 root@200.58.105.22
cd /home/ieorgpy/public_html/app
```

### 2. Poner en mantenimiento

```bash
php artisan down
```

### 3. Traer cambios del repositorio

```bash
git pull origin main
```

### 4. Instalar dependencias

```bash
composer install --no-dev --optimize-autoloader
```

### 5. Ejecutar migraciones

```bash
php artisan migrate --force
```

### 6. Limpiar y optimizar caché

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Permisos de archivos

```bash
chmod -R 775 storage bootstrap/cache
```

### 8. Volver a poner online

```bash
php artisan up
```

---

## Deploy Rápido (una línea desde local)

Ejecutar desde la máquina local sin necesidad de abrir SSH interactivo:

```bash
sshpass -p 'zyU-LwhtSeL3&3' ssh -p5714 -o StrictHostKeyChecking=no root@200.58.105.22 \
  "cd /home/ieorgpy/public_html/app && \
   php artisan down && \
   git pull origin main && \
   composer install --no-dev --optimize-autoloader && \
   php artisan migrate --force && \
   php artisan config:clear && \
   php artisan cache:clear && \
   php artisan view:clear && \
   php artisan route:clear && \
   php artisan config:cache && \
   php artisan route:cache && \
   php artisan view:cache && \
   chmod -R 775 storage bootstrap/cache && \
   php artisan up"
```

> **Nota:** Requiere `sshpass` instalado. En macOS: `brew install hudochenkov/sshpass/sshpass`

---

## Deploy con script remoto

Si el servidor tiene `deploy.sh` en la raíz del proyecto:

```bash
sshpass -p 'zyU-LwhtSeL3&3' ssh -p5714 -o StrictHostKeyChecking=no root@200.58.105.22 \
  "cd /home/ieorgpy/public_html/app && bash deploy.sh"
```

---

## Troubleshooting

### Migraciones renombradas aparecen como "Pending"

Si al renombrar migraciones localmente (cambio de timestamp) el servidor las muestra como pendientes pero las tablas ya existen, registrarlas manualmente:

```bash
php artisan tinker --execute="
\$batch = DB::table('migrations')->max('batch') + 1;
\$migrations = [
    'nombre_migracion_renombrada_1',
    'nombre_migracion_renombrada_2',
];
foreach (\$migrations as \$m) {
    DB::table('migrations')->insert(['migration' => \$m, 'batch' => \$batch]);
}
echo 'Registradas ' . count(\$migrations) . ' migraciones';
"
```

### Ver estado de migraciones

```bash
php artisan migrate:status
```

### Ver logs de errores

```bash
tail -100 storage/logs/laravel.log
```

### Verificar que la app responde

```bash
curl -I https://ie.org.py
```

---

## Checklist Post-Deploy

- [ ] Verificar que la app carga correctamente
- [ ] Probar login de administrador
- [ ] Verificar funcionalidades modificadas
- [ ] Revisar logs de errores (`storage/logs/laravel.log`)
- [ ] Confirmar conectividad de la app móvil
