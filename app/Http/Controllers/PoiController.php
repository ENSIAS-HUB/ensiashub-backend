<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poi;

class PoiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Poi::all();
        return response()->json($items, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dataValide = $request->validate([
            'nomLieu' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categorie' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $poi = Poi::create($dataValide);
        return response()->json($poi, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $poi = Poi::findOrFail($id);
        return response()->json($poi, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $poi = Poi::findOrFail($id);

        $dataValide = $request->validate([
            'nomLieu' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'categorie' => 'sometimes|required|string|max:255',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
        ]);

        $poi->update($dataValide);
        return response()->json($poi, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $poi = Poi::findOrFail($id);
        $poi->delete();
        return response()->json(null, 204);
    }
}
