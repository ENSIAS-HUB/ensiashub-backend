<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;



class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Order::all();
        return response()->json($items, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dataValide = $request->validate([
            'numeroCommande'=>'required|string',
            'statut'=>'required|in:EnAttente,EnPreparation,Prete,Recuperee,Annulee',
            'tempsAttenteEstime'=>'nullable|integer',
        ]);
        $item = Order::create($dataValide);
        return response()->json($item, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Order::findOrFail($id);
        return response()->json($item, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Order::findOrFail($id);
        $donneesValides = $request->validate([
            'numeroCommande'=>'unique:orders,numeroCommande|required|string',
            'statut'=>'sometimes|required|in:EnAttente,EnPreparation,Prete,Recuperee,Annulee',
            'tempsAttenteEstime'=>'sometimes|nullable|integer',
        ]);
        $item->update($donneesValides);
        return response()->json($item, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Order::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
