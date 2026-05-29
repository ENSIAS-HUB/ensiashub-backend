<?php

namespace App\Http\Controllers;

use App\Models\SavedItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Relations\Relation;

class SavedItemController extends Controller
{
    private function resolveModel(string $type): ?string
    {
        $map = Relation::morphMap();
        return $map[$type] ?? null;
    }

    // ── GET /saved ────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $items = SavedItem::with('saveable')
            ->where('user_id', $request->user()->id)
            ->when($request->input('type'), fn ($q, $t) => $q->where('saveable_type', $t))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    // ── POST /{type}/{id}/save ────────────────────────────────────────────────
    public function save(Request $request, string $type, string $id): JsonResponse
    {
        $modelClass = $this->resolveModel($type);

        if (! $modelClass) {
            return response()->json(['success' => false, 'message' => 'Type inconnu'], 404);
        }

        // Vérifie que la ressource existe
        $modelClass::findOrFail($id);

        $saved = SavedItem::firstOrCreate([
            'user_id'       => $request->user()->id,
            'saveable_type' => $modelClass,
            'saveable_id'   => $id,
        ]);

        return response()->json([
            'success' => true,
            'saved'   => true,
            'data'    => $saved,
        ], $saved->wasRecentlyCreated ? 201 : 200);
    }

    // ── DELETE /{type}/{id}/save ──────────────────────────────────────────────
    public function unsave(Request $request, string $type, string $id): JsonResponse
    {
        $modelClass = $this->resolveModel($type);

        if (! $modelClass) {
            return response()->json(['success' => false, 'message' => 'Type inconnu'], 404);
        }

        SavedItem::where([
            'user_id'       => $request->user()->id,
            'saveable_type' => $modelClass,
            'saveable_id'   => $id,
        ])->delete();

        return response()->json([
            'success' => true,
            'saved'   => false,
        ]);
    }
}
