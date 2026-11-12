# Publicar en Google Play — IE - intercultural experience

Paquete: `com.intercultural.experience` · Solo smartphones · Textos y gráficos en `store/google-play/`.

Camino: **EAS Build** genera el `.aab` firmado y **EAS Submit** lo sube a Play con una cuenta de servicio de Google.
La única subida manual es la primera (Google no permite crear la primera versión por API).

## 1. Crear la app en Play Console

Play Console → **Crear app**: nombre `IE - intercultural experience`, idioma Español (Latinoamérica), App, Gratis,
aceptar las declaraciones.

## 2. Primera build y subida manual

```bash
cd ieapp
npx eas-cli build --platform android --profile production
```

Descargar el `.aab` del link que da EAS. En Play Console → **Pruebas → Prueba interna → Crear versión**:
- Aceptar **Firma de apps de Play** (Google guarda la clave de firma; la de EAS queda como clave de subida).
- Subir el `.aab`, agregar testers (lista de correos) y publicar la prueba interna.

## 3. Cuenta de servicio (para que las siguientes subidas sean automáticas)

1. Google Cloud Console (mismo proyecto vinculado a Play) → IAM → **Cuentas de servicio → Crear**. Sin roles.
2. En la cuenta creada → **Claves → Agregar clave → JSON**. Guardar como
   `ieapp/credentials/google-play-service-account.json` (la carpeta `credentials/` está en `.gitignore`).
3. Play Console → **Usuarios y permisos → Invitar usuario** con el email de la cuenta de servicio; permisos de la app
   IE: "Lanzar a pruebas", "Lanzar a producción" y "Administrar versiones".

Desde ahí: `npx eas-cli build --platform android --profile production --auto-submit` compila y sube a la pista
`internal` (configurada en `eas.json`). Se promueve a producción desde Play Console.

## 4. Solo smartphones

- La app declara `supports-screens` sin pantallas grandes (`plugins/withPhoneOnlyScreens.js`).
- Play Console → **Versión → Catálogo de dispositivos**: filtrar por tipo **Tablet** y **Chromebook** → Excluir.
- Play Console → **Configuración avanzada → Factores de forma**: no agregar Wear OS, Android TV, Android Auto ni XR.

## 5. Ficha y contenido de la app

Copiar de `store/google-play/ficha-play-store.md`: ficha principal, gráficos, Contenido de la app (privacidad,
acceso con la cuenta demo, anuncios, clasificación, público objetivo, eliminación de cuenta) y Seguridad de los datos.

## 6. Producción

- **Cuenta de desarrollador personal creada después del 13/11/2023:** Google exige una **prueba cerrada con al menos
  12 testers durante 14 días seguidos** antes de habilitar producción. Crear la pista "Prueba cerrada", invitar a los
  testers (que deben aceptar desde el link y mantener la app instalada) y luego pedir acceso a producción.
- **Cuenta de organización:** se puede pasar directo a producción.

La revisión de Google suele tardar de unas horas a 7 días (más en la primera publicación).
