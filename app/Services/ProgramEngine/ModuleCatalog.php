<?php

namespace App\Services\ProgramEngine;

/**
 * Catálogo FIJO de módulos activables por programa. Agregar un tipo nuevo de
 * módulo requiere código (admin tab + pantalla mobile); activarlo en un programa
 * no.
 */
final class ModuleCatalog
{
    public const ENGLISH_TEST = 'english_test';

    public const VISA = 'visa';

    public const MATCHES = 'matches';

    public const JOB_POOL = 'job_pool';

    public const PLACEMENT = 'placement';

    public const SUPPORT = 'support';

    public const RESOURCES = 'resources';

    /** @return array<string, array{label:string, icon:string, admin_tab:string, mobile_screen:string, implemented:bool}> */
    public static function all(): array
    {
        return [
            self::ENGLISH_TEST => ['label' => 'Test de inglés', 'icon' => 'fa-language', 'admin_tab' => 'english', 'mobile_screen' => 'ProgramEnglishTest', 'implemented' => true],
            self::VISA => ['label' => 'Gestión de Visa J1', 'icon' => 'fa-passport', 'admin_tab' => 'visa', 'mobile_screen' => 'ProgramVisa', 'implemented' => true],
            self::MATCHES => ['label' => 'Matches (familias)', 'icon' => 'fa-people-roof', 'admin_tab' => 'matches', 'mobile_screen' => 'ProgramMatches', 'implemented' => false],
            self::JOB_POOL => ['label' => 'Pool de Ofertas Laborales', 'icon' => 'fa-briefcase', 'admin_tab' => 'job_pool', 'mobile_screen' => 'JobPool', 'implemented' => true],
            self::PLACEMENT => ['label' => 'Job Placement', 'icon' => 'fa-building-user', 'admin_tab' => 'placement', 'mobile_screen' => 'JobPlacement', 'implemented' => true],
            self::SUPPORT => ['label' => 'Support', 'icon' => 'fa-headset', 'admin_tab' => 'support', 'mobile_screen' => 'ProgramSupport', 'implemented' => true],
            self::RESOURCES => ['label' => 'Recursos del programa', 'icon' => 'fa-folder-open', 'admin_tab' => 'resources', 'mobile_screen' => 'ProgramResources', 'implemented' => true],
        ];
    }

    /** @return string[] */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function isValid(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /** @param string[] $keys */
    public static function sanitize(array $keys): array
    {
        return array_values(array_intersect(self::keys(), array_unique($keys)));
    }
}
