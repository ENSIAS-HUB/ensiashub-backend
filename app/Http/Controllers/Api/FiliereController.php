<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FiliereController extends Controller
{
    // GET /api/drive/filieres
    public function index(): JsonResponse
    {
        $filieres = Filiere::where('is_active', true)
            ->withCount('documents')
            ->with(['modules' => function ($q) {
                $q->where('is_active', true)
                  ->withCount('documents')
                  ->orderBy('semestre')
                  ->orderBy('nom');
            }])
            ->orderBy('nom')
            ->get();

        return response()->json(['filieres' => $filieres]);
    }

    // GET /api/drive/filieres/{filiere}/modules
    public function modules(Request $request, Filiere $filiere): JsonResponse
    {
        $modules = Module::where('filiere_id', $filiere->id)
            ->where('is_active', true)
            ->withCount('documents')
            ->orderBy('semestre')
            ->orderBy('nom')
            ->get();

        return response()->json(['modules' => $modules]);
    }
}
