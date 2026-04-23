<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceEvent;
use Carbon\Carbon; // Pour gérer le temps facilement

class DeviceEventController extends Controller
{
    public function index()
    {
        // On peut charger la relation pour voir quel capteur a fait quoi
        $items = DeviceEvent::with('iotDevice')->get();
        return response()->json($items, 200);
    }

    public function store(Request $request)
    {
        $dataValide = $request->validate([
            'iot_device_id' => 'required|uuid|exists:iot_devices,id', // On vérifie que le capteur existe !
            'valeurBrute'   => 'required|string',
            'statutDerive'  => 'required|string',
            'timestamp'     => 'sometimes|date', // Optionnel, sinon on prend l'heure actuelle
        ]);

        if (!isset($dataValide['timestamp'])) {
            $dataValide['timestamp'] = Carbon::now();
        }

        $item = DeviceEvent::create($dataValide);
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = DeviceEvent::with('iotDevice')->findOrFail($id);
        return response()->json($item, 200);
    }

    public function update(Request $request, string $id)
    {
        $item = DeviceEvent::findOrFail($id);
        
        $donneesValides = $request->validate([
            'valeurBrute'  => 'sometimes|required|string',
            'statutDerive' => 'sometimes|required|string',
        ]);

        $item->update($donneesValides);
        return response()->json($item, 200);
    }

    public function destroy(string $id)
    {
        $item = DeviceEvent::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Événement supprimé avec succès'], 200);
    }
}