<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\AdhesionGroup;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Mettre à jour le profil (bio uniquement — email/nom viennent du SSO).
     * PATCH /api/me
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bio' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $user->update($request->only('bio'));

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour.',
            'data'    => new UserResource($user),
        ]);
    }

    /**
     * Compléter le profil de l'étudiant (filière + année d'étude).
     * PATCH /api/me/complete-profile
     *
     * Appelé depuis la page /complete-profile du frontend.
     * Accessible uniquement si l'utilisateur est authentifié (token Sanctum).
     */
    public function completeProfile(Request $request)
    {
        $filieres = ['GL', 'GD', 'D2S', '2IA', '2SCL', 'SSE', 'CSCMC', 'IDF', 'BI&A'];
        $annees   = ['1A', '2A', '3A'];

        $validator = Validator::make($request->all(), [
            'filiere' => ['required', 'string', 'in:' . implode(',', $filieres)],
            'annee'   => ['required', 'string', 'in:' . implode(',', $annees)],
        ], [
            'filiere.in' => 'Filière non reconnue. Valeurs acceptées : ' . implode(', ', $filieres),
            'annee.in'   => 'Année non reconnue. Valeurs acceptées : ' . implode(', ', $annees),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Ne pas écraser si déjà rempli (sécurité anti-replay)
        if ($user->filiere && $user->annee) {
            return response()->json([
                'success' => false,
                'message' => 'Profil déjà complété.',
            ], 409);
        }

        $user->update([
            'filiere' => $request->filiere,
            'annee'   => $request->annee,
        ]);

        // Auto-inscription dans le groupe de filière correspondant
        $groupNom = $user->filiere . ' ' . $user->annee; // ex: "GL 1A"
        $group    = Group::where('nom', $groupNom)->where('categorie', 'Filiere')->first();

        if ($group) {
            AdhesionGroup::firstOrCreate(
                ['user_id' => $user->id, 'group_id' => $group->id],
                [
                    'statut'     => 'Approuve',
                    'role'       => 'Membre',
                    'joinedAt'   => now(),
                    'reviewedAt' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil complété avec succès.',
            'data'    => [
                'filiere' => $user->filiere,
                'annee'   => $user->annee,
            ],
        ]);
    }
}
