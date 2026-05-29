<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;

class MenuItemController extends Controller
{
    /**
     * GET /api/menu-items
     * Returns all available menu items normalized for the frontend.
     */
    public function index(Request $request)
    {
        $query = MenuItem::query();

        // Filter by availability (default: only available)
        if ($request->query('all') !== 'true') {
            $query->where('estDisponible', true);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('categorie', $request->query('category'));
        }

        $items = $query->orderBy('categorie')->orderBy('nomPlat')->get();

        return response()->json([
            'success' => true,
            'data'    => $items->map(fn ($m) => $m->toApiArray()),
        ]);
    }

    /**
     * GET /api/menu-items/categories
     * Returns the list of distinct categories.
     */
    public function categories()
    {
        $cats = MenuItem::where('estDisponible', true)
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie');

        return response()->json(['success' => true, 'data' => $cats]);
    }

    /**
     * POST /api/menu-items
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nomPlat'      => 'required|string|max:255',
            'description'  => 'nullable|string',
            'image_url'    => 'nullable|url',
            'prix'         => 'required|numeric|min:0',
            'categorie'    => 'required|string|max:100',
            'estDisponible'=> 'boolean',
        ]);

        $item = MenuItem::create($data);

        return response()->json(['success' => true, 'data' => $item->toApiArray()], 201);
    }

    /**
     * GET /api/menu-items/{id}
     */
    public function show(string $id)
    {
        $item = MenuItem::findOrFail($id);
        return response()->json(['success' => true, 'data' => $item->toApiArray()]);
    }

    /**
     * PUT/PATCH /api/menu-items/{id}
     */
    public function update(Request $request, string $id)
    {
        $item = MenuItem::findOrFail($id);

        $data = $request->validate([
            'nomPlat'      => 'sometimes|required|string|max:255',
            'description'  => 'sometimes|nullable|string',
            'image_url'    => 'sometimes|nullable|url',
            'prix'         => 'sometimes|required|numeric|min:0',
            'categorie'    => 'sometimes|required|string|max:100',
            'estDisponible'=> 'sometimes|boolean',
        ]);

        $item->update($data);

        return response()->json(['success' => true, 'data' => $item->toApiArray()]);
    }

    /**
     * DELETE /api/menu-items/{id}
     */
    public function destroy(string $id)
    {
        MenuItem::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Plat supprimé.']);
    }
}
