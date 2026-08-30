<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait LogsAuditActivity
{
    protected static function bootLogsAuditActivity(): void
    {
        static::created(function (Model $model) {
            static::logAuditAction($model, 'created');
        });

        static::updated(function (Model $model) {
            static::logAuditAction($model, 'updated');
        });

        static::deleted(function (Model $model) {
            static::logAuditAction($model, 'deleted');
        });
    }

    protected static function logAuditAction(Model $model, string $action): void
    {
        // Don't log if running in console (e.g., seeders) unless it's a specific action
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        if ($action === 'updated') {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getDirty());
            $newValues = $model->getDirty();
        } elseif ($action === 'created') {
            $newValues = $model->getAttributes();
        } elseif ($action === 'deleted') {
            $oldValues = $model->getAttributes();
        }

        // Hide sensitive fields
        $hidden = ['password', 'remember_token'];
        foreach ($hidden as $field) {
            if (isset($oldValues[$field])) $oldValues[$field] = '********';
            if (isset($newValues[$field])) $newValues[$field] = '********';
        }

        $modelName = class_basename($model);
        $nameField = $model->name ?? $model->title ?? $model->po_number ?? $model->code ?? $model->id;
        $description = "{$action} {$modelName}: {$nameField}";

        AuditLog::log(
            action: $action,
            description: ucfirst($description),
            modelType: get_class($model),
            modelId: $model->getKey(),
            oldValues: empty($oldValues) ? null : $oldValues,
            newValues: empty($newValues) ? null : $newValues
        );
    }
}
