<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'changes',
        'action',
        'ip_address',
        'user_agent',
        'method',
        'url',
        'session_id',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relación polimórfica con el sujeto de la actividad
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Relación polimórfica con el causante de la actividad
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Scope para filtrar por tipo de log
     */
    public function scopeInLog($query, ...$logNames)
    {
        if (is_array($logNames[0])) {
            $logNames = $logNames[0];
        }

        return $query->whereIn('log_name', $logNames);
    }

    /**
     * Scope para filtrar por causante
     */
    public function scopeCausedBy($query, Model $causer)
    {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    /**
     * Scope para filtrar por sujeto
     */
    public function scopeForSubject($query, Model $subject)
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeWithAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para logs recientes
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Método estático para crear un log
     */
    public static function log(string $logName = null): ActivityLogBuilder
    {
        return new ActivityLogBuilder($logName);
    }

    /**
     * Método para obtener el valor anterior de un campo
     */
    public function getOldValue(string $key)
    {
        return $this->changes['old'][$key] ?? null;
    }

    /**
     * Método para obtener el nuevo valor de un campo
     */
    public function getNewValue(string $key)
    {
        return $this->changes['attributes'][$key] ?? null;
    }

    /**
     * Verificar si hubo cambios en un campo específico
     */
    public function hasChangedField(string $key): bool
    {
        return isset($this->changes['old'][$key]) || isset($this->changes['attributes'][$key]);
    }

    /**
     * Obtener todos los campos que cambiaron
     */
    public function getChangedFields(): array
    {
        if (!$this->changes) {
            return [];
        }

        $oldKeys = array_keys($this->changes['old'] ?? []);
        $newKeys = array_keys($this->changes['attributes'] ?? []);

        return array_unique(array_merge($oldKeys, $newKeys));
    }

    /**
     * Formatear la descripción para mostrar
     */
    public function getFormattedDescriptionAttribute()
    {
        $description = $this->description;
        
        // Reemplazar placeholders con valores reales
        if ($this->causer) {
            $description = str_replace('{causer}', $this->causer->name ?? 'Usuario', $description);
        }
        
        if ($this->subject) {
            $subjectName = $this->subject->name ?? $this->subject->title ?? "ID {$this->subject_id}";
            $description = str_replace('{subject}', $subjectName, $description);
        }

        return $description;
    }
}

/**
 * Builder class para crear logs de actividad
 */
class ActivityLogBuilder
{
    protected $logName;
    protected $description;
    protected $subject;
    protected $causer;
    protected $properties = [];
    protected $changes = [];
    protected $action;

    public function __construct(string $logName = null)
    {
        $this->logName = $logName ?? 'default';
    }

    public function performedOn(Model $model): self
    {
        $this->subject = $model;
        return $this;
    }

    public function causedBy(Model $causer): self
    {
        $this->causer = $causer;
        return $this;
    }

    public function withProperties(array $properties): self
    {
        $this->properties = array_merge($this->properties, $properties);
        return $this;
    }

    public function withProperty(string $key, $value): self
    {
        $this->properties[$key] = $value;
        return $this;
    }

    public function withChanges(array $changes): self
    {
        $this->changes = $changes;
        return $this;
    }

    public function withAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function log(string $description): ActivityLog
    {
        $this->description = $description;

        $activityLog = new ActivityLog([
            'log_name' => $this->logName,
            'description' => $this->description,
            'subject_type' => $this->subject ? $this->subject->getMorphClass() : null,
            'subject_id' => $this->subject ? $this->subject->getKey() : null,
            'causer_type' => $this->causer ? $this->causer->getMorphClass() : null,
            'causer_id' => $this->causer ? $this->causer->getKey() : null,
            'properties' => $this->properties,
            'changes' => $this->changes,
            'action' => $this->action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'method' => request()->method(),
            'url' => request()->fullUrl(),
            'session_id' => session()->getId(),
            'created_at' => Carbon::now(),
        ]);

        $activityLog->save();

        return $activityLog;
    }
}
