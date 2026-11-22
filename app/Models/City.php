<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Ciudad del catálogo (por ahora solo Paraguay). El participante la elige de una lista con buscador;
 * si no figura elige "Otra" y la escribe (users.city con city_id = null).
 */
class City extends Model
{
    protected $fillable = ['name', 'department', 'department_order', 'country', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * Lista enviada por IE (nov 2026): principales ciudades de cada departamento, en el orden de la lista.
     * Solo la usa la migración que crea el catálogo; después se administra en la tabla.
     */
    public const PARAGUAY = [
        ['Distrito Capital', ['Asunción']],
        ['Concepción', ['Concepción', 'Horqueta', 'Yby Yaú', 'Belén', 'Vallemí']],
        ['San Pedro', ['San Pedro de Ycuamandyyú', 'San Estanislao (Santaní)', 'Santa Rosa del Aguaray']],
        ['Cordillera', ['Caacupé', 'Tobatí', 'Piribebuy', 'Arroyos y Esteros', 'Altos', 'San Bernardino', 'Eusebio Ayala', 'Atyrá', 'Emboscada', 'Itacurubí de la Cordillera']],
        ['Guairá', ['Villarrica', 'Colonia Independencia', 'Paso Yobái']],
        ['Caaguazú', ['Coronel Oviedo', 'Caaguazú', 'J. Eulogio Estigarribia (Campo 9)', 'Yhú', 'San José de los Arroyos']],
        ['Caazapá', ['Caazapá', 'San Juan Nepomuceno']],
        ['Itapúa', ['Encarnación', 'Cambyretá', 'Tomás Romero Pereira (María Auxiliadora)', 'Coronel Bogado', 'Hohenau', 'Obligado', 'Bella Vista', 'Carmen del Paraná', 'San Pedro del Paraná', 'Capitán Miranda', 'Fram', 'Trinidad']],
        ['Misiones', ['San Juan Bautista', 'San Ignacio Guazú', 'Ayolas', 'Santa Rosa', 'Santiago', 'Villa Florida']],
        ['Paraguarí', ['Paraguarí', 'Carapeguá', 'Quiindy', 'Yaguarón', 'Pirayú', 'Ybycuí', 'Sapucai', 'Acahay', 'La Colmena']],
        ['Alto Paraná', ['Ciudad del Este', 'Presidente Franco', 'Minga Guazú', 'Hernandarias', 'Santa Rita', 'Naranjal', 'San Alberto', 'Itakyry', 'Juan León Mallorquín', 'Yguazú']],
        ['Central', ['Areguá', 'Luque', 'San Lorenzo', 'Capiatá', 'Lambaré', 'Fernando de la Mora', 'Limpio', 'Ñemby', 'Mariano Roque Alonso', 'Villa Elisa', 'Itauguá', 'Itá', 'San Antonio', 'Ypacaraí', 'Guarambaré', 'Villeta', 'J. Augusto Saldívar', 'Nueva Italia']],
        ['Ñeembucú', ['Pilar', 'Alberdi', 'Cerrito', 'Humaitá', 'Paso de Patria', 'Villalbín', 'Laureles']],
        ['Amambay', ['Pedro Juan Caballero', 'Capitán Bado', 'Bella Vista Norte']],
        ['Canindeyú', ['Salto del Guairá', 'Curuguaty', 'Katueté', 'Nueva Esperanza', 'La Paloma del Espíritu Santo', 'Corpus Christi']],
        ['Presidente Hayes', ['Villa Hayes', 'Benjamín Aceval', 'José Falcón', 'Nanawa', 'Pozo Colorado']],
        ['Boquerón', ['Filadelfia', 'Loma Plata', 'Mariscal José Félix Estigarribia', 'Neuland']],
        ['Alto Paraguay', ['Fuerte Olimpo', 'Puerto Casado', 'Carmelo Peralta', 'Bahía Negra']],
    ];

    /** Formas habituales de escribir algunas ciudades. */
    private const ALIASES = [
        'asuncion' => 'Asunción', 'asu' => 'Asunción', 'cde' => 'Ciudad del Este', 'pjc' => 'Pedro Juan Caballero',
        'fdo de la mora' => 'Fernando de la Mora', 'fernando' => 'Fernando de la Mora', 'mra' => 'Mariano Roque Alonso',
        'campo 9' => 'J. Eulogio Estigarribia (Campo 9)', 'santani' => 'San Estanislao (Santaní)',
        'maria auxiliadora' => 'Tomás Romero Pereira (María Auxiliadora)', 'mcal estigarribia' => 'Mariscal José Félix Estigarribia',
        'san pedro' => 'San Pedro de Ycuamandyyú', 'ycuamandyyu' => 'San Pedro de Ycuamandyyú',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('department_order')->orderBy('name');
    }

    /** "Luque — Central". Asunción no repite el departamento. */
    public function getLabelAttribute(): string
    {
        return $this->department === 'Distrito Capital' ? $this->name : "{$this->name} — {$this->department}";
    }

    /** Minúsculas, sin acentos, sin puntuación y con espacios simples. */
    public static function normalize(?string $value): string
    {
        $value = Str::of((string) $value)->ascii()->lower()->replaceMatches('/[^a-z0-9 ]+/', ' ')->squish();

        return (string) $value;
    }

    /**
     * Busca la ciudad del catálogo que corresponde a un texto libre ("asuncion", "Santaní",
     * "Luque - Central", "CDE"). Devuelve null si no hay una coincidencia clara.
     */
    public static function matchName(?string $text): ?self
    {
        $needle = self::normalize($text);
        if ($needle === '') {
            return null;
        }

        $cities = self::query()->where('country', 'Paraguay')->get();
        $byKey = fn (string $key) => $cities->first(fn (self $c) => in_array($key, self::keysFor($c), true));

        if ($city = $byKey($needle)) {
            return $city;
        }
        if (isset(self::ALIASES[$needle])) {
            return $cities->firstWhere('name', self::ALIASES[$needle]);
        }
        // "Luque, Central" / "Luque - Paraguay": probar con la primera parte del texto original.
        $first = self::normalize(preg_split('/[,\-\/]/', (string) $text)[0] ?? '');
        if ($first !== '' && $first !== $needle) {
            return $byKey($first) ?? (isset(self::ALIASES[$first]) ? $cities->firstWhere('name', self::ALIASES[$first]) : null);
        }

        return null;
    }

    /** Nombre completo, nombre sin paréntesis y texto entre paréntesis, normalizados. */
    private static function keysFor(self $city): array
    {
        $keys = [self::normalize($city->name)];
        if (preg_match('/^(.*?)\s*\((.*)\)$/', $city->name, $m)) {
            $keys[] = self::normalize($m[1]);
            $keys[] = self::normalize($m[2]);
        }

        return $keys;
    }

    /** Catálogo para la app y los formularios: [{id, name, department, label}]. */
    public static function catalog(): Collection
    {
        return self::active()->ordered()->get()
            ->map(fn (self $c) => ['id' => $c->id, 'name' => $c->name, 'department' => $c->department, 'label' => $c->label]);
    }
}
