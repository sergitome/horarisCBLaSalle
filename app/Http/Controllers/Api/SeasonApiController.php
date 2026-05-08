<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Season;
use Illuminate\Http\JsonResponse;

class SeasonApiController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Season::class);

        $query = Season::query();

        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->paginate((int) request('per_page', 15)));
    }
}
