<?php

namespace App\Http\Controllers;

use App\Models\Share;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Relations\Relation;

class ShareController extends Controller
{
    private function resolveModel(string $type): ?string
    {
        $map = Relation::morphMap();
        return $map[$type] ?? null;
    }

    // ── POST /{type}/{id}/share ───────────────────────────────────────────────
    public function share(Request $request, string $type, string $id): JsonResponse
    {
        $modelClass = $this->resolveModel($type);

        if (! $modelClass) {
            return response()->json(['success' => false, 'message' => 'Type inconnu'], 404);
        }

        $validated = $request->validate([
            'channel'         => 'required|in:internal,link,whatsapp,other',
            'target_group_id' => 'nullable|string|exists:groups,id',
        ]);

        // Vérifie que la ressource existe
        $modelClass::findOrFail($id);

        $share = Share::create([
            'user_id'         => $request->user()->id,
            'shareable_type'  => $modelClass,
            'shareable_id'    => $id,
            'channel'         => $validated['channel'],
            'target_group_id' => $validated['target_group_id'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $share,
        ], 201);
    }
}
