<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IotDevice;

class IotDeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $item = IotDevice::all();
        return response()->json($item, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dataValide = $request->validate([
            'idMateriel' => 'required|string|unique:iot_devices,idMateriel',
            'typeCapteur' => 'required|string|in:Contact,Vibration',
            'emplacement' => 'required|string',
            'statutActuel' => 'required|boolean',
        ]);
        $item = IotDevice::create($dataValide);
        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = IotDevice::findOrFail($id);
        return response()->json($item, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = IotDevice::findOrFail($id);
        $donneesValides = $request->validate([
            'idMateriel' => 'sometimes|required|string|unique:iot_devices,idMateriel,' . $id,
            'typeCapteur' => 'sometimes|required|string|in:Contact,Vibration,Temperature',
            'emplacement' => 'sometimes|required|string',
            'statutActuel' => 'sometimes|required|boolean',
        ]);
        $item->update($donneesValides);
        return response()->json($item, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = IotDevice::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Suppression réussie'], 200);
    }
}
