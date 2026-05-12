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

            return response()->json([
                'success' => false,
                'message' => 'Adhésion non trouvée'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $ressource
        ]);
    }

    /**
     * Mettre à jour une adhésion
     * PUT/PATCH /api/adhesions/{id}
     */
    public function update(Request $request, string $id)
    {
        $ressource = AdhesionGroup::find($id);
        
        if (!$ressource) {
            return response()->json([
                'success' => false,
                'message' => 'Ressource non trouvée'
            ], 404);
        }
        
        // Vérifier si l'utilisateur est modérateur du groupe
        $group = $ressource->group;
        if (!$group->estModerateur(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé à modifier cette adhésion'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'statut' => 'sometimes|in:EnAttente,Approuve,Rejete,Banni',
            'role' => 'sometimes|in:Membre,Moderateur',
            'motifDecision' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $updateData = [];
        
        if ($request->has('statut')) {
            $updateData['statut'] = $request->statut;
            $updateData['reviewedAt'] = now();
        }
        
        if ($request->has('role')) {
            $updateData['role'] = $request->role;
        }
        
        if ($request->has('motifDecision')) {
            $updateData['motifDecision'] = $request->motifDecision;
        }
        
        $ressource->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => 'Adhésion mise à jour',
            'data' => $ressource
        ]);
    }

    /**
     * Supprimer une adhésion (quitter le groupe)
     * DELETE /api/adhesions/{id}
     */
    public function destroy(string $id)
    {
        $ressource = AdhesionGroup::find($id);
        
        if (!$ressource) {
            return response()->json([
                'success' => false,
                'message' => 'Adhésion non trouvée'
            ], 404);
        }
        
        // Vérifier si l'utilisateur peut supprimer (lui-même ou modérateur)
        $user = Auth::user();
        $group = $ressource->group;
        
        if ($user->id !== $ressource->user_id && !$group->estModerateur($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé à supprimer cette adhésion'
            ], 403);
        }
        
        $ressource->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Adhésion supprimée avec succès'
        ]);
    }
}