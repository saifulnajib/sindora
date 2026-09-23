<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::recordAudit($model, 'created');
        });

        static::updated(function ($model) {
            self::recordAudit($model, 'updated');
        });

        static::deleted(function ($model) {
            self::recordAudit($model, 'deleted');
        });

        // Optional: handle restores for SoftDeletes models
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                self::recordAudit($model, 'restored');
            });
        }
    }

    protected static function recordAudit($model, string $event): void
    {
        // Avoid auditing the AuditLog itself to prevent infinite loop
        if ($model instanceof AuditLog) {
            return;
        }

        try {
            $oldValues = null;
            $newValues = null;

            if ($event === 'created') {
                $newValues = $model->getAttributes();
            } elseif ($event === 'updated') {
                $oldValues = $model->getOriginal();
                $newValues = $model->getChanges();
                // Filter to only changed attributes + keep old for comparison
                if (empty($newValues)) {
                    return;
                }
            } elseif ($event === 'deleted' || $event === 'restored') {
                $oldValues = $model->getOriginal();
            }

            // Hide sensitive casts if any
            $hidden = ['password', 'remember_token'];

            if (is_array($newValues)) {
                $newValues = array_diff_key($newValues, array_flip($hidden));
            }
            if (is_array($oldValues)) {
                $oldValues = array_diff_key($oldValues, array_flip($hidden));
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => $event,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'url' => Request::fullUrl() ?: null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Silently ignore audit failures to not break main transaction
            // Log for debugging if needed
            logger()->warning('Auditable failed: '.$e->getMessage(), [
                'model' => get_class($model),
                'id' => $model->getKey() ?? null,
                'event' => $event,
            ]);
        }
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }
}
