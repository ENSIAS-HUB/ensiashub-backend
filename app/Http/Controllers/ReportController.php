<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Relations\Relation;

class ReportController extends Controller
{
    private function resolveModel(string $type): ?string
    {
        $map = Relation::morphMap();
        return $map[$type] ?? null;
    }

    // ── POST /{type}/{id}/report ──────────────────────────────────────────────
    public function report(Request $request, string $type, string $id): JsonResponse
    {
        $modelClass = $this->resolveModel($type);

        if (! $modelClass) {
            return response()->json(['success' => false, 'message' => 'Type inconnu'], 404);
        }

        $validated = $request->validate([
            'reason'  => 'required|in:spam,inappropriate,harassment,misinformation,other',
            'details' => 'nullable|string|max:1000',
        ]);

        // Vérifie que la ressource existe
        $modelClass::findOrFail($id);

        // Un seul signalement par utilisateur par ressource
        $alreadyReported = Report::where([
            'user_id'         => $request->user()->id,
            'reportable_type' => $modelClass,
            'reportable_id'   => $id,
        ])->exists();

        if ($alreadyReported) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà signalé ce contenu',
            ], 422);
        }

        $report = Report::create([
            'user_id'         => $request->user()->id,
            'reportable_type' => $modelClass,
            'reportable_id'   => $id,
            'reason'          => $validated['reason'],
            'details'         => $validated['details'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Signalement enregistré',
            'data'    => $report,
        ], 201);
    }
}
