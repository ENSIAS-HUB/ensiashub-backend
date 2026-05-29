<?php

namespace App\Http\Controllers;

use App\Models\AdhesionGroup;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdhesionGroupController extends Controller
{
    /**
     * Afficher la liste des adhésions
     * GET /api/adhesions
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Seul admin/superAdmin peut voir toutes les adhésions
        if (!$user->isAdminOrAbove()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        $ressources = AdhesionGroup::with(['user', 'group'])->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $ressources
        ]);
    }

    /**
     * Créer une nouvelle adhésion (demande)
     * POST /api/adhesions
     *
     * - Groupes Filière  : accès direct automatique (Approuve) — normalement géré à la connexion.
     *   Les étudiants ne peuvent rejoindre que leur propre groupe de filière.
     * - Groupes Club     : demande en attente (EnAttente), nécessite validation admin.
     * - Groupes General  : accès direct (Approuve).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',  // ← table correcte
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $group = Group::findOrFail($request->group_id);

        // Empêcher de rejoindre manuellement un groupe de filière
        if ($group->categorie === 'Filiere') {
            return response()->json([
                'success' => false,
                'message' => 'L\'adhésion aux groupes de filière est automatique. Contactez un administrateur pour modifier votre filière.',
            ], 403);
        }

        // Vérifier si l'utilisateur a déjà une demande
        $existant = AdhesionGroup::where('user_id', Auth::id())
                                  ->where('group_id', $request->group_id)
                                  ->first();
        
        if ($existant) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà une demande pour ce groupe',
            ], 400);
        }

        // Clubs → EnAttente (approbation requise) ; Général → Approuve direct
        $statut = $group->categorie === 'Club' ? 'EnAttente' : 'Approuve';

        $ressource = AdhesionGroup::create([
            'user_id'     => Auth::id(),
            'group_id'    => $request->group_id,
            'statut'      => $statut,
            'role'        => 'Membre',
            'joinedAt'    => now(),
            'reviewedAt'  => $statut === 'Approuve' ? now() : null,
        ]);

        $message = $statut === 'EnAttente'
            ? 'Demande d\'adhésion envoyée avec succès, en attente de validation.'
            : 'Adhésion effectuée avec succès.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $ressource,
        ], 201);
    }
}
