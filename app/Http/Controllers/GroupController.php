<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\AdhesionGroup;
use App\Http\Resources\GroupResource;
use App\Http\Resources\UserResource;
use App\Notifications\ClubMembershipRequested;
use App\Notifications\ClubMembershipReviewed;
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
        $user    = Auth::user();
        $userId  = $user->id;

        $groups = Group::query()
            ->withCount('membres')
            // Eager-load only the current user's membership row (avoids N+1)
            ->with(['membres' => fn ($q) => $q->where('adhesion_groups.user_id', $userId)])
            ->when($request->has('categorie'), fn ($q) => $q->where('categorie', $request->categorie))
            ->when($request->has('search'),    fn ($q) => $q->where('nom', 'like', '%' . $request->search . '%'))
            // Admin & superAdmin voient tout, les autres voient leur filière + clubs + général
            ->when(!$user->isAdminOrAbove(), function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    // Non-filière groups are always visible
                    $sub->where('categorie', '!=', 'Filiere');
                    if ($user->filiere) {
                        // Match on new filiere_key + annee_filiere columns (precise)
                        $sub->orWhere(function ($inner) use ($user) {
                            $inner->where('categorie', 'Filiere')
                                  ->where('filiere_key', $user->filiere);
                            if ($user->annee) {
                                $inner->where('annee_filiere', $user->annee);
                            } else {
                                $inner->whereNull('annee_filiere');
                            }
                        });
                        // Fallback: old groups without filiere_key — only include if no new-style
                        // group already exists for the same filiere/annee (prevents duplicates)
                        if ($user->annee) {
                            $sub->orWhere(function ($inner) use ($user) {
                                $inner->where('categorie', 'Filiere')
                                      ->whereNull('filiere_key')
                                      ->where('nom', $user->filiere . ' ' . $user->annee)
                                      ->whereNotExists(function ($q) use ($user) {
                                          $q->from('groups')
                                            ->where('categorie', 'Filiere')
                                            ->where('filiere_key', $user->filiere)
                                            ->where('annee_filiere', $user->annee);
                                      });
                            });
                        }
                    }
                });
            })
            ->distinct()
            ->paginate(100);

        return response()->json(
            $groups->through(fn ($g) => (new GroupResource($g))->toArray($request))
        );
    }

    /**
     * Créer un nouveau groupe
     * POST /api/groups
     */
    public function store(Request $request)
    {
        // 1. On retire le createur_id du validateur (le frontend n'a pas à l'envoyer)
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

        // 2. On injecte directement l'ID de l'utilisateur authentifié
        $group = Group::create([
            'createur_id' => Auth::id(), // <-- L'ajout crucial est ici !
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
        $group = Group::withCount('membres')
            ->with('membres') // load all members so GroupResource can find the moderator
            ->find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => (new GroupResource($group))->toArray(request()),
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
        $user = Auth::user();
        if (!$user->isAdminOrAbove() && !$group->estModerateur($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé — vous devez être admin ou modérateur de ce groupe.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nom'         => 'sometimes|string|max:255',
            'categorie'   => 'sometimes|in:Filiere,Club,General',
            'description' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $group->update($request->only(['nom', 'categorie', 'description']));

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

        $user = Auth::user();
        if (!$user->isAdminOrAbove() && !$group->estModerateur($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé — réservé aux admins et modérateurs.',
            ], 403);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Groupe supprimé avec succès'
        ]);
    }

    /**
     * Rejoindre un groupe (utilisateur connecté)
     * POST /api/groups/{id}/ajouter-membre
     */
    public function ajouterMembre(Request $request, string $id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe non trouvé',
            ], 404);
        }

        // Les filières ne peuvent pas être rejointes manuellement
        if ($group->categorie === 'Filiere') {
            return response()->json([
                'success' => false,
                'message' => 'L\'adhésion aux groupes de filière est automatique.',
            ], 403);
        }

        $userId = Auth::id();

        // Vérifier si déjà membre / demande existante
        $existant = \App\Models\AdhesionGroup::where('user_id', $userId)
            ->where('group_id', $id)
            ->first();

        if ($existant) {
            $msg = $existant->statut === 'EnAttente'
                ? 'Demande d\'adhésion déjà en cours.'
                : 'Vous êtes déjà membre ou votre demande a été traitée.';
            return response()->json(['success' => false, 'message' => $msg], 409);
        }

        // Club → EnAttente, Général → Approuve
        $statut = $group->categorie === 'Club' ? 'EnAttente' : 'Approuve';

        \App\Models\AdhesionGroup::create([
            'user_id'  => $userId,
            'group_id' => $id,
            'statut'   => $statut,
            'role'     => 'Membre',
            'joinedAt' => now(),
        ]);

        // Notify the club's moderators when a new membership request arrives
        if ($statut === 'EnAttente') {
            $requester = Auth::user();
            $moderateurs = $group->membres()
                ->wherePivot('role', 'Modérateur')
                ->wherePivot('statut', 'Approuve')
                ->get();

            foreach ($moderateurs as $mod) {
                try { $mod->notify(new ClubMembershipRequested($requester, $group)); }
                catch (\Throwable) { /* notification non-critique */ }
            }
        }

        $message = $statut === 'EnAttente'
            ? 'Demande d\'adhésion envoyée, en attente d\'approbation.'
            : 'Vous avez rejoint le groupe.';

        return response()->json(['success' => true, 'message' => $message]);
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

        $auth = Auth::user();
        // Autorisé si : superAdmin / admin, OU modérateur du groupe (délégué / président club)
        if (!$auth->isAdminOrAbove() && !$group->estModerateur($auth)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé — réservé aux admins et modérateurs du groupe.',
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

        $user   = User::find($request->user_id);
        $result = $group->validerMembre($user);

        if ($result['success']) {
            // Notify the student
            try { $user->notify(new ClubMembershipReviewed($group, 'approved')); }
            catch (\Throwable) {}
            // Track who reviewed
            AdhesionGroup::where('user_id', $user->id)
                ->where('group_id', $id)
                ->update(['reviewed_by' => Auth::id()]);
        }

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
            'data'    => UserResource::collection($membres)->toArray(request()),
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

        if (!$group->estModerateur(Auth::user()) && !Auth::user()->isAdminOrAbove()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $demandes = $group->getDemandesEnAttente();

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($demandes)->toArray(request()),
        ]);
    }

    /**
     * GET /api/groups/mine — Return the authenticated user's filière group and clubs
     */
    public function mine()
    {
        $userId = Auth::id();

        $approvedGroups = Group::whereHas('membres', fn ($q) =>
                $q->where('user_id', $userId)->where('statut', 'Approuve')
            )
            ->withCount('membres')
            ->with(['membres' => fn ($q) => $q->where('user_id', $userId)])
            ->get();

        $filiereGroup = $approvedGroups->firstWhere('categorie', 'Filiere');
        $clubs        = $approvedGroups->where('categorie', 'Club')->values();

        $toResource = fn ($g) => (new GroupResource($g))->toArray(request());

        return response()->json([
            'success' => true,
            'data'    => [
                'filiere_group' => $filiereGroup ? $toResource($filiereGroup) : null,
                'clubs'         => $clubs->map($toResource)->values(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // CLUB MEMBERSHIP WORKFLOW
    // ────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/groups/{id}/members/{userId}/approve
     * Approuver la demande d'adhésion d'un étudiant.
     * Accessible : modérateur du club + superAdmin
     */
    public function approveMember(Request $request, string $id, string $userId)
    {
        $group = Group::find($id);
        if (!$group) return response()->json(['success' => false, 'message' => 'Groupe non trouvé'], 404);

        $auth = Auth::user();
        if (!$auth->canManageClub($id)) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $adhesion = AdhesionGroup::where('group_id', $id)
            ->where('user_id', $userId)
            ->where('statut', 'EnAttente')
            ->first();

        if (!$adhesion) {
            return response()->json(['success' => false, 'message' => 'Demande introuvable ou déjà traitée'], 404);
        }

        $adhesion->update([
            'statut'      => 'Approuve',
            'reviewedAt'  => now(),
            'reviewed_by' => $auth->id,
        ]);

        // Notify the student
        $student = User::find($userId);
        if ($student) {
            try { $student->notify(new ClubMembershipReviewed($group, 'approved')); }
            catch (\Throwable) {}
        }

        return response()->json(['success' => true, 'message' => 'Membre approuvé avec succès.']);
    }

    /**
     * POST /api/groups/{id}/members/{userId}/reject
     * Refuser la demande d'adhésion d'un étudiant.
     * Accessible : modérateur du club + superAdmin
     */
    public function rejectMember(Request $request, string $id, string $userId)
    {
        $group = Group::find($id);
        if (!$group) return response()->json(['success' => false, 'message' => 'Groupe non trouvé'], 404);

        $auth = Auth::user();
        if (!$auth->canManageClub($id)) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $adhesion = AdhesionGroup::where('group_id', $id)
            ->where('user_id', $userId)
            ->where('statut', 'EnAttente')
            ->first();

        if (!$adhesion) {
            return response()->json(['success' => false, 'message' => 'Demande introuvable ou déjà traitée'], 404);
        }

        $reason = $request->input('reason');

        $adhesion->update([
            'statut'        => 'Rejete',
            'reviewedAt'    => now(),
            'reviewed_by'   => $auth->id,
            'motifDecision' => $reason,
        ]);

        // Notify the student
        $student = User::find($userId);
        if ($student) {
            try { $student->notify(new ClubMembershipReviewed($group, 'rejected', $reason)); }
            catch (\Throwable) {}
        }

        return response()->json(['success' => true, 'message' => 'Demande refusée.']);
    }

    /**
     * DELETE /api/groups/{id}/leave
     * L'utilisateur connecté quitte un club (ne fonctionne pas pour les filières auto-assignées).
     */
    public function leaveGroup(string $id)
    {
        $group = Group::find($id);
        if (!$group) return response()->json(['success' => false, 'message' => 'Groupe non trouvé'], 404);

        $adhesion = AdhesionGroup::where('group_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$adhesion) {
            return response()->json(['success' => false, 'message' => 'Vous n\'appartenez pas à ce groupe'], 404);
        }

        if ($adhesion->auto_assigned) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de quitter un groupe de filière assigné automatiquement.',
            ], 403);
        }

        $adhesion->delete();

        return response()->json(['success' => true, 'message' => 'Vous avez quitté le groupe.']);
    }

    /**
     * GET /api/clubs/pending-reviews
     * Toutes les demandes en attente pour les clubs que l'utilisateur modère.
     * Regroupées par club.
     */
    public function allPendingReviews()
    {
        $user   = Auth::user();
        $userId = $user->id;

        $query = AdhesionGroup::with(['user', 'group'])
            ->where('statut', 'EnAttente')
            ->whereHas('group', fn ($q) => $q->where('categorie', 'Club'));

        if (!$user->isAdminOrAbove()) {
            // Only clubs where the current user is Modérateur
            $query->whereHas('group', fn ($q) =>
                $q->whereHas('membres', fn ($mq) =>
                    $mq->where('adhesion_groups.user_id', $userId)
                        ->where('adhesion_groups.role', 'Modérateur')
                        ->where('adhesion_groups.statut', 'Approuve')
                )
            );
        }

        $pending = $query->orderBy('created_at', 'asc')->get();

        $grouped = $pending
            ->groupBy('group_id')
            ->map(function ($items) {
                $group = $items->first()->group;
                return [
                    'group'    => (new GroupResource($group))->toArray(request()),
                    'requests' => $items->map(fn ($a) => [
                        'id'           => $a->id,
                        'user'         => (new UserResource($a->user))->toArray(request()),
                        'requested_at' => $a->joinedAt,
                    ])->values(),
                ];
            })
            ->values();

        return response()->json(['success' => true, 'data' => $grouped]);
    }
}