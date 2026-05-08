<?php

namespace App\Http\Controllers;

use App\Models\ImportClub;
use App\Models\ImportExecution;
use App\Services\Imports\FbibImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ImportExecution::class);

        return view('imports.index', [
            'clubs' => ImportClub::orderBy('name')->get(),
            'executions' => ImportExecution::with('importClub')->orderByDesc('started_at')->paginate()->withQueryString(),
        ]);
    }

    public function importTeams(Request $request, ImportClub $club, FbibImportService $service): RedirectResponse|JsonResponse
    {
        $this->authorize('create', ImportExecution::class);

        $execution = $service->importTeams($club, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'execution_id' => $execution->id,
                'status_url' => route('imports.status', $execution),
                'message' => $execution->result === 'success'
                    ? 'Importacio d\'equips completada.'
                    : 'La importacio d\'equips ha finalitzat amb errors.',
            ]);
        }

        return redirect()->route('imports.index')->with(
            'status',
            $execution->result === 'success'
                ? 'Importacio d\'equips completada.'
                : 'La importacio d\'equips ha finalitzat amb errors. Revisa l\'historial.'
        );
    }

    public function importMatches(Request $request, ImportClub $club, FbibImportService $service): RedirectResponse|JsonResponse
    {
        $this->authorize('create', ImportExecution::class);

        $execution = $service->importMatches($club, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'execution_id' => $execution->id,
                'status_url' => route('imports.status', $execution),
                'message' => $execution->result === 'success'
                    ? 'Importacio de partits completada.'
                    : 'La importacio de partits ha finalitzat amb errors.',
            ]);
        }

        return redirect()->route('imports.index')->with(
            'status',
            $execution->result === 'success'
                ? 'Importacio de partits completada.'
                : 'La importacio de partits ha finalitzat amb errors. Revisa l\'historial.'
        );
    }

    public function status(ImportExecution $execution): JsonResponse
    {
        $this->authorize('viewAny', ImportExecution::class);

        $execution->load('importClub');

        return response()->json([
            'id' => $execution->id,
            'type' => $execution->type,
            'result' => $execution->result,
            'created_count' => $execution->created_count,
            'updated_count' => $execution->updated_count,
            'skipped_count' => $execution->skipped_count,
            'error_count' => $execution->error_count,
            'finished_at' => optional($execution->finished_at)?->toIso8601String(),
            'details' => $execution->details ?? [],
            'club' => $execution->importClub?->name,
        ]);
    }
}
