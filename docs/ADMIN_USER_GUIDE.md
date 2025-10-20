# Guía de Usuario - Panel Administrativo

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Acceso al Sistema](#acceso-al-sistema)
3. [Dashboard Principal](#dashboard-principal)
4. [Gestión de Usuarios](#gestión-de-usuarios)
5. [Gestión de Programas](#gestión-de-programas)
6. [Gestión de Aplicaciones](#gestión-de-aplicaciones)
7. [Sistema de Documentos](#sistema-de-documentos)
8. [Sistema de Puntos y Recompensas](#sistema-de-puntos-y-recompensas)
9. [Formularios Dinámicos](#formularios-dinámicos)
10. [Reportes y Estadísticas](#reportes-y-estadísticas)
11. [Configuración del Sistema](#configuración-del-sistema)

## Introducción

El Panel Administrativo de la Plataforma Intercultural Experience es una herramienta completa para gestionar todos los aspectos de los programas de intercambio cultural. Este sistema permite administrar usuarios, programas, aplicaciones, documentos, y generar reportes detallados.

### Características Principales

- **Gestión Integral**: Control completo sobre usuarios, programas y aplicaciones
- **Interfaz Intuitiva**: Diseño responsive y fácil de usar
- **Reportes Avanzados**: Estadísticas detalladas y exportación de datos
- **Seguridad**: Sistema robusto de roles y permisos
- **Automatización**: Flujos de trabajo automatizados para eficiencia

## Acceso al Sistema

### Inicio de Sesión

1. Navegue a `https://ie.org.py/admin/login`
2. Ingrese sus credenciales de administrador
3. Haga clic en "Iniciar Sesión"

> **Nota**: Solo usuarios con rol de "admin" pueden acceder al panel administrativo.

### Recuperación de Contraseña

Si olvida su contraseña:
1. Haga clic en "¿Olvidó su contraseña?"
2. Ingrese su email administrativo
3. Revise su correo electrónico para el enlace de recuperación
4. Siga las instrucciones para restablecer su contraseña

## Dashboard Principal

### Vista General

El dashboard principal proporciona una vista integral del estado de la plataforma:

#### Widgets de Estadísticas
- **Usuarios Activos**: Número total de usuarios registrados
- **Aplicaciones Pendientes**: Aplicaciones que requieren revisión
- **Programas Activos**: Programas disponibles para aplicación
- **Documentos Sin Verificar**: Documentos pendientes de revisión

#### Gráficos y Métricas
- **Aplicaciones por Mes**: Tendencia de aplicaciones en el tiempo
- **Distribución por Programas**: Popularidad de diferentes programas
- **Estado de Aplicaciones**: Distribución entre pendientes, aprobadas y rechazadas

#### Actividad Reciente
- Lista de las últimas actividades en el sistema
- Aplicaciones recientes
- Documentos subidos
- Cambios administrativos

## Gestión de Usuarios

### Ver Lista de Usuarios

**Navegación**: `Admin → Usuarios`

#### Funciones Disponibles
- **Buscar**: Filtrar usuarios por nombre, email o nacionalidad
- **Filtros**: Por rol, estado, fecha de registro
- **Ordenamiento**: Por nombre, fecha, último acceso
- **Exportar**: Descargar lista en formato Excel/CSV

#### Información Mostrada
- Nombre completo y email
- Nacionalidad y teléfono
- Fecha de registro
- Estado (activo/inactivo)
- Número de aplicaciones

### Crear Nuevo Usuario

1. Haga clic en "Crear Usuario"
2. Complete el formulario:
   - **Información Personal**: Nombre, email, teléfono
   - **Datos Adicionales**: Nacionalidad, fecha de nacimiento, dirección
   - **Configuración**: Rol (user/admin), estado activo
3. Haga clic en "Guardar Usuario"

#### Validaciones
- Email debe ser único en el sistema
- Contraseña debe cumplir políticas de seguridad
- Teléfono debe tener formato válido

### Editar Usuario Existente

1. Localice el usuario en la lista
2. Haga clic en el ícono de edición
3. Modifique los campos necesarios
4. Guarde los cambios

> **Importante**: Los cambios de rol requieren confirmación adicional.

### Gestión de Contraseñas

#### Restablecer Contraseña
1. En la vista de edición del usuario
2. Haga clic en "Restablecer Contraseña"
3. Se enviará un email al usuario con instrucciones

#### Desactivar Usuario
1. En la vista de edición del usuario
2. Cambie el estado a "Inactivo"
3. Confirme la acción

## Gestión de Programas

### Categorías de Programas

La plataforma maneja dos categorías principales:

#### Programas IE (Intercultural Experience)
- Work and Travel
- Au Pair
- Teacher's Program
- Internship
- Study Abroad
- Volunteer Program
- Language Exchange

#### Programas YFU (Youth For Understanding)
- High School Exchange
- University Exchange
- Language Immersion
- Cultural Exchange
- Summer Programs
- Gap Year Programs
- Family Programs

### Crear Nuevo Programa

**Navegación**: `Admin → Programas IE/YFU → Crear Programa`

#### Información Básica
- **Nombre del Programa**: Título descriptivo
- **Descripción**: Detalles completos del programa
- **País Destino**: País donde se desarrolla
- **Categoría**: IE o YFU
- **Subcategoría**: Tipo específico de programa

#### Detalles del Programa
- **Ubicación**: Ciudad o región específica
- **Fechas**: Inicio, fin y fecha límite de aplicación
- **Duración**: Tiempo total del programa
- **Capacidad**: Número máximo de participantes

#### Información Financiera
- **Costo**: Precio del programa
- **Moneda**: Seleccionar moneda correspondiente
- **Detalles de Pago**: Información sobre formas de pago

#### Configuración
- **Estado**: Activo/Inactivo
- **Visible en App**: Mostrar en aplicación móvil
- **Requisitos Especiales**: Criterios adicionales

### Gestión de Requisitos

Cada programa puede tener requisitos específicos:

#### Tipos de Requisitos
- **Documentos**: Pasaporte, certificados, fotos
- **Formularios**: Cuestionarios específicos
- **Evaluaciones**: Tests de idioma, entrevistas
- **Fechas Límite**: Plazos para cada requisito

#### Configurar Requisitos
1. En la vista de edición del programa
2. Vaya a la sección "Requisitos"
3. Agregue nuevos requisitos con:
   - Nombre y descripción
   - Tipo de requisito
   - Obligatorio/Opcional
   - Fecha límite

## Gestión de Aplicaciones

### Dashboard de Aplicaciones

**Navegación**: `Admin → Aplicaciones`

#### Estados de Aplicaciones
- **Pendiente**: Recién enviada, requiere revisión
- **En Revisión**: Siendo evaluada por el equipo
- **Aprobada**: Aceptada para el programa
- **Rechazada**: No cumple con los criterios
- **En Espera**: Requiere información adicional

### Proceso de Revisión

#### 1. Revisar Aplicación
1. Haga clic en una aplicación pendiente
2. Revise la información del aplicante:
   - **Datos Personales**: Información básica
   - **Motivación**: Carta de motivación
   - **Experiencia**: Experiencia previa relevante
   - **Referencias**: Contactos de referencia

#### 2. Verificar Documentos
1. En la sección "Documentos"
2. Revise cada documento subido:
   - **Pasaporte**: Vigencia y datos
   - **Certificados**: Autenticidad y relevancia
   - **Fotos**: Calidad y requisitos
3. Marque como "Verificado" o "Rechazado"

#### 3. Evaluar Requisitos
1. Verifique que todos los requisitos estén completos
2. Revise formularios específicos del programa
3. Confirme fechas y disponibilidad

#### 4. Tomar Decisión
1. Haga clic en "Aprobar" o "Rechazar"
2. Agregue comentarios explicativos
3. La decisión se notifica automáticamente al usuario

### Comunicación con Aplicantes

#### Mensajes Automáticos
- Confirmación de recepción de aplicación
- Notificación de documentos faltantes
- Decisión final (aprobación/rechazo)

#### Mensajes Personalizados
1. En la vista de aplicación
2. Haga clic en "Enviar Mensaje"
3. Escriba el mensaje personalizado
4. Seleccione si requiere respuesta

## Sistema de Documentos

### Tipos de Documentos

#### Documentos de Identidad
- **Pasaporte**: Página principal con foto
- **Cédula de Identidad**: Documento nacional
- **Certificado de Nacimiento**: Con apostilla si es necesario

#### Documentos Académicos
- **Certificados de Estudio**: Títulos y diplomas
- **Transcripciones**: Notas académicas
- **Certificados de Idioma**: TOEFL, IELTS, etc.

#### Documentos Médicos
- **Certificado Médico**: Estado de salud general
- **Vacunas**: Cartilla de vacunación
- **Seguro Médico**: Póliza de seguro internacional

### Gestión de Documentos

#### Verificación de Documentos
1. **Revisión Visual**: Verificar autenticidad y calidad
2. **Validación de Datos**: Confirmar información con aplicación
3. **Cumplimiento de Requisitos**: Verificar que cumple criterios
4. **Marcado de Estado**: Aprobar o rechazar con comentarios

#### Estados de Documentos
- **Pendiente**: Recién subido, no revisado
- **En Revisión**: Siendo verificado
- **Aprobado**: Cumple con todos los requisitos
- **Rechazado**: No cumple criterios, necesita resubir
- **Expirado**: Documento vencido

#### Solicitar Nuevos Documentos
1. En la aplicación del usuario
2. Sección "Documentos"
3. Haga clic en "Solicitar Documento"
4. Especifique tipo y requisitos
5. Se notifica al usuario automáticamente

## Sistema de Puntos y Recompensas

### Configuración de Puntos

#### Acciones que Generan Puntos
- **Completar Perfil**: 100 puntos
- **Subir Documentos**: 50 puntos por documento
- **Completar Aplicación**: 200 puntos
- **Referir Amigos**: 150 puntos por referencia exitosa
- **Completar Evaluaciones**: 75 puntos

#### Gestionar Reglas de Puntos
**Navegación**: `Admin → Puntos → Configuración`

1. Definir acciones que otorgan puntos
2. Establecer cantidad de puntos por acción
3. Configurar límites y restricciones
4. Activar/desactivar reglas según necesidad

### Gestión de Recompensas

#### Crear Nueva Recompensa
1. **Navegación**: `Admin → Recompensas → Crear`
2. **Información Básica**:
   - Nombre de la recompensa
   - Descripción detallada
   - Imagen representativa
3. **Configuración de Canje**:
   - Puntos requeridos
   - Cantidad disponible
   - Fecha de expiración
4. **Estado**: Activa/Inactiva

#### Tipos de Recompensas
- **Descuentos**: Reducción en costo de programas
- **Productos**: Artículos físicos (camisetas, tazas)
- **Servicios**: Asesorías adicionales, cursos
- **Experiencias**: Eventos especiales, workshops

#### Gestionar Canjes
**Navegación**: `Admin → Recompensas → Canjes`

1. Ver todas las solicitudes de canje
2. Aprobar o rechazar canjes
3. Marcar como entregado
4. Generar reportes de canjes

## Formularios Dinámicos

### Constructor de Formularios

La plataforma incluye un poderoso constructor de formularios drag & drop:

#### Tipos de Campos Disponibles
- **Texto Simple**: Input de una línea
- **Área de Texto**: Input multilínea
- **Selección Única**: Radio buttons
- **Selección Múltiple**: Checkboxes
- **Lista Desplegable**: Select dropdown
- **Fecha**: Date picker
- **Archivo**: Upload de documentos
- **Número**: Input numérico
- **Email**: Validación de email
- **Teléfono**: Formato de teléfono

#### Crear Nuevo Formulario
**Navegación**: `Admin → Formularios → Crear`

1. **Información Básica**:
   - Nombre del formulario
   - Descripción
   - Programa asociado

2. **Constructor Visual**:
   - Arrastre campos desde la barra lateral
   - Configure propiedades de cada campo
   - Establezca validaciones
   - Defina campos obligatorios

3. **Configuración Avanzada**:
   - Lógica condicional entre campos
   - Mensajes de validación personalizados
   - Estilos y apariencia

#### Propiedades de Campos
- **Etiqueta**: Texto mostrado al usuario
- **Placeholder**: Texto de ayuda
- **Requerido**: Campo obligatorio
- **Validación**: Reglas de validación
- **Opciones**: Para campos de selección

### Gestión de Respuestas

#### Ver Respuestas de Formularios
**Navegación**: `Admin → Formularios → [Nombre] → Respuestas`

1. Lista de todas las submissions
2. Filtros por fecha, usuario, estado
3. Exportación a Excel/CSV
4. Vista detallada de cada respuesta

#### Análisis de Respuestas
- **Estadísticas Generales**: Número de respuestas, tasa de completación
- **Análisis por Campo**: Distribución de respuestas
- **Gráficos**: Visualización de datos
- **Reportes**: Generación automática de informes

## Reportes y Estadísticas

### Dashboard de Reportes

**Navegación**: `Admin → Reportes`

#### Reportes Disponibles

##### 1. Reporte de Usuarios
- **Usuarios Registrados por Período**
- **Distribución por Nacionalidad**
- **Actividad de Usuarios**
- **Usuarios más Activos**

##### 2. Reporte de Aplicaciones
- **Aplicaciones por Programa**
- **Estados de Aplicaciones**
- **Tiempo Promedio de Procesamiento**
- **Tasas de Aprobación/Rechazo**

##### 3. Reporte de Programas
- **Popularidad de Programas**
- **Ocupación por Programa**
- **Ingresos por Programa**
- **Tendencias Estacionales**

##### 4. Reporte Financiero
- **Ingresos Totales**
- **Ingresos por Programa**
- **Proyecciones Financieras**
- **Análisis de Costos**

#### Generar Reportes Personalizados

1. **Seleccionar Tipo de Reporte**
2. **Configurar Parámetros**:
   - Rango de fechas
   - Filtros específicos
   - Campos a incluir
3. **Formato de Salida**: PDF, Excel, CSV
4. **Generar y Descargar**

### Exportación de Datos

#### Exportar Listas
- **Usuarios**: Lista completa con filtros
- **Aplicaciones**: Por estado, programa, fecha
- **Programas**: Información detallada
- **Transacciones**: Histórico financiero

#### Formatos Disponibles
- **Excel (.xlsx)**: Formato completo con fórmulas
- **CSV**: Para análisis en otras herramientas
- **PDF**: Reportes formateados para impresión

## Configuración del Sistema

### Configuración General

**Navegación**: `Admin → Configuración → General`

#### Información de la Organización
- **Nombre de la Empresa**
- **Dirección y Contacto**
- **Logo y Branding**
- **Redes Sociales**

#### Configuración de Email
- **Servidor SMTP**
- **Templates de Email**
- **Configuración de Notificaciones**
- **Firmas Automáticas**

### Configuración de Notificaciones

#### Tipos de Notificaciones
- **Email**: Confirmaciones, recordatorios, alertas
- **Push Notifications**: Para app móvil
- **SMS**: Para casos urgentes
- **Panel Admin**: Notificaciones internas

#### Configurar Plantillas
1. **Navegación**: `Admin → Configuración → Notificaciones`
2. **Seleccionar Tipo de Notificación**
3. **Editar Plantilla**:
   - Asunto del mensaje
   - Contenido HTML/texto
   - Variables dinámicas
4. **Vista Previa y Guardar**

### Gestión de Usuarios Administrativos

#### Crear Nuevo Administrador
**Navegación**: `Admin → Configuración → Administradores`

1. **Información Personal**
2. **Permisos y Roles**:
   - Administrador Completo
   - Solo Lectura
   - Permisos Específicos por Módulo
3. **Configuración de Acceso**

#### Permisos por Módulo
- **Gestión de Usuarios**: Crear, editar, eliminar
- **Gestión de Programas**: CRUD completo
- **Revisión de Aplicaciones**: Aprobar/rechazar
- **Gestión Financiera**: Ver/editar transacciones
- **Configuración del Sistema**: Cambios en configuración
- **Reportes**: Generar y ver reportes

### Mantenimiento del Sistema

#### Limpieza de Datos
- **Logs Antiguos**: Eliminar logs de más de 6 meses
- **Sesiones Expiradas**: Limpiar sesiones no utilizadas
- **Archivos Temporales**: Eliminar archivos cache
- **Tokens Expirados**: Limpiar tokens de acceso vencidos

#### Backup y Restauración
- **Backup Automático**: Configurar horarios
- **Backup Manual**: Generar backup inmediato
- **Restauración**: Desde backup específico
- **Verificación**: Integridad de backups

---

## Consejos y Mejores Prácticas

### Flujo de Trabajo Recomendado

1. **Revisión Matutina**:
   - Verificar dashboard para alertas
   - Revisar aplicaciones pendientes
   - Responder mensajes urgentes

2. **Gestión de Aplicaciones**:
   - Procesar en orden de llegada
   - Mantener comunicación clara con aplicantes
   - Documentar decisiones y razones

3. **Mantenimiento Regular**:
   - Actualizar información de programas
   - Revisar y optimizar formularios
   - Generar reportes mensuales

### Atajos de Teclado

- `Ctrl + D`: Ir al Dashboard
- `Ctrl + U`: Gestión de Usuarios
- `Ctrl + P`: Gestión de Programas
- `Ctrl + A`: Gestión de Aplicaciones
- `Ctrl + R`: Reportes
- `Ctrl + S`: Guardar cambios
- `Esc`: Cerrar modales

### Soporte Técnico

#### Contactos de Soporte
- **Email**: soporte@ie.org.py
- **Teléfono**: +595 XXX XXXXXX
- **Chat en Vivo**: Disponible en horario laboral
- **Tickets**: Sistema interno de tickets

#### Recursos Adicionales
- **Base de Conocimiento**: FAQ y tutoriales
- **Videos Tutoriales**: Guías paso a paso
- **Webinars**: Sesiones de capacitación mensual
- **Foro de Usuarios**: Comunidad de administradores

---

*Última actualización: Enero 2024*
*Versión del Sistema: 1.0.0*
