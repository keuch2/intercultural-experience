# 🌍 SISTEMA DE MÚLTIPLES DESTINOS PARA PROGRAMAS

**Fecha:** 22 de Octubre, 2025 - 7:15 AM  
**Status:** ✅ **100% IMPLEMENTADO**

---

## 📋 RESUMEN EJECUTIVO

Se implementó un sistema profesional de múltiples destinos que permite que cada programa tenga **uno o más países** como destino, con la capacidad de marcar un país como principal y especificar ubicaciones específicas dentro de cada país.

### ⚙️ Solución Implementada: **Opción 2 - Tabla de Países + Many-to-Many**

---

## 🎯 PROBLEMA RESUELTO

**Antes:** Los programas solo podían tener un destino (campo `country` en tabla `programs`)

**Ahora:** Los programas pueden tener múltiples destinos con:
- ✅ País principal marcado
- ✅ Países secundarios opcionales
- ✅ Ubicaciones específicas por país (ej: "California, Texas")
- ✅ Orden de visualización personalizado
- ✅ Validación de países activos
- ✅ Organización por regiones
- ✅ Emojis de banderas para mejor UX

---

## 🗄️ ESTRUCTURA DE BASE DE DATOS

### 1. Tabla `countries`

```sql
CREATE TABLE countries (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,           -- "United States"
    code VARCHAR(3) UNIQUE NOT NULL,             -- "USA" (ISO 3166-1 alpha-3)
    iso2 VARCHAR(2) NULL,                        -- "US" (ISO 3166-1 alpha-2)
    region VARCHAR(50) NULL,                     -- "North America", "Europe", etc.
    flag_emoji VARCHAR(10) NULL,                 -- "🇺🇸"
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,                 -- Para ordenar en listas
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_code (code),
    INDEX idx_region (region),
    INDEX idx_is_active (is_active)
);
```

**Campos clave:**
- `code` - Código ISO de 3 letras (estándar internacional)
- `iso2` - Código ISO de 2 letras (para APIs externas)
- `region` - Agrupación geográfica
- `flag_emoji` - Para mostrar banderas en UI 🇺🇸 🇨🇦 🇬🇧
- `display_order` - Control de orden en listas

---

### 2. Tabla Pivote `program_country`

```sql
CREATE TABLE program_country (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    program_id BIGINT NOT NULL,
    country_id BIGINT NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,            -- ⭐ País principal del programa
    display_order INT DEFAULT 0,                 -- Orden de visualización
    specific_locations TEXT NULL,                -- "California, Texas, Florida"
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_program_country (program_id, country_id),
    INDEX idx_program_id (program_id),
    INDEX idx_country_id (country_id),
    INDEX idx_is_primary (is_primary)
);
```

**Características:**
- Un programa no puede tener el mismo país dos veces (`UNIQUE`)
- Eliminar programa elimina sus países (`CASCADE`)
- `is_primary` marca el destino principal
- `specific_locations` permite detallar ciudades/estados

---

## 📦 MODELOS ELOQUENT

### Modelo `Country`

```php
class Country extends Model
{
    protected $fillable = [
        'name', 'code', 'iso2', 'region', 
        'flag_emoji', 'is_active', 'display_order'
    ];

    // Relación con Programs
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_country')
            ->withPivot(['is_primary', 'display_order', 'specific_locations'])
            ->withTimestamps();
    }

    // Solo programas donde este país es principal
    public function primaryPrograms(): BelongsToMany
    {
        return $this->programs()->wherePivot('is_primary', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    // Accessor: Nombre con emoji
    public function getFullNameAttribute(): string
    {
        return ($this->flag_emoji ? $this->flag_emoji . ' ' : '') . $this->name;
        // Resultado: "🇺🇸 United States"
    }
}
```

---

### Modelo `Program` (Actualizado)

```php
class Program extends Model
{
    // Relación con Countries (múltiples destinos)
    public function countries()
    {
        return $this->belongsToMany(Country::class, 'program_country')
            ->withPivot(['is_primary', 'display_order', 'specific_locations'])
            ->withTimestamps()
            ->orderBy('program_country.display_order');
    }

    // Obtener solo el país principal
    public function primaryCountry()
    {
        return $this->belongsToMany(Country::class, 'program_country')
            ->wherePivot('is_primary', true)
            ->first();
    }

    // Obtener países activos
    public function activeCountries()
    {
        return $this->countries()->where('countries.is_active', true);
    }
}
```

---

## 🌱 SEEDER DE PAÍSES

Se incluyen **43 países** predefinidos organizados por región:

### Regiones Disponibles:
- **North America** (3): USA, Canada, Mexico
- **Europe** (19): UK, Ireland, France, Germany, Spain, Italy, etc.
- **Oceania** (2): Australia, New Zealand
- **Asia** (6): Japan, South Korea, China, Singapore, Thailand, India
- **South America** (6): Brazil, Argentina, Chile, Colombia, Peru, Ecuador
- **Central America** (2): Costa Rica, Panama
- **Africa** (3): South Africa, Morocco, Egypt
- **Middle East** (2): UAE, Israel

**Ejecutar seeder:**
```bash
php artisan db:seed --class=CountrySeeder
```

---

## 🚀 EJEMPLOS DE USO

### 1. Crear un Programa con Múltiples Destinos

```php
// Crear programa
$program = Program::create([
    'name' => 'Au Pair USA & Canada',
    'description' => 'Programa de au pair en Norteamérica',
    'main_category' => 'IE',
    'subcategory' => 'Au Pair',
    'is_active' => true
]);

// Obtener países
$usa = Country::where('code', 'USA')->first();
$canada = Country::where('code', 'CAN')->first();

// Asociar países al programa
$program->countries()->attach($usa->id, [
    'is_primary' => true,                    // USA es el destino principal
    'display_order' => 1,
    'specific_locations' => 'California, Texas, New York'
]);

$program->countries()->attach($canada->id, [
    'is_primary' => false,                   // Canada es secundario
    'display_order' => 2,
    'specific_locations' => 'Toronto, Vancouver'
]);
```

---

### 2. Obtener Destinos de un Programa

```php
// Todos los destinos
$destinations = $program->countries;

foreach ($destinations as $country) {
    echo $country->full_name;                // 🇺🇸 United States
    echo $country->pivot->is_primary;        // true/false
    echo $country->pivot->specific_locations; // California, Texas, New York
}

// Solo el país principal
$primaryCountry = $program->primaryCountry();
echo $primaryCountry->name;                   // United States

// Solo países activos
$activeDestinations = $program->activeCountries()->get();
```

---

### 3. Buscar Programas por País

```php
// Programas que tienen USA como destino
$usaPrograms = Country::where('code', 'USA')
    ->first()
    ->programs;

// Programas donde USA es el destino principal
$usaPrimaryPrograms = Country::where('code', 'USA')
    ->first()
    ->primaryPrograms;

// Programas por región
$europeanCountries = Country::where('region', 'Europe')->pluck('id');
$europeanPrograms = Program::whereHas('countries', function($query) use ($europeanCountries) {
    $query->whereIn('country_id', $europeanCountries);
})->get();
```

---

### 4. Actualizar Destinos de un Programa

```php
// Reemplazar todos los destinos
$program->countries()->sync([
    1 => ['is_primary' => true, 'display_order' => 1],
    2 => ['is_primary' => false, 'display_order' => 2],
    3 => ['is_primary' => false, 'display_order' => 3],
]);

// Agregar un destino adicional
$spain = Country::where('code', 'ESP')->first();
$program->countries()->attach($spain->id, [
    'is_primary' => false,
    'display_order' => 4,
    'specific_locations' => 'Madrid, Barcelona'
]);

// Remover un destino
$program->countries()->detach($canada->id);

// Actualizar datos de pivote
$program->countries()->updateExistingPivot($usa->id, [
    'specific_locations' => 'California, Florida, Massachusetts'
]);
```

---

## 📱 USO EN VISTAS BLADE

### Mostrar Destinos en Card de Programa

```blade
<div class="card">
    <div class="card-header">
        <h5>{{ $program->name }}</h5>
    </div>
    <div class="card-body">
        <h6>Destinos:</h6>
        <div class="d-flex flex-wrap gap-2">
            @foreach($program->countries as $country)
                <span class="badge badge-{{ $country->pivot->is_primary ? 'primary' : 'secondary' }}">
                    {{ $country->flag_emoji }} {{ $country->name }}
                    @if($country->pivot->is_primary)
                        <i class="fas fa-star"></i>
                    @endif
                </span>
            @endforeach
        </div>
        
        {{-- Mostrar ubicaciones específicas --}}
        @if($program->primaryCountry() && $program->primaryCountry()->pivot->specific_locations)
            <p class="mt-2">
                <small><strong>Ubicaciones:</strong> 
                    {{ $program->primaryCountry()->pivot->specific_locations }}
                </small>
            </p>
        @endif
    </div>
</div>
```

---

### Formulario de Selección de Países

```blade
<form action="{{ route('admin.programs.update', $program) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="form-group">
        <label>Seleccionar Destinos</label>
        
        {{-- Agrupar por región --}}
        @foreach(\App\Models\Country::active()->ordered()->get()->groupBy('region') as $region => $countries)
            <optgroup label="{{ $region }}">
                @foreach($countries as $country)
                    <div class="custom-control custom-checkbox">
                        <input 
                            type="checkbox" 
                            name="countries[]" 
                            value="{{ $country->id }}"
                            id="country_{{ $country->id }}"
                            {{ $program->countries->contains($country->id) ? 'checked' : '' }}
                            class="custom-control-input">
                        <label class="custom-control-label" for="country_{{ $country->id }}">
                            {{ $country->full_name }}
                        </label>
                    </div>
                @endforeach
            </optgroup>
        @endforeach
    </div>
    
    <div class="form-group">
        <label>País Principal</label>
        <select name="primary_country_id" class="form-control">
            @foreach($program->countries as $country)
                <option value="{{ $country->id }}" 
                    {{ $country->pivot->is_primary ? 'selected' : '' }}>
                    {{ $country->full_name }}
                </option>
            @endforeach
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary">Guardar</button>
</form>
```

---

## 🔌 USO EN API

### Endpoint: Listar Programas con Destinos

```php
// Controller
public function index()
{
    $programs = Program::with(['countries' => function($query) {
        $query->where('is_active', true)->ordered();
    }])->get();

    return response()->json($programs);
}

// Response JSON
{
    "id": 1,
    "name": "Au Pair USA & Canada",
    "countries": [
        {
            "id": 1,
            "name": "United States",
            "code": "USA",
            "flag_emoji": "🇺🇸",
            "pivot": {
                "is_primary": true,
                "specific_locations": "California, Texas, New York"
            }
        },
        {
            "id": 2,
            "name": "Canada",
            "code": "CAN",
            "flag_emoji": "🇨🇦",
            "pivot": {
                "is_primary": false,
                "specific_locations": "Toronto, Vancouver"
            }
        }
    ]
}
```

---

## 📊 REPORTES Y ESTADÍSTICAS

### Programas por País

```php
// Contar programas por país
$programsByCountry = Country::withCount('programs')
    ->orderBy('programs_count', 'desc')
    ->get();

// Mostrar top 10
foreach ($programsByCountry->take(10) as $country) {
    echo "{$country->full_name}: {$country->programs_count} programas\n";
}
```

### Programas por Región

```php
// Agrupar por región
$programsByRegion = Country::with('programs')
    ->get()
    ->groupBy('region')
    ->map(function($countries) {
        return $countries->sum(function($country) {
            return $country->programs->count();
        });
    })
    ->sortDesc();
```

---

## 🎨 MIGRACIONES CREADAS

### 1. `2025_10_22_095111_create_countries_table.php`
- Crea tabla `countries`
- 43 países predefinidos via seeder

### 2. `2025_10_22_095138_create_program_country_table.php`
- Crea tabla pivote `program_country`
- Relación many-to-many
- Restricción UNIQUE para evitar duplicados

---

## ⚡ COMANDOS DE MIGRACIÓN

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeder de países
php artisan db:seed --class=CountrySeeder

# Rollback si necesario
php artisan migrate:rollback --step=2

# Refresh completo
php artisan migrate:fresh --seed
```

---

## 🔍 CONSULTAS ÚTILES

### SQL Directo

```sql
-- Ver todos los destinos de un programa
SELECT 
    p.name AS program_name,
    c.name AS country_name,
    c.flag_emoji,
    pc.is_primary,
    pc.specific_locations
FROM programs p
JOIN program_country pc ON p.id = pc.program_id
JOIN countries c ON pc.country_id = c.id
WHERE p.id = 1
ORDER BY pc.display_order;

-- Países más populares
SELECT 
    c.name,
    c.flag_emoji,
    COUNT(pc.program_id) as total_programs,
    SUM(pc.is_primary) as primary_programs
FROM countries c
LEFT JOIN program_country pc ON c.id = pc.country_id
GROUP BY c.id
ORDER BY total_programs DESC;

-- Programas multi-destino
SELECT 
    p.name,
    COUNT(pc.country_id) as destination_count
FROM programs p
LEFT JOIN program_country pc ON p.id = pc.program_id
GROUP BY p.id
HAVING destination_count > 1;
```

---

## 📈 VENTAJAS DEL SISTEMA

### ✅ Escalabilidad
- Fácil agregar nuevos países
- No hay límite en destinos por programa
- Estructura normalizada

### ✅ Flexibilidad
- Un programa puede tener 1 o N destinos
- País principal claramente identificado
- Ubicaciones específicas opcionales

### ✅ Performance
- Índices en campos críticos
- Relaciones eager loading
- Queries optimizadas

### ✅ UX Mejorada
- Emojis de banderas 🌍
- Agrupación por regiones
- Filtros por país/región

### ✅ Mantenibilidad
- Datos centralizados
- Validación automática
- Fácil de actualizar

---

## 🔄 MIGRACIÓN DE DATOS EXISTENTES

Si tienes programas con el campo `country` antiguo:

```php
// Comando Artisan para migrar
php artisan tinker

// Script de migración
$programs = Program::whereNotNull('country')->get();

foreach ($programs as $program) {
    // Buscar país por nombre
    $country = Country::where('name', $program->country)
        ->orWhere('code', $program->country)
        ->first();
    
    if ($country) {
        // Asociar como país principal
        $program->countries()->attach($country->id, [
            'is_primary' => true,
            'display_order' => 1
        ]);
        
        echo "✓ {$program->name} → {$country->name}\n";
    } else {
        echo "✗ {$program->name} → País '{$program->country}' no encontrado\n";
    }
}
```

---

## 🧪 TESTING

### Test de Relaciones

```php
// tests/Feature/ProgramCountryTest.php

public function test_program_can_have_multiple_countries()
{
    $program = Program::factory()->create();
    $usa = Country::where('code', 'USA')->first();
    $canada = Country::where('code', 'CAN')->first();
    
    $program->countries()->attach([
        $usa->id => ['is_primary' => true],
        $canada->id => ['is_primary' => false],
    ]);
    
    $this->assertCount(2, $program->countries);
    $this->assertTrue($program->primaryCountry()->is($usa));
}

public function test_cannot_attach_same_country_twice()
{
    $program = Program::factory()->create();
    $usa = Country::where('code', 'USA')->first();
    
    $program->countries()->attach($usa->id);
    
    $this->expectException(QueryException::class);
    $program->countries()->attach($usa->id);
}
```

---

## 📚 DOCUMENTACIÓN ADICIONAL

### Archivos Relacionados

```
database/
  migrations/
    - 2025_10_22_095111_create_countries_table.php
    - 2025_10_22_095138_create_program_country_table.php
  seeders/
    - CountrySeeder.php

app/
  Models/
    - Country.php (nuevo)
    - Program.php (actualizado)
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

- [x] Migración de tabla `countries`
- [x] Migración de tabla pivote `program_country`
- [x] Modelo `Country` con relaciones
- [x] Modelo `Program` actualizado
- [x] Seeder con 43 países
- [x] Relaciones bidireccionales
- [x] Scopes útiles
- [x] Accessors (full_name)
- [x] Índices de BD
- [x] Restricciones UNIQUE
- [x] Documentación completa

---

## 🎉 CONCLUSIÓN

El sistema de múltiples destinos está **100% implementado y listo para producción**. Permite una gestión flexible y escalable de países de destino para programas internacionales.

**Beneficios principales:**
- ✅ Soporte para múltiples destinos por programa
- ✅ Identificación clara de país principal
- ✅ Validación y normalización de datos
- ✅ Búsquedas y filtros eficientes
- ✅ UX mejorada con banderas y regiones
- ✅ Fácil mantenimiento y escalabilidad

---

**Implementado por:** Cascade AI  
**Fecha:** 22 de Octubre, 2025  
**Status:** ✅ Production Ready 🌍
