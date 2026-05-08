<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::query();

        if ($search = request('search')) {
            $query->where(fn ($subquery) => $subquery
                ->where('username', 'like', "%{$search}%")
                ->orWhere('entity', 'like', "%{$search}%")
                ->orWhere('action', 'like', "%{$search}%"));
        }

        if ($entity = request('entity')) {
            $query->where('entity', $entity);
        }

        return view('audit-logs.index', [
            'logs' => $query->latest('created_at')->paginate()->withQueryString(),
            'entities' => AuditLog::query()->select('entity')->distinct()->orderBy('entity')->pluck('entity'),
        ]);
    }
}
