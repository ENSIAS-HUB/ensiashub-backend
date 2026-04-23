<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaundryMachine;

class LaundryMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $item = LaundryMachine::all();
        return response()->json($item, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $itemValide = $request->validate([
            'numeroMachine'=>'required|string|unique:laundry_machines,numeroMachine',
            'type'=>'required|string|in:LaveLinge,SecheLinge',
            'status'=>'required|string|in:Libre,Occupee,HorsService'
        ]);
        $item = LaundryMachine::create($itemValide);
        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = LaundryMachine::findOrFail($id);
        return response()->json($item, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = LaundryMachine::findOrFail($id);
        $itemValide = $request->validate([
            'numeroMachine'=>'sometimes|required|string|unique:laundry_machines,numeroMachine,' . $id,
            'type'=>'sometimes|required|string|in:LaveLinge,SecheLinge',
            'status'=>'sometimes|required|string|in:Libre,Occupee,HorsService'
        ]);
        $item->update($itemValide);
        return response()->json($item, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = LaundryMachine::findOrFail($id);
        $item->delete();
        return response()->json(['message'=>'Machine à laver supprimée avec succès'], 200);
    }
}
