<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderLine;


class OrderLineController extends Controller
{
    public function index()
    {
        $items = OrderLine::all();
        return response()->json($items, 200); 
    }

    public function store(Request $request)
    {
        $dataValide = $request->validate([
            'order_id'     => 'required|exists:orders,id', 
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantite'     => 'required|integer|min:1',    
            'prixUnitaire' => 'required|numeric|min:0',
        ]);

        $dataValide['totalLigne'] = $dataValide['quantite'] * $dataValide['prixUnitaire'];

        $item = OrderLine::create($dataValide);
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = OrderLine::findOrFail($id);
        return response()->json($item, 200);
    }

    public function update(Request $request, string $id)
    {
        $item = OrderLine::findOrFail($id);
        
        $dataValide = $request->validate([
            'order_id'     => 'sometimes|required|exists:orders,id',
            'menu_item_id' => 'sometimes|required|exists:menu_items,id',
            'quantite'     => 'sometimes|required|integer|min:1',
            'prixUnitaire' => 'sometimes|required|numeric|min:0',
        ]);

        $nouvelleQuantite = $dataValide['quantite'] ?? $item->quantite;
        $nouveauPrix = $dataValide['prixUnitaire'] ?? $item->prixUnitaire;
        
        $dataValide['totalLigne'] = $nouvelleQuantite * $nouveauPrix;

        $item->update($dataValide);
        return response()->json($item, 200);
    }

    public function destroy(string $id)
    {
        $item = OrderLine::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}