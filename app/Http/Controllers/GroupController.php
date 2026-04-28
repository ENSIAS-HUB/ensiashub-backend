<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    /**
     * Afficher la liste des groupes
     * GET /api/groups
     */
    public function index(Request $request)
    {
        $query = Group::query();
        
        if ($request->has('categorie')) {
            $query->where('categorie', $request->categorie);
        }
        
        if ($request->has('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }
        
        $groups = $query->withCount('membres')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $groups
        ]);
    }

    /**
     * Créer un nouveau groupe
     * POST /api/groups
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'categorie' => 'required|in:Filiere,Club,General',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $group = Group::create([
            'nom' => $request->nom,
            'categorie' => $request->categorie,
            'description' => $request->description,
        ]);

        // L'utilisateur qui crée devient modérateur
        $group->membres()->attach(Auth::id(), [
            'statut' => 'Approuve',
            'role' => 'Moderateur',
            'joinedAt' => now(),
            'reviewedAt' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Groupe créé avec succès',
            'data' => $group
        ], 201);
    }

    /**
     * Afficher un groupe spécifique
     * GET /api/groups/{id}
     */
    public function show(string $id)
    {
        $group = Group::with(['membres', 'publications.user'])->find($id);
        
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $group
        ]);
    }

    /**
     * Mettre à jour un groupe
     * PUT/PATCH /api/groups/{id}
     */
    public function update(Request $request, string $id)
    {
        $group = Group::find($id);
        
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe non trouvé'
            ], 404);
        }

        // Vérifier si l'utilisateur est modérateur
        if (!$group->estModerateur(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:255',
            'categorie' => 'sometimes|in:Filiere,Club,General',
            'description' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $group->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Groupe mis à jour',
            'data' => $group
        ]);
    }

    /**
     * Supprimer un groupe
     * DELETE /api/groups/{id}
     */
    public function destroy(string $id)
    {
        $group = Group::find($id);
        
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe non trouvé'
            ], 404);
        }

        if (!$group->estModerateur(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Groupe supprimé avec succès'
        ]);
    }

    /**
     * Ajouter un membre au groupe
     * POST /api/groups/{id}/add-member
     */
    public function ajouterMembre(Request $request, string $id)
    {
        $group = Group::find($id);
        
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        
        // Vérifier si déjà membre
        if ($group->estMembre($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Cet utilisateur est déjà membre'
            ], 400);
        }

        $group->ajouterMembre($user);

        return response()->json([
            'success' => true,
            'message' => 'Demande d\'adhésion envoyée'
        ]);
    }

    /**
     * Valider un membre
     * POST /api/groups/{id}/validate-member
     */
    public function validerMembre(Request $request, string $id)
    {
        $group = Group::find($id);
        
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe non trouvé'
            ], 404);
        }

        if (!$group->estModerateur(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        $result = $group->validerMembre($user);

        return response()->json($result);
    }

    /**
     * Rechercher des groupes
     * GET /api/groups/search?q=nom
     */
    public function rechercher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $groups = Group::rechercher($request->q);

        return response()->json([
            'success' => true,
            'data' => $groups
        ]);
    }

    /**
     * Obtenir les membres du groupe
     * GET /api/groups/{id}/members
     */
    public function getMembres(string $id)
    {
        $group = Group::find($id);
        
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe non trouvé'
            ], 404);
        }

        $membres = $group->getMembresApprouves();

        return response()->json([
            'success' => true,
            'data' => $membres
        ]);
    }

    /**
     * Obtenir les demandes en attente
     * GET /api/groups/{id}/requests
     */
    public function getDemandes(string $id)
    {
        $group = Group::find($id);
        
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe non trouvé'
            ], 404);
        }

        if (!$group->estModerateur(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $demandes = $group->getDemandesEnAttente();

        return response()->json([
            'success' => true,
            'data' => $demandes
        ]);
    }
}