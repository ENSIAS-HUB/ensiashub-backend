<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IotDeviceController;
use App\Http\Controllers\LaundryMachineController;
use App\Http\Controllers\DeviceEventController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderLineController;
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


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// ==================== ROUTES POUR section 1 ====================
Route::apiResource('iot-devices', IotDeviceController::class);
Route::apiResource('laundry-machine',LaundryMachineController::class);
Route::apiResource('device-events',DeviceEventController::class);
Route::apiResource('menu-items', MenuItemController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('order-lines', OrderLineController::class);
Route::get('/auth/redirect/{provider}', [AuthController::class, 'redirect']);
Route::get('/auth/callback/{provider}', [AuthController::class, 'callback']);


// ==================== ROUTES POUR GROUPES ====================
Route::apiResource('groups', GroupController::class);
Route::post('groups/{id}/ajouter-membre', [GroupController::class, 'ajouterMembre']);
Route::post('groups/{id}/valider-membre', [GroupController::class, 'validerMembre']);
Route::post('groups/{id}/bannir-membre', [GroupController::class, 'bannirMembre']);
Route::get('groups/rechercher', [GroupController::class, 'rechercher']);
Route::get('groups/{id}/membres', [GroupController::class, 'getMembres']);
Route::get('groups/{id}/demandes', [GroupController::class, 'getDemandes']);

// ==================== ROUTES POUR ADHESIONS ====================
Route::apiResource('adhesions', AdhesionGroupController::class);
Route::post('adhesions/{id}/approuver', [AdhesionGroupController::class, 'approuver']);
Route::post('adhesions/{id}/rejeter', [AdhesionGroupController::class, 'rejeter']);
Route::post('adhesions/{id}/bannir', [AdhesionGroupController::class, 'bannir']);
Route::put('adhesions/{id}/changer-role', [AdhesionGroupController::class, 'changerRole']);

// ==================== ROUTES POUR PUBLICATIONS ====================
Route::apiResource('publications', PublicationController::class);
Route::post('publications/{id}/publier', [PublicationController::class, 'publier']);

// ==================== ROUTES POUR REVUES DE PUBLICATIONS ====================
Route::apiResource('publication-reviews', PublicationReviewController::class);
Route::post('publication-reviews/{id}/valider', [PublicationReviewController::class, 'valider']);
Route::post('publication-reviews/{id}/rejeter', [PublicationReviewController::class, 'rejeter']);

// ==================== ROUTES POUR INTERACTIONS ====================
Route::apiResource('interactions', InteractionController::class);
Route::apiResource('commentaires', CommentaireController::class);
Route::apiResource('reactions', ReactionController::class);

// ==================== ROUTES POUR LE DRIVE ====================
Route::apiResource('filieres', FiliereController::class);
Route::apiResource('modules', ModuleController::class);
Route::apiResource('documents', DocumentController::class);
Route::apiResource('document-reviews', DocumentReviewController::class);
