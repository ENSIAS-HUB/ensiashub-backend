<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check pour Render (keep-alive / zero-downtime checks)
Route::get('/health', fn () => response()->json([
    'status'    => 'ok',
    'timestamp' => now()->toISOString(),
]));
use App\Http\Controllers\IotDeviceController;
use App\Http\Controllers\LaundryMachineController;
use App\Http\Controllers\DeviceEventController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\OrderLineController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SavedItemController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\AdhesionGroupController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\PublicationReviewController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\DocumentReviewController;
use App\Http\Controllers\DriveSyncController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FollowController;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Api\DocumentController as DriveDocumentController;
use App\Http\Controllers\Api\FiliereController as DriveFiliereController;
use App\Http\Controllers\Api\DriveController;
use App\Http\Controllers\Api\DriveAdminController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| 🟢 ROUTES PUBLIQUES (Accessibles sans être connecté)
|--------------------------------------------------------------------------
*/
Route::get('/auth/redirect/{provider}', [AuthController::class, 'redirect']);
Route::get('/auth/callback/{provider}', [AuthController::class, 'callback']);
// Limite anti brute-force : 5 tentatives de connexion par minute par IP
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth-login');

/*
|--------------------------------------------------------------------------
| 🔴 ROUTES PROTÉGÉES (Nécessitent un Token Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // ==================== AUTHENTIFICATION & PROFIL ====================
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user()->load('userRoles'))
        ]);
    });

    Route::patch('/me/complete-profile', [ProfileController::class, 'completeProfile']);
    Route::patch('/me', [ProfileController::class, 'update']);
    // Droit à l’oubli (CNDP) : anonymise le compte sans suppression physique
    Route::delete('/me', [ProfileController::class, 'deleteAccount']);

    // ── Profil étendu ───────────────────────────────────────────────
    Route::get('/profile',               [ProfileController::class, 'me']);
    Route::put('/profile',               [ProfileController::class, 'update']);
    Route::post('/profile/avatar',       [ProfileController::class, 'updateAvatar']);
    Route::delete('/profile/avatar',     [ProfileController::class, 'deleteAvatar']);
    Route::post('/profile/cover',        [ProfileController::class, 'updateCover']);
    Route::delete('/profile/cover',      [ProfileController::class, 'deleteCover']);
    Route::put('/profile/password',      [ProfileController::class, 'changePassword']);

    Route::get('/users/{username}/profile',   [ProfileController::class, 'show']);
    Route::get('/users/{username}/projects',  [ProfileController::class, 'projects']);
    Route::get('/users/{username}/activity',  [ProfileController::class, 'activity']);
    Route::get('/users/{username}/followers', [ProfileController::class, 'followers']);
    Route::get('/users/{username}/following', [ProfileController::class, 'following']);

    // ── Projets ─────────────────────────────────────────────────────
    Route::post('/projects',                    [ProjectController::class, 'store']);
    Route::put('/projects/{project}',           [ProjectController::class, 'update']);
    Route::delete('/projects/{project}',        [ProjectController::class, 'destroy']);
    Route::patch('/projects/{project}/feature', [ProjectController::class, 'toggleFeature']);

    // ── Réseau social ───────────────────────────────────────────────
    Route::post('/users/{username}/follow',   [FollowController::class, 'follow']);
    Route::delete('/users/{username}/follow', [FollowController::class, 'unfollow']);
    Route::get('/suggestions',               [FollowController::class, 'suggestions']);

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Déconnecté avec succès']);
    });

    // ==================== ROUTES POUR section 1 ====================
    Route::apiResource('iot-devices', IotDeviceController::class);
    Route::apiResource('laundry-machine', LaundryMachineController::class);
    Route::apiResource('device-events', DeviceEventController::class);

    // Menu items — categories must come before the apiResource to avoid route collision
    Route::get('menu-items/categories', [MenuItemController::class, 'categories']);
    Route::apiResource('menu-items', MenuItemController::class);

    // Orders — cancel must come before apiResource
    Route::patch('orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::apiResource('orders', OrderController::class);

    Route::apiResource('order-lines', OrderLineController::class);

    // ==================== ROUTES CUISINE (cuisinier + superAdmin) ====================
    Route::middleware('ensure.role:cuisinier,superAdmin')->prefix('kitchen')->group(function () {
        Route::get('/orders',                              [KitchenOrderController::class, 'index']);
        Route::get('/orders/{order}',                      [KitchenOrderController::class, 'show']);
        Route::patch('/orders/{order}/status',             [KitchenOrderController::class, 'updateStatus']);
        Route::get('/stats',                               [KitchenOrderController::class, 'stats']);
    });

    // ==================== ROUTES POUR GROUPES ====================
    Route::get('groups/mine', [GroupController::class, 'mine']);          // BEFORE apiResource
    Route::apiResource('groups', GroupController::class);
    Route::post('groups/{id}/ajouter-membre', [GroupController::class, 'ajouterMembre']);
    Route::post('groups/{id}/valider-membre', [GroupController::class, 'validerMembre']);
    Route::get('groups/rechercher', [GroupController::class, 'rechercher']);
    Route::get('groups/{id}/membres', [GroupController::class, 'getMembres']);
    Route::get('groups/{id}/demandes', [GroupController::class, 'getDemandes']);
    Route::get('groups/{groupId}/feed', [PublicationController::class, 'groupFeed'])->middleware('group.member');
    // Club membership workflow
    Route::post('groups/{id}/members/{userId}/approve', [GroupController::class, 'approveMember']);
    Route::post('groups/{id}/members/{userId}/reject',  [GroupController::class, 'rejectMember']);
    Route::delete('groups/{id}/leave',                  [GroupController::class, 'leaveGroup']);

    // ==================== CLUBS — président dashboard ====================
    Route::get('clubs/pending-reviews', [GroupController::class, 'allPendingReviews']);

    // ==================== FEED GLOBAL ====================
    Route::get('feed', [PublicationController::class, 'feed']);

    // ==================== ROUTES POUR ADHESIONS ====================
    Route::apiResource('adhesions', AdhesionGroupController::class);
    // Méthodes manquantes signalées par le scan, commentées pour éviter un crash 500 :
    // Route::post('adhesions/{id}/approuver', [AdhesionGroupController::class, 'approuver']);
    // Route::post('adhesions/{id}/rejeter', [AdhesionGroupController::class, 'rejeter']);
    // Route::post('adhesions/{id}/bannir', [AdhesionGroupController::class, 'bannir']);
    // Route::put('adhesions/{id}/changer-role', [AdhesionGroupController::class, 'changerRole']);

    // ==================== ROUTES POUR PUBLICATIONS ====================
    Route::apiResource('publications', PublicationController::class);
    Route::post('publications/{id}/publier', [PublicationController::class, 'publier']);

    // ==================== ROUTES POUR REVUES DE PUBLICATIONS ====================
    Route::apiResource('publication-reviews', PublicationReviewController::class);
    // Route::post('publication-reviews/{id}/valider', [PublicationReviewController::class, 'valider']); // <-- Commenté (méthode manquante)
    // Route::post('publication-reviews/{id}/rejeter', [PublicationReviewController::class, 'rejeter']); // <-- Commenté (méthode manquante)

    // ==================== ROUTES POUR INTERACTIONS ====================
    Route::apiResource('interactions', InteractionController::class);
    Route::apiResource('commentaires', CommentaireController::class);
    Route::apiResource('reactions', ReactionController::class);

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->group(function () {
        Route::get('/',          [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/stream',    [NotificationController::class, 'stream'])->withoutMiddleware('auth:sanctum')->middleware(['token.query', 'auth:sanctum']);
        Route::post('/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('/{id}/read', [NotificationController::class, 'markRead']);
    });

    // ==================== ROUTES POUR LE DRIVE ====================
    Route::get('/me/drive-access', [DriveController::class, 'myAccess']);

    Route::prefix('drive')->middleware('drive.access')->group(function () {

        // ── Vue étudiant/délégué/président — modules personnalisés ──────────
        Route::get('/mes-modules',      [DriveController::class, 'mesModules']);
        Route::get('/mes-arborescence', [DriveController::class, 'mesArborescence']);

        // ── Vue admin/scolarité — navigation libre ──────────────────────────
        Route::middleware('ensure.role:superAdmin,admin,scolarite,chef_scolarite')->group(function () {
            Route::get('/filieres',                          [DriveController::class, 'filieres']);
            Route::get('/filieres/{filiere}/modules',        [DriveController::class, 'modulesByFiliere']);
            Route::get('/filieres/{filiere}/arborescence',   [DriveController::class, 'arborescence']);
        });

        // ── Commun ──────────────────────────────────────────────────────────
        Route::get   ('/modules/{module}/elements',       [DriveController::class, 'elementsOfModule']);
        Route::get   ('/documents',                       [DriveController::class, 'documents']);
        // Limite uploads : 10 par heure par utilisateur authentifié
        Route::post  ('/documents',                       [DriveController::class, 'upload'])->middleware(['ensure.role:superAdmin,scolarite,chef_scolarite', 'throttle:drive-upload']);
        Route::get   ('/documents/{document}/download',   [DriveController::class, 'download']);
        Route::get   ('/documents/{document}/view',       [DriveController::class, 'view']);
        Route::delete('/documents/{document}',            [DriveController::class, 'deleteDocument'])->middleware('ensure.role:superAdmin,scolarite,chef_scolarite');

        // ── Upload form helpers ──────────────────────────────────────────────
        Route::get('/upload/form-data',                   [DriveController::class, 'uploadFormData'])->middleware('ensure.role:superAdmin,scolarite,chef_scolarite');
        Route::get('/upload/modules',                     [DriveController::class, 'uploadModules'])->middleware('ensure.role:superAdmin,scolarite,chef_scolarite');
    });

    Route::prefix('drive/sync')->group(function () {
        Route::post('/filiere',  [DriveSyncController::class, 'syncFiliere']);
        Route::post('/module',   [DriveSyncController::class, 'syncModule']);
        Route::post('/document', [DriveSyncController::class, 'syncDocument']);
    });

    // ==================== ADMIN DRIVE — CRUD modules, éléments, documents ====================
    Route::prefix('admin/drive')
         ->middleware('ensure.role:superAdmin,chef_scolarite')
         ->group(function () {
             // Modules
             Route::get   ('/modules',               [DriveAdminController::class, 'indexModules']);
             Route::post  ('/modules',               [DriveAdminController::class, 'storeModule']);
             Route::put   ('/modules/{module}',      [DriveAdminController::class, 'updateModule']);
             Route::put   ('/modules/{module}/move', [DriveAdminController::class, 'moveModule']);
             Route::delete('/modules/{module}',      [DriveAdminController::class, 'destroyModule']);

             // Éléments
             Route::post  ('/elements',           [DriveAdminController::class, 'storeElement']);
             Route::put   ('/elements/{element}', [DriveAdminController::class, 'updateElement']);
             Route::delete('/elements/{element}', [DriveAdminController::class, 'destroyElement']);

             // Documents
             Route::put   ('/documents/{document}/rename', [DriveAdminController::class, 'renameDocument']);
             Route::put   ('/documents/{document}/move',   [DriveAdminController::class, 'moveDocument']);
             Route::delete('/documents/{document}',        [DriveAdminController::class, 'destroyDocument']);

             // Sync
             Route::get('/sync-status', [DriveAdminController::class, 'syncStatus']);
         });

    Route::apiResource('filieres', FiliereController::class);
    Route::apiResource('modules', ModuleController::class);
    Route::apiResource('documents', DocumentController::class);
    Route::apiResource('document-reviews', DocumentReviewController::class);

    // ==================== SOCIAL POLYMORPHIQUE ====================
    // Commentaires
    Route::get('/{type}/{id}/comments',          [CommentController::class, 'index']);
    Route::post('/{type}/{id}/comments',         [CommentController::class, 'store']);
    Route::post('/comments/{comment}/reply',     [CommentController::class, 'reply']);
    Route::put('/comments/{comment}',            [CommentController::class, 'update']);
    Route::delete('/comments/{comment}',         [CommentController::class, 'destroy']);

    // Sauvegardes
    Route::get('/saved',                         [SavedItemController::class, 'index']);
    Route::post('/{type}/{id}/save',             [SavedItemController::class, 'save']);
    Route::delete('/{type}/{id}/save',           [SavedItemController::class, 'unsave']);

    // Partages
    Route::post('/{type}/{id}/share',            [ShareController::class, 'share']);

    // Signalements
    Route::post('/{type}/{id}/report',           [ReportController::class, 'report']);

    // Réactions polymorphiques
    Route::post('/{type}/{id}/react',            [ReactionController::class, 'react']);

    // Contraindre {type} aux valeurs valides uniquement
    Route::pattern('type', 'publications|documents|projects|menu-items');

});