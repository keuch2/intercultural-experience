# CORRECCIÓN: Modelo VisaProcess

Fecha: 22 de Octubre, 2025  
Status: COMPLETADO

## ERROR

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'current_status' 
in 'where clause'
```

**Ubicación:** app/Http/Controllers/Admin/AdminVisaController.php:20

## CAUSA

El modelo VisaProcess estaba usando `current_status` en todos los scopes,
pero la columna real en la BD se llama `current_step`.

**En la migración (visa_processes):**
- ✅ Columna: `current_step`
- ✅ Columna: `visa_result` (para approved/rejected)

**En el modelo (ANTES):**
- ❌ Scopes usaban: `current_status`
- ❌ Métodos usaban: `current_status`

## CORRECCIONES APLICADAS

### 1. Scopes Corregidos

```php
// ❌ ANTES
public function scopeApproved($query)
{
    return $query->where('current_status', 'visa_approved');
}

// ✅ DESPUÉS
public function scopeApproved($query)
{
    return $query->where('visa_result', 'approved');
}
```

**Cambios en scopes:**
- `scopeByStatus()` → usa `current_step`
- `scopeApproved()` → usa `visa_result = 'approved'`
- `scopeRejected()` → usa `visa_result = 'rejected'`
- `scopeInProgress()` → usa `visa_result NOT IN (approved, rejected)`
- `scopePendingSevisPayment()` → usa `current_step` y `sevis_paid`
- `scopeWithAppointment()` → usa `consular_appointment_scheduled`

### 2. Métodos Corregidos

**changeStatus():**
- `$this->current_status` → `$this->current_step`

**getProgressPercentage():**
- `$this->current_status` → `$this->current_step`
- `'visa_approved'` → `$this->visa_result === 'approved'`
- `'visa_rejected'` → `$this->visa_result === 'rejected'`

**getNextStep():**
- `$this->current_status` → `$this->current_step`

**canAdvanceToNextStatus():**
- `$this->current_status` → `$this->current_step`
- `$this->visa_result` para validaciones

**markSevisPaid():**
- `sevis_paid_at` → `sevis_paid` (boolean) + `sevis_paid_date`

**markConsularFeePaid():**
- `consular_fee_paid_at` → `consular_fee_paid` + `consular_fee_paid_date`

**scheduleAppointment():**
- `appointment_date` → `consular_appointment_date`
- `appointment_location` → `consular_appointment_location`
- Agrega: `consular_appointment_scheduled = true`

**getDaysUntilAppointment():**
- `appointment_date` → `consular_appointment_date`

**getTimeline():**
- `$this->current_status` → `$this->current_step`

## ESTRUCTURA CORRECTA DE COLUMNAS

### Estado del Proceso (current_step):
- documentation_pending
- sponsor_interview_pending
- sponsor_interview_approved
- job_interview_pending
- job_interview_approved
- ds160_pending
- ds160_completed
- ds2019_pending
- ds2019_received
- sevis_paid
- consular_fee_paid
- appointment_scheduled
- in_correspondence

### Resultado Final (visa_result):
- pending (default)
- approved
- correspondence
- rejected

## ARCHIVOS MODIFICADOS

1. ✅ app/Models/VisaProcess.php
   - 16 scopes/métodos corregidos
   - current_status → current_step
   - visa_approved/visa_rejected → visa_result

## RESULTADO

- ✅ Todos los scopes usan nombres correctos de columnas
- ✅ Métodos usan current_step y visa_result
- ✅ El dashboard de visa ahora funciona sin errores
- ✅ Queries SQL ejecutan correctamente

## VERIFICACIÓN

El AdminVisaController ahora puede ejecutar:
```php
VisaProcess::inProgress()->count()  // ✅ Funciona
VisaProcess::approved()->count()    // ✅ Funciona
VisaProcess::rejected()->count()    // ✅ Funciona
```
