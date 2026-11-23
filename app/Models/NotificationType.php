<?php

namespace App\Models;

use App\Support\NotificationCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Configuración de IE por tipo de aviso (push activa / obligatorio). Etiquetas y categorías: NotificationCatalog.
 */
class NotificationType extends Model
{
    private const CACHE_KEY = 'notification_types.settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'push_enabled', 'mandatory'];

    protected $casts = ['push_enabled' => 'boolean', 'mandatory' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** @return array<string, array{push_enabled: bool, mandatory: bool}> todos los tipos del catálogo */
    public static function settings(): array
    {
        $rows = Cache::rememberForever(self::CACHE_KEY, fn () => self::query()->get()
            ->mapWithKeys(fn ($t) => [$t->key => ['push_enabled' => $t->push_enabled, 'mandatory' => $t->mandatory]])->all());

        $out = [];
        foreach (array_keys(NotificationCatalog::TYPES) as $key) {
            $out[$key] = $rows[$key] ?? ['push_enabled' => true, 'mandatory' => NotificationCatalog::defaultMandatory($key)];
        }

        return $out;
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
