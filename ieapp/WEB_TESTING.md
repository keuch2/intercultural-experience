# Probar la app en el navegador

La app corre en el navegador con `react-native-web`, sin necesidad de Expo Go ni de un
dispositivo. Sirve para validar flujo de navegación, pantallas, formularios y llamadas a la API.

## Uso

```bash
cd ieapp

# Contra la API de producción (ie.org.py) — por defecto
npm run web

# Contra la API de Laravel en esta Mac
npm run web:local
```

Abre `http://localhost:8081`. Metro recompila al guardar, igual que en Expo Go.

Para ver el diseño como en un celular: DevTools (F12) → modo dispositivo (Ctrl/Cmd+Shift+M).

## Usuario de prueba local

Los administradores no pueden entrar por la app (el backend los rechaza a propósito).
Hay que usar un usuario con rol `user`. En local:

```
testaupair@test.com / test1234
```

Para fijarle contraseña a otro participante:

```bash
php artisan tinker --execute '$u=App\Models\User::find(ID); $u->password=bcrypt("test1234"); $u->requires_password_setup=false; $u->save();'
```

## Qué se puede probar y qué no

Funciona: navegación completa, login y registro, Home, catálogo de programas, postulaciones,
proceso Au Pair y del motor, documentos, pagos, avisos, perfil, y la descarga de archivos
(en web baja el archivo por el navegador en vez de abrir la hoja de compartir nativa).

No funciona igual que en el dispositivo:

- **Cámara y galería** (`expo-image-picker`): el navegador abre el selector de archivos.
- **Selector de fecha** (`@react-native-community/datetimepicker`): usa el control del navegador.
- **Área segura**: en web no hay notch, así que los márgenes de `SafeAreaView` quedan en cero.
  El solapamiento con la barra de estado de Android solo se valida en un dispositivo real o en el APK.
- **Notificaciones push y `expo-updates`**: no aplican en web.

Por eso el APK de EAS sigue siendo necesario antes de entregar al cliente. El navegador es para
iterar rápido durante el desarrollo.

## CORS

`config/cors.php` ya permite `http://localhost:8081` con el header `Authorization`. Si Metro
arranca en otro puerto (8082, 8083...), hay que agregarlo a `allowed_origins`.
