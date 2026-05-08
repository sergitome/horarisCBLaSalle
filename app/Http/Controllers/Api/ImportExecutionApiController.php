<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImportExecution;
use Illuminate\Http\JsonResponse;

class ImportExecutionApiController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ImportExecution::class);

        $query = ImportExecution::with('importClub')->orderByDesc('started_at');

        if ($type = request('type')) {
            $query->where('type', $type);
        }

        if ($clubId = request('import_club_id')) {
            $query->where('import_club_id', $clubId);
        }

        return response()->json($query->paginate((int) request('per_page', 15)));
    }
}
