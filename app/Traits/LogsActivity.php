<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected static $eventsToLog = ['created', 'updated', 'deleted'];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public static function bootLogsActivity()
    {
        // Log creation
        if (in_array('created', static::$eventsToLog)) {
            static::created(function (Model $model) {
                $model->logActivity('created');
            });
        }

        // Log updates
        if (in_array('updated', static::$eventsToLog)) {
            static::updated(function (Model $model) {
                $model->logActivity('updated');
            });
        }

        // Log deletion
        if (in_array('deleted', static::$eventsToLog)) {
            static::deleted(function (Model $model) {
                $model->logActivity('deleted');
            });
        }
    }

    /**
     * Create an activity log for this model
     */
    public function logActivity(string $action, string $description = null, array $properties = [])
    {
        $logName = $this->getLogNameToUse($action);
        $description = $description ?: $this->getDescriptionForEvent($action);

        $builder = ActivityLog::log($logName)
            ->performedOn($this)
            ->withAction($action)
            ->withProperties($properties);

        if (auth()->check()) {
            $builder->causedBy(auth()->user());
        }

        // Add changes for update events
        if ($action === 'updated' && $this->wasChanged()) {
            $builder->withChanges([
                'attributes' => $this->getChanges(),
                'old' => $this->getOriginal()
            ]);
        }

        return $builder->log($description);
    }

    /**
     * Get the log name to use for this model
     */
    protected function getLogNameToUse(string $action): string
    {
        return property_exists($this, 'logName') 
            ? $this->logName 
            : strtolower(class_basename($this));
    }

    /**
     * Get description for the event
     */
    protected function getDescriptionForEvent(string $action): string
    {
        $modelName = class_basename($this);
        $identifier = $this->name ?? $this->title ?? $this->getKey();

        switch ($action) {
            case 'created':
                return "Se creó {$modelName}: {$identifier}";
            case 'updated':
                return "Se actualizó {$modelName}: {$identifier}";
            case 'deleted':
                return "Se eliminó {$modelName}: {$identifier}";
            default:
                return "Acción '{$action}' en {$modelName}: {$identifier}";
        }
    }

    /**
     * Get attributes to log
     */
    protected function getAttributesToLog(): array
    {
        $attributes = property_exists($this, 'logAttributes') ? $this->logAttributes : static::$logAttributes;
        
        if (in_array('*', $attributes)) {
            return array_keys($this->getAttributes());
        }

        return $attributes;
    }

    /**
     * Get attributes to ignore when logging
     */
    protected function getAttributesToIgnore(): array
    {
        return property_exists($this, 'logAttributesToIgnore') 
            ? $this->logAttributesToIgnore 
            : ['updated_at', 'created_at'];
    }

    /**
     * Relationship to activity logs
     */
    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest('created_at');
    }

    /**
     * Get the latest activity
     */
    public function latestActivity()
    {
        return $this->morphOne(ActivityLog::class, 'subject')->latest('created_at');
    }

    /**
     * Manual log method for custom activities
     */
    public function logCustomActivity(string $description, array $properties = [], string $logName = null)
    {
        $builder = ActivityLog::log($logName ?: $this->getLogNameToUse('custom'))
            ->performedOn($this)
            ->withProperties($properties);

        if (auth()->check()) {
            $builder->causedBy(auth()->user());
        }

        return $builder->log($description);
    }
}
