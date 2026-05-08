<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class TeamApiController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Team::class);

        $query = Team::with(['season', 'importClub']);

        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('display_name', 'like', "%{$search}%");
        }

        foreach (['season_id', 'import_club_id'] as $filter) {
            if ($value = request($filter)) {
                $query->where($filter, $value);
            }
        }

        return response()->json($query->paginate((int) request('per_page', 15)));
    }
}
