<?php

namespace App\Support;

use App\Services\Auditing\AuditLogger;

trait Auditable
{
    protected array $auditBeforeState = [];

    public static function bootAuditable(): void
    {
        static::created(function ($model): void {
            app(AuditLogger::class)->logModel('create', $model, null, $model->fresh()?->toArray() ?? $model->toArray());
        });

        static::updating(function ($model): void {
            $model->auditBeforeState = $model->getOriginal();
        });

        static::updated(function ($model): void {
            app(AuditLogger::class)->logModel(
                'update',
                $model,
                $model->auditBeforeState ?: $model->getOriginal(),
                $model->fresh()?->toArray() ?? $model->toArray()
            );
        });

        static::deleting(function ($model): void {
            $model->auditBeforeState = $model->toArray();
        });

        static::deleted(function ($model): void {
            app(AuditLogger::class)->logModel('delete', $model, $model->auditBeforeState ?: $model->toArray(), null);
        });
    }
}
