<?php

namespace App\Services\Auditing;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public function logModel(string $action, Model $model, ?array $before, ?array $after, string $result = 'success'): void
    {
        $this->store($action, class_basename($model), $model->getKey(), $before, $after, $result);
    }

    public function logImport(string $action, string $entity, ?int $entityId, ?array $before = null, ?array $after = null, string $result = 'success'): void
    {
        $this->store($action, $entity, $entityId, $before, $after, $result);
    }

    private function store(string $action, string $entity, ?int $entityId, ?array $before, ?array $after, string $result): void
    {
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->id,
            'username' => $user?->username,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'before_data' => $before,
            'after_data' => $after,
            'ip' => Request::ip(),
            'result' => $result,
            'created_at' => now(),
        ]);
    }
}
