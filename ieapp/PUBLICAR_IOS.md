# Publicar en Apple App Store — ie · intercultural experience

Guía de los pasos que **ejecutás vos** en tu terminal (requieren login con Apple / 2FA, que no puede
hacerse desde una sesión automatizada). Todo el código de la app ya quedó preparado (Parte A).

Bundle ID: `com.intercultural.experience`
Cuenta EAS: `keuch2` (ya autenticado)

---

## Paso 1 — Build de producción iOS (.ipa)

No necesitás crear la app en App Store Connect todavía. EAS gestiona los certificados automáticamente.

```bash
cd ieapp
npx eas-cli build --platform ios --profile production
```

Durante este comando, EAS te va a pedir:

1. **Login con tu Apple ID** (email + contraseña de tu cuenta Apple Developer).
2. **Código 2FA** que llega a tus dispositivos Apple.
3. Cuando pregunte por credenciales:
   - "Generate a new Apple Distribution Certificate?" → **Yes**
   - "Generate a new Apple Provisioning Profile?" → **Yes**
   - Si pregunta por registrar el Bundle ID `com.intercultural.experience` en tu cuenta → **Yes**
     (EAS lo crea por vos en el Apple Developer Portal).

El build corre en la nube (~20-35 min). Al terminar te da una URL con el `.ipa`.

> EAS guarda el certificado y el perfil en su servidor, así que los próximos builds no vuelven a pedir
> login.

---

## Paso 2 — Crear la app en App Store Connect

Mientras corre el build (o después), creá el registro de la app:

1. Entrá a https://appstoreconnect.apple.com → **Apps** → **+** → **New App**.
2. Completá:
   - **Platform:** iOS
   - **Name:** `ie - intercultural experience`
   - **Primary Language:** Español (México) o Español (España)
   - **Bundle ID:** seleccioná `com.intercultural.experience` (aparece porque EAS lo registró en el Paso 1)
   - **SKU:** un identificador interno libre, ej. `ie-intercultural-experience`
   - **User Access:** Full Access
3. Al guardar, en la URL o en **App Information** vas a ver el **Apple ID de la app** (un número, ej.
   `6511234567`). **Ese número es tu `ascAppId`.**

También necesitás tu **Team ID** (10 caracteres): https://developer.apple.com/account → **Membership**.

---

## Paso 3 — Completar eas.json con tus datos

Editá `ieapp/eas.json`, bloque `submit.production.ios`, y reemplazá los `REEMPLAZAR_`:

```json
"ios": {
  "appleId": "tu-email@apple.com",
  "ascAppId": "6511234567",
  "appleTeamId": "ABCDE12345"
}
```

(Avisame cuando tengas estos 3 valores y lo edito yo si preferís.)

---

## Paso 4 — Subir a App Store Connect / TestFlight

```bash
cd ieapp
npx eas-cli submit --platform ios --profile production
```

Esto sube el `.ipa` del último build. Pide login Apple otra vez (o usa una API Key de App Store
Connect si la configurás). Al terminar, la build aparece en **TestFlight** en unos minutos, y luego
disponible para adjuntar a una versión de la App Store.

---

## Paso 5 — Completar la ficha y enviar a revisión (en App Store Connect)

Antes de "Submit for Review" necesitás:

- [ ] **Screenshots** obligatorios: iPhone 6.7" (1290×2796) y 6.5" (1242×2688). Podés generarlos
      corriendo la app en el simulador y capturando pantallas clave (login, dashboard, subir documento).
- [ ] **Descripción, keywords, categoría** → ya están en `store.config.json`; podés publicarlos con
      `npx eas-cli metadata:push` o cargarlos a mano.
- [ ] **URL de Política de Privacidad** → publicá `POLITICA_PRIVACIDAD.md` en `https://ie.org.py/privacidad`
      y pegá esa URL en el campo "Privacy Policy URL".
- [ ] **App Privacy** (cuestionario "Data Collection"): declará que la app recopila:
      - Datos de contacto (nombre, email) — vinculados a identidad, para funcionalidad de la app.
      - Fotos/documentos subidos por el usuario — para funcionalidad de la app.
      - No se usan para tracking ni publicidad.
- [ ] **Export Compliance:** ya está resuelto (`ITSAppUsesNonExemptEncryption: false` en app.json), no
      te preguntará.
- [ ] Seleccioná la build de TestFlight, completá "Version Information" y **Submit for Review**.

La revisión de Apple suele tardar **1 a 3 días**.

---

## Resumen de datos que tenés que conseguir

| Dato | Dónde |
|---|---|
| `appleId` | Tu email de Apple Developer |
| `ascAppId` | App Store Connect → tu app → App Information (número) |
| `appleTeamId` | developer.apple.com → Membership |
| URL privacidad | Publicar POLITICA_PRIVACIDAD.md en ie.org.py |
| Screenshots | Simulador iOS |
