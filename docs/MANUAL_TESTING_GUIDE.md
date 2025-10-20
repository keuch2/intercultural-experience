# GUÍA DE TESTING MANUAL
## Intercultural Experience Platform

**Fecha:** 16 de Octubre, 2025  
**Versión:** 1.0  
**Objetivo:** Validar todas las funcionalidades implementadas

---

## 🎯 CHECKLIST GENERAL

### Pre-requisitos
- [ ] Base de datos actualizada (`php artisan migrate`)
- [ ] Seeders ejecutados (`php artisan db:seed`)
- [ ] Cache limpiado (`php artisan config:clear`)
- [ ] Credenciales de testing disponibles

---

## 🔐 ÉPICA 1: ROLES DE AGENTES

### 1.1 Login como Agente
**Credenciales:** `agent@interculturalexperience.com` / `AgentIE2025!`

**Pasos:**
1. Ir a `/login`
2. Ingresar credenciales de agente
3. Click en "Iniciar Sesión"

**Resultado Esperado:**
- ✅ Redirección a `/agent/dashboard`
- ✅ Dashboard muestra métricas del agente
- ✅ Sidebar con opciones de agente

### 1.2 Crear Participante
**Ruta:** `/agent/participants/create`

**Pasos:**
1. Click en "Participantes" → "Crear Nuevo"
2. Llenar formulario:
   - Nombre: Juan Pérez Test
   - Email: juan.test@test.com
   - Teléfono: +595 981 123456
   - País: Paraguay
   - Nacionalidad: Paraguaya
   - Fecha de nacimiento: 01/01/2000
3. (Opcional) Seleccionar programa
4. Click en "Crear Participante"

**Resultado Esperado:**
- ✅ Redirección a lista de participantes
- ✅ Mensaje de éxito con contraseña temporal
- ✅ Email enviado al participante (si SMTP configurado)
- ✅ Participante aparece en lista

### 1.3 Asignar Programa a Participante
**Ruta:** `/agent/participants/{id}/assign-program`

**Pasos:**
1. Ir a detalle de participante
2. Click en "Asignar Programa"
3. Seleccionar programa disponible
4. Agregar notas (opcional)
5. Click en "Asignar"

**Resultado Esperado:**
- ✅ Programa asignado correctamente
- ✅ Slots disponibles decrementados
- ✅ Email de asignación enviado
- ✅ Programa aparece en detalle del participante

### 1.4 Gestión desde Admin
**Ruta:** `/admin/agents`

**Pasos:**
1. Login como admin
2. Ir a "Agentes"
3. Verificar lista de agentes
4. Crear nuevo agente con contraseña manual
5. Editar agente existente
6. Resetear contraseña de agente

**Resultado Esperado:**
- ✅ Lista completa con filtros
- ✅ Creación con contraseña manual funciona
- ✅ Edición guarda cambios
- ✅ Reset de contraseña genera nueva contraseña

---

## 📧 ÉPICA 2: NOTIFICACIONES

### 2.1 Configurar SMTP
**Archivo:** `.env`

**Pasos:**
1. Abrir `.env`
2. Configurar:
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
QUEUE_CONNECTION=database
```
3. `php artisan config:clear`

### 2.2 Probar Comando de Email
**Comando:** `php artisan emails:test`

**Pasos:**
1. Ejecutar: `php artisan emails:test tu-email@test.com --type=welcome`
2. Verificar email recibido
3. Ejecutar: `php artisan emails:test tu-email@test.com --type=credentials`
4. Verificar email con credenciales

**Resultado Esperado:**
- ✅ Emails recibidos en inbox
- ✅ Diseño responsive correcto
- ✅ Branding de IE presente
- ✅ Links funcionales

### 2.3 Queue Worker
**Comando:** `php artisan queue:work`

**Pasos:**
1. Iniciar worker: `php artisan queue:work --once`
2. Crear un participante desde agente
3. Verificar logs de queue
4. Verificar email recibido

**Resultado Esperado:**
- ✅ Job procesado sin errores
- ✅ Email enviado correctamente
- ✅ Sin failed jobs

### 2.4 Eventos Automáticos
**Testing de todos los eventos:**

| Evento | Acción | Email Esperado |
|--------|--------|----------------|
| UserCreated | Crear agente/participante | Welcome o Credentials |
| ParticipantAssignedToProgram | Asignar programa | ProgramAssigned |
| PaymentVerified | Verificar pago | PaymentVerified |

**Pasos:**
1. Ejecutar cada acción
2. Verificar en `jobs` table
3. Procesar con `php artisan queue:work`
4. Verificar emails recibidos

---

## 📦 ÉPICA 3: CARGA MASIVA

### 3.1 Descargar Plantilla
**Ruta:** `/admin/bulk-import`

**Pasos:**
1. Login como admin
2. Ir a "Herramientas" → "Importación Masiva"
3. Click en "Descargar Plantilla Participantes"
4. Verificar descarga de Excel

**Resultado Esperado:**
- ✅ Archivo .xlsx descargado
- ✅ Columnas correctas
- ✅ Instrucciones claras

### 3.2 Preview de Importación
**Ruta:** `/admin/bulk-import`

**Pasos:**
1. Llenar plantilla con 3-5 registros de prueba
2. Subir archivo en formulario
3. Click en "Preview"
4. Verificar validaciones

**Resultado Esperado:**
- ✅ Tabla de preview mostrada
- ✅ Errores resaltados en rojo
- ✅ Éxitos en verde
- ✅ Estadísticas correctas

### 3.3 Importación Completa
**Ruta:** `/admin/bulk-import`

**Pasos:**
1. Después de preview exitoso
2. Click en "Importar Definitivamente"
3. Esperar proceso
4. Descargar reporte

**Resultado Esperado:**
- ✅ Registros creados en BD
- ✅ Contraseñas generadas
- ✅ Reporte Excel descargado
- ✅ Emails enviados (si configurado)

---

## 🔍 ÉPICA 4: AUDITORÍA

### 4.1 Ver Registro de Auditoría
**Ruta:** `/admin/activity-logs`

**Pasos:**
1. Login como admin
2. Ir a "Herramientas" → "Registro de Auditoría"
3. Verificar lista de logs
4. Aplicar filtros

**Resultado Esperado:**
- ✅ Lista de actividades mostrada
- ✅ Estadísticas en cards superiores
- ✅ Filtros funcionales
- ✅ Paginación correcta

### 4.2 Ver Detalle de Log
**Ruta:** `/admin/activity-logs/{id}`

**Pasos:**
1. Click en "Ver" de cualquier log
2. Verificar información detallada
3. Verificar cambios (si aplica)

**Resultado Esperado:**
- ✅ Usuario causante mostrado
- ✅ Fecha y hora correctas
- ✅ Cambios de campos visibles
- ✅ IP y User Agent presentes

### 4.3 Verificar Registro Automático
**Testing:**

**Pasos:**
1. Crear un nuevo programa
2. Ir a activity logs
3. Buscar el registro

**Resultado Esperado:**
- ✅ Log creado automáticamente
- ✅ Acción = "created"
- ✅ Usuario = admin actual
- ✅ Cambios registrados

---

## ⏰ ÉPICA 5: DEADLINES

### 5.1 Configurar Deadline en Requisito
**Ruta:** `/admin/programs/{id}/requisites`

**Pasos:**
1. Editar un requisito de programa
2. Establecer fecha límite (deadline)
3. Marcar "Enviar recordatorios"
4. Guardar

**Resultado Esperado:**
- ✅ Deadline guardado
- ✅ Campo visible en detalle

### 5.2 Comando de Deadlines
**Comando:** `php artisan deadlines:check`

**Pasos:**
1. Configurar deadline para hoy +7 días
2. Ejecutar comando manualmente
3. Verificar output
4. Revisar `jobs` table

**Resultado Esperado:**
- ✅ Comando ejecuta sin errores
- ✅ Recordatorios identificados
- ✅ Emails en queue (si aplica)

### 5.3 Cron Job
**Testing en servidor:**

**Pasos:**
1. Configurar cron: `* * * * * php artisan deadlines:check`
2. Esperar 1 minuto
3. Verificar logs
4. Verificar emails enviados

**Resultado Esperado:**
- ✅ Cron ejecuta cada minuto
- ✅ Sin errores en logs
- ✅ Recordatorios enviados

---

## 💰 ÉPICA 6: FACTURACIÓN

### 6.1 Crear Factura
**Ruta:** `/admin/invoices/create`

**Pasos:**
1. Login como admin
2. Ir a "Facturación" → "Facturas"
3. Click en "Nueva Factura"
4. Llenar formulario:
   - Participante
   - Datos de facturación
   - Concepto
   - Subtotal: 1000
   - Impuestos: 100
   - Descuento: 50
5. Estado: "Emitir"
6. Click en "Crear Factura"

**Resultado Esperado:**
- ✅ Factura creada
- ✅ Número automático: INV-YYYY-MM-0001
- ✅ PDF generado automáticamente
- ✅ Total calculado: 1050

### 6.2 Descargar PDF
**Ruta:** `/admin/invoices/{id}`

**Pasos:**
1. Ir a detalle de factura
2. Click en "Descargar PDF"
3. Verificar PDF

**Resultado Esperado:**
- ✅ PDF descargado
- ✅ Diseño profesional
- ✅ Datos completos
- ✅ Branding IE presente

### 6.3 Marcar como Pagado
**Ruta:** `/admin/invoices/{id}`

**Pasos:**
1. En factura emitida
2. Click en "Marcar como Pagado"
3. Verificar cambio de estado

**Resultado Esperado:**
- ✅ Estado cambia a "Pagado"
- ✅ Fecha de pago registrada
- ✅ Badge verde mostrado

### 6.4 Sistema de Cuotas
**Testing de modelos:**

**Pasos (en Tinker):**
```php
php artisan tinker
>>> $installment = PaymentInstallment::first()
>>> $installment->paid_installments_count
>>> $installment->progress_percentage
>>> $installment->checkCompletion()
```

**Resultado Esperado:**
- ✅ Contadores correctos
- ✅ Porcentaje calculado
- ✅ Métodos sin errores

---

## 🧪 TESTING DE INTEGRACIÓN

### Flujo Completo: Agente → Participante → Programa → Pago → Factura

**Escenario:** Crear participante y completar flujo completo

**Pasos:**
1. **Login como Agente**
   - Email: agent@interculturalexperience.com
   - Password: AgentIE2025!

2. **Crear Participante**
   - Nombre: María González Test
   - Email: maria.test@test.com
   - Datos completos

3. **Asignar Programa**
   - Seleccionar programa Work & Travel
   - Guardar

4. **Login como Admin**

5. **Crear Aplicación** (si no existe)
   - Para María González
   - Programa asignado

6. **Agregar Requisito de Pago**
   - Monto: 500 USD
   - Deadline: +30 días

7. **Verificar Pago**
   - Marcar como verificado
   - Verificar transacción financiera creada

8. **Crear Factura**
   - Para María González
   - Concepto: Pago programa Work & Travel
   - Generar PDF

9. **Verificar Auditoría**
   - Revisar logs de todas las acciones
   - Verificar registros completos

10. **Verificar Emails**
    - Revisar que se enviaron todos los emails correctos

**Resultado Esperado:**
- ✅ Flujo completo sin errores
- ✅ Todos los emails enviados
- ✅ Auditoría registrada
- ✅ PDFs generados
- ✅ Datos consistentes en BD

---

## 🔒 SECURITY TESTING

### 6.1 Control de Acceso

**Testing de Roles:**

| Ruta | Admin | Agente | Usuario | Público |
|------|-------|--------|---------|---------|
| `/admin/*` | ✅ | ❌ | ❌ | ❌ |
| `/agent/*` | ❌ | ✅ | ❌ | ❌ |
| `/api/*` | ✅ | ✅ | ✅ | Depende |
| `/login` | ✅ | ✅ | ✅ | ✅ |

**Pasos:**
1. Logout completo
2. Intentar acceder a `/admin/dashboard`
3. Verificar redirección a login
4. Login como agente
5. Intentar acceder a `/admin/users`
6. Verificar error 403

### 6.2 CSRF Protection

**Pasos:**
1. Abrir DevTools
2. Inspeccionar formulario
3. Verificar campo `_token`
4. Intentar submit sin token
5. Verificar error 419

### 6.3 XSS Prevention

**Pasos:**
1. Crear participante con nombre: `<script>alert('XSS')</script>`
2. Ver detalle del participante
3. Verificar que script no se ejecuta
4. Verificar HTML escapado

### 6.4 SQL Injection

**Pasos:**
1. Buscar programa con: `'; DROP TABLE programs;--`
2. Verificar que búsqueda falla sin dañar BD
3. Verificar logs de error

---

## 📊 PERFORMANCE TESTING

### 7.1 Importación Masiva

**Testing de Volumen:**

**Pasos:**
1. Crear Excel con 100 registros
2. Importar y medir tiempo
3. Repetir con 500 registros
4. Verificar memoria usada

**Resultado Esperado:**
- ✅ 100 registros: < 30 segundos
- ✅ 500 registros: < 2 minutos
- ✅ Sin timeout
- ✅ Sin errores de memoria

### 7.2 Paginación

**Pasos:**
1. Crear 100+ registros de actividad
2. Ir a `/admin/activity-logs`
3. Verificar paginación
4. Navegar entre páginas

**Resultado Esperado:**
- ✅ Carga rápida (< 1 segundo)
- ✅ Paginación funcional
- ✅ Filtros no rompen paginación

### 7.3 Generación de PDFs

**Pasos:**
1. Crear 10 facturas
2. Descargar PDFs una por una
3. Medir tiempo promedio

**Resultado Esperado:**
- ✅ Generación < 3 segundos
- ✅ Sin errores de memoria
- ✅ PDFs bien formados

---

## 📋 CHECKLIST FINAL

### Pre-Deployment
- [ ] Todos los tests manuales pasados
- [ ] Sin errores en logs
- [ ] Cache limpiado
- [ ] Migraciones ejecutadas
- [ ] Seeders verificados
- [ ] SMTP configurado
- [ ] Queue worker funcionando
- [ ] Cron job configurado
- [ ] PDFs generándose correctamente
- [ ] Auditoría registrando acciones

### Funcionalidades Críticas
- [ ] Login/Logout funciona
- [ ] Roles correctos (Admin/Agente)
- [ ] CRUD de participantes
- [ ] Asignación de programas
- [ ] Sistema de pagos
- [ ] Generación de facturas
- [ ] Emails enviándose
- [ ] Auditoría completa

### Performance
- [ ] Páginas cargan < 2 segundos
- [ ] Importación masiva < 2 minutos (500 registros)
- [ ] PDFs < 3 segundos
- [ ] Sin memory leaks
- [ ] Query optimization OK

### Security
- [ ] CSRF protection activo
- [ ] XSS escapado correctamente
- [ ] SQL injection prevenido
- [ ] Control de acceso funcional
- [ ] Passwords hasheados
- [ ] Validaciones en backend

---

## 🚨 REPORTE DE BUGS

### Template de Reporte

```markdown
**Título:** [Descripción breve]

**Severidad:** [Crítico / Alto / Medio / Bajo]

**Pasos para Reproducir:**
1. Paso 1
2. Paso 2
3. Paso 3

**Resultado Actual:** [Qué sucede]

**Resultado Esperado:** [Qué debería suceder]

**Evidencia:** [Screenshot o logs]

**Navegador/OS:** [Chrome 120 / macOS 14]

**Usuario de Prueba:** [admin@test.com]
```

---

## ✅ CONCLUSIÓN

Esta guía cubre el testing manual completo de las 6 épicas implementadas. 

**Tiempo Estimado Total:** 3-4 horas

**Recomendación:** Ejecutar en orden y documentar todos los hallazgos.

---

**Preparado por:** QA Engineer + Backend Developer  
**Fecha:** 16 de Octubre, 2025  
**Versión:** 1.0
