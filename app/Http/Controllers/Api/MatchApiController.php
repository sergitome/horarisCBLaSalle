<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClubMatch;
use Illuminate\Http\JsonResponse;

class MatchApiController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ClubMatch::class);

        $query = ClubMatch::with(['season', 'team', 'lockerRoom']);

        if ($search = request('search')) {
            $query->where('home_team', 'like', "%{$search}%")
                ->orWhere('away_team', 'like', "%{$search}%")
                ->orWhere('competition', 'like', "%{$search}%");
        }

        foreach (['season_id', 'team_id', 'status'] as $filter) {
            if ($value = request($filter)) {
                $query->where($filter, $value);
            }
        }

        return response()->json($query->paginate((int) request('per_page', 15)));
    }
}
