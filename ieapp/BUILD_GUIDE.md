# 📱 GUÍA DE BUILD - IEAPP
## Intercultural Experience Mobile App

**Fecha:** 20 de Octubre, 2025  
**Versión:** 1.0.0  
**Framework:** React Native + Expo

---

## 🎯 OPCIONES DE BUILD

Tienes **3 opciones** para testear la aplicación:

### **Opción 1: Expo Go (Más Rápido)** ⚡
- ✅ No requiere build
- ✅ Testing inmediato
- ✅ Hot reload
- ⚠️ Limitaciones en funcionalidades nativas

### **Opción 2: Development Build** 🔧
- ✅ Todas las funcionalidades
- ✅ Testing en dispositivo real
- ⚠️ Requiere configuración inicial

### **Opción 3: Production Build** 🚀
- ✅ APK/IPA para distribución
- ✅ Testing completo
- ⚠️ Proceso más largo

---

## ⚡ OPCIÓN 1: EXPO GO (RECOMENDADO PARA TESTING)

### **Paso 1: Instalar Expo Go**

**En tu dispositivo móvil:**
- **Android:** [Google Play Store](https://play.google.com/store/apps/details?id=host.exp.exponent)
- **iOS:** [App Store](https://apps.apple.com/app/expo-go/id982107779)

### **Paso 2: Configurar API URL**

Necesitas usar la **IP local de tu Mac** en lugar de `localhost`:

```bash
# Obtener tu IP local
ifconfig | grep "inet " | grep -v 127.0.0.1
```

Ejemplo de salida:
```
inet 192.168.1.100 netmask 0xffffff00 broadcast 192.168.1.255
```

Tu IP es: **192.168.1.100**

### **Paso 3: Actualizar apiClient.ts**

Edita: `ieapp/src/services/api/apiClient.ts`

```typescript
const getBaseUrl = () => {
  if (Platform.OS === 'web') {
    return 'http://localhost/intercultural-experience/public/api';
  } else if (Platform.OS === 'ios') {
    return 'http://localhost/intercultural-experience/public/api';
  } else if (Platform.OS === 'android') {
    // Para dispositivo físico Android, usa tu IP local
    return 'http://192.168.1.100/intercultural-experience/public/api';
  }
  return 'http://192.168.1.100/intercultural-experience/public/api';
};
```

### **Paso 4: Iniciar el servidor**

```bash
cd /opt/homebrew/var/www/intercultural-experience/ieapp

# Iniciar Expo
npm start

# O con opciones específicas
npx expo start --tunnel  # Para acceso desde cualquier red
```

### **Paso 5: Escanear QR**

1. Abre **Expo Go** en tu dispositivo
2. Escanea el **código QR** que aparece en la terminal
3. La app se cargará automáticamente

**¡Listo para testear!** ✅

---

## 🔧 OPCIÓN 2: DEVELOPMENT BUILD

### **Requisitos:**
- **Android:** Android Studio + SDK
- **iOS:** Xcode (solo en Mac)
- **Expo Account:** Crear en [expo.dev](https://expo.dev)

### **Paso 1: Instalar EAS CLI**

```bash
npm install -g eas-cli

# Login a Expo
eas login
```

### **Paso 2: Configurar EAS**

```bash
cd /opt/homebrew/var/www/intercultural-experience/ieapp

# Inicializar EAS
eas build:configure
```

Esto creará `eas.json`:

```json
{
  "build": {
    "development": {
      "developmentClient": true,
      "distribution": "internal"
    },
    "preview": {
      "distribution": "internal"
    },
    "production": {}
  }
}
```

### **Paso 3: Build para Android**

```bash
# Development build
eas build --platform android --profile development

# Espera 10-15 minutos
# Recibirás un link para descargar el APK
```

### **Paso 4: Instalar en dispositivo**

1. Descarga el APK desde el link
2. Transfiere a tu dispositivo Android
3. Instala (permite "Fuentes desconocidas")
4. Abre la app

### **Paso 5: Conectar al servidor**

```bash
# En tu Mac, inicia el servidor de desarrollo
cd ieapp
npx expo start --dev-client
```

---

## 🚀 OPCIÓN 3: PRODUCTION BUILD (APK/IPA)

### **Para Android (APK):**

```bash
cd /opt/homebrew/var/www/intercultural-experience/ieapp

# Build de producción
eas build --platform android --profile production

# O build local (más rápido)
eas build --platform android --local
```

### **Para iOS (IPA):**

```bash
# Requiere cuenta de Apple Developer ($99/año)
eas build --platform ios --profile production
```

### **Configuración adicional para producción:**

Edita `app.json`:

```json
{
  "expo": {
    "name": "Intercultural Experience",
    "slug": "intercultural-experience",
    "version": "1.0.0",
    "android": {
      "package": "com.intercultural.experience",
      "versionCode": 1,
      "permissions": [
        "INTERNET",
        "READ_EXTERNAL_STORAGE",
        "WRITE_EXTERNAL_STORAGE"
      ]
    },
    "ios": {
      "bundleIdentifier": "com.intercultural.experience",
      "buildNumber": "1.0.0"
    }
  }
}
```

---

## 🔧 CONFIGURACIÓN PREVIA AL BUILD

### **1. Verificar dependencias:**

```bash
cd /opt/homebrew/var/www/intercultural-experience/ieapp

# Instalar dependencias
npm install

# Verificar que no haya errores
npm run start
```

### **2. Configurar variables de entorno:**

Crea `.env` en la raíz de `ieapp`:

```env
# API Configuration
API_URL=http://192.168.1.100/intercultural-experience/public/api

# App Configuration
APP_NAME=Intercultural Experience
APP_VERSION=1.0.0

# Environment
NODE_ENV=development
```

### **3. Actualizar apiClient.ts para usar .env:**

```typescript
import Constants from 'expo-constants';

const getBaseUrl = () => {
  // Usar variable de entorno si existe
  const envApiUrl = Constants.expoConfig?.extra?.apiUrl;
  if (envApiUrl) return envApiUrl;
  
  // Fallback a configuración por plataforma
  if (Platform.OS === 'web') {
    return 'http://localhost/intercultural-experience/public/api';
  } else if (Platform.OS === 'android') {
    return 'http://192.168.1.100/intercultural-experience/public/api';
  }
  return 'http://192.168.1.100/intercultural-experience/public/api';
};
```

### **4. Actualizar app.json:**

```json
{
  "expo": {
    "extra": {
      "apiUrl": "http://192.168.1.100/intercultural-experience/public/api"
    }
  }
}
```

---

## 🧪 TESTING CHECKLIST

Antes de hacer el build, verifica:

- [ ] Backend Laravel corriendo (`php artisan serve`)
- [ ] Base de datos actualizada (`php artisan migrate`)
- [ ] API accesible desde red local
- [ ] CORS configurado correctamente
- [ ] Token de autenticación funcionando
- [ ] Endpoints probados con Postman

### **Probar API desde red local:**

```bash
# Desde tu Mac
curl http://192.168.1.100/intercultural-experience/public/api/health

# Desde tu dispositivo móvil (navegador)
http://192.168.1.100/intercultural-experience/public/api/health
```

---

## 🔥 TROUBLESHOOTING

### **Error: "Network request failed"**

**Solución:**
1. Verifica que tu Mac y dispositivo estén en la **misma red WiFi**
2. Verifica la IP local: `ifconfig | grep "inet "`
3. Verifica que el firewall permita conexiones entrantes
4. Prueba acceder a la API desde el navegador del móvil

### **Error: "Unable to resolve host"**

**Solución:**
1. Usa IP en lugar de `localhost`
2. Verifica que XAMPP esté corriendo
3. Verifica que Apache esté escuchando en todas las interfaces

### **Error: "CORS policy"**

**Solución:**
Edita `config/cors.php` en Laravel:

```php
'allowed_origins' => ['*'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'allowed_methods' => ['*'],
```

### **Error: "Expo Go not compatible"**

**Solución:**
Algunas dependencias requieren development build:
```bash
eas build --platform android --profile development
```

---

## 📱 COMANDOS RÁPIDOS

```bash
# Iniciar con Expo Go
npm start

# Iniciar con tunnel (acceso desde cualquier red)
npx expo start --tunnel

# Limpiar cache
npx expo start -c

# Build Android (development)
eas build --platform android --profile development

# Build Android (production)
eas build --platform android --profile production

# Build local
eas build --platform android --local

# Ver builds
eas build:list
```

---

## 🎯 RECOMENDACIÓN PARA TESTING

**Para testing rápido:**
1. ✅ Usa **Expo Go** (Opción 1)
2. ✅ Configura IP local en `apiClient.ts`
3. ✅ Escanea QR y testea inmediatamente

**Para testing completo:**
1. ✅ Crea **Development Build** (Opción 2)
2. ✅ Instala en dispositivo físico
3. ✅ Testea todas las funcionalidades

**Para distribución:**
1. ✅ Crea **Production Build** (Opción 3)
2. ✅ Genera APK/IPA
3. ✅ Distribuye a testers

---

## 📊 COMPARACIÓN DE OPCIONES

| Característica | Expo Go | Dev Build | Production |
|----------------|---------|-----------|------------|
| **Tiempo setup** | 5 min | 30 min | 1 hora |
| **Hot reload** | ✅ | ✅ | ❌ |
| **Funcionalidades nativas** | ⚠️ Limitado | ✅ Todas | ✅ Todas |
| **Requiere cuenta Expo** | ❌ | ✅ | ✅ |
| **Distribución** | ❌ | ⚠️ Internal | ✅ Stores |
| **Tamaño app** | ~50MB | ~80MB | ~30MB |

---

## 🚀 PRÓXIMOS PASOS

1. **Elige una opción** de build
2. **Configura la IP local** en apiClient.ts
3. **Inicia el servidor** Laravel
4. **Ejecuta el build** o Expo Go
5. **Testea la integración** completa

---

**¿Necesitas ayuda con algún paso específico?**

- Configurar IP local
- Crear development build
- Resolver errores de red
- Configurar CORS
- Generar APK de producción

---

**Preparado por:** Backend Developer  
**Fecha:** 20 de Octubre, 2025  
**Versión:** 1.0  
**Estado:** ✅ LISTO PARA BUILD
