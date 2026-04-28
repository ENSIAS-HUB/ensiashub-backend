<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = MenuItem::all();
        return response()->json($items);
    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dataValide = $request->validate([
            'nomPlat' => 'required|string',
            'prix' => 'required|numeric',
            'categorie' => 'required|string',
            'estDisponible' => 'boolean',
         ]);
         
         $item = MenuItem::create($dataValide);
            return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = MenuItem::findOrFail($id);
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = MenuItem::findOrFail($id);
        
        $dataValide = $request->validate([
            'nomPlat' => 'sometimes|required|string',
            'prix' => 'sometimes|required|numeric',
            'categorie' => 'sometimes|required|string',
            'estDisponible' => 'sometimes|required|boolean',
        ]);

        $item->update($dataValide);
        return response()->json($item, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = MenuItem::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Élément du menu supprimé avec succès'], 200);
    }
}
