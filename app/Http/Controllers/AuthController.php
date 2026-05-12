<?php

namespace App\Http\Controllers;

use App\Models\AdhesionGroup;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function redirect($provider)
    {
        if (!in_array($provider, ['google', 'microsoft'])) {
            return response()->json(['error' => 'Fournisseur non supporté.'], 400);
        }

        // stateless() est OBLIGATOIRE pour une API React/Next.js
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback($provider)
    {
        try {
            // stateless() ici aussi
            $socialUser = Socialite::driver($provider)->stateless()->user();
            $email = strtolower(trim($socialUser->getEmail()));

            // LA LISTE BLANCHE (comparaison insensible à la casse)
            $localUser = User::whereRaw('LOWER("emailInstitutionnel") = ?', [$email])->first();

            if (!$localUser) {
                // On redirige vers ton interface Next.js avec un paramètre d'erreur
                return redirect('http://localhost:3000/login?error=access_denied');
            }

            $localUser->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'photoProfil' => $localUser->photoProfil ?? $socialUser->getAvatar(),
            ]);

            // Génération du token (nécessaire dans les deux cas de redirection)
            $token = $localUser->createToken('auth_token')->plainTextToken;

            // ── Profil incomplet → redirection vers la page de complétion ──
            if (!$localUser->filiere || !$localUser->annee) {
                $userJson = urlencode(json_encode([
                    'id'      => $localUser->id,
                    'name'    => trim(($localUser->prenom ?? '') . ' ' . ($localUser->nom ?? '')),
                    'email'   => $localUser->emailInstitutionnel,
                    'avatar'  => $localUser->photoProfil,
                    'role'    => is_array($localUser->roles) ? ($localUser->roles[0] ?? 'etudiant') : ($localUser->roles ?? 'etudiant'),
                    'filiere' => null,
                    'annee'   => null,
                ]));
                return redirect('http://localhost:3000/complete-profile?token=' . $token . '&user=' . $userJson);
            }

            // ── Profil complet → inscription filière + dashboard ──────────
            $this->autoEnrollFiliere($localUser);

            $userJson = urlencode(json_encode([
                'id'      => $localUser->id,
                'name'    => trim(($localUser->prenom ?? '') . ' ' . ($localUser->nom ?? '')),
                'email'   => $localUser->emailInstitutionnel,
                'avatar'  => $localUser->photoProfil,
                'role'    => is_array($localUser->roles) ? ($localUser->roles[0] ?? 'etudiant') : ($localUser->roles ?? 'etudiant'),
                'filiere' => $localUser->filiere,
                'annee'   => $localUser->annee,
            ]));
            return redirect('http://localhost:3000/callback?token=' . $token . '&user=' . $userJson);

        } catch (\Exception $e) {
            Log::error("Erreur SSO {$provider} : " . $e->getMessage());
            return redirect('http://localhost:3000/login?error=server_error');
        }
    }

    /**
     * Enrôle automatiquement l'utilisateur dans son groupe de filière
     * (statut Approuve, sans demande manuelle).
     */
    private function autoEnrollFiliere(User $user): void
    {
        if (!$user->filiere || !$user->annee) {
            return;
        }

        $groupNom = $user->filiere . ' ' . $user->annee; // ex: "GL 1A"
        $group    = Group::where('nom', $groupNom)->where('categorie', 'Filiere')->first();

        if (!$group) {
            return;
        }

        // firstOrCreate évite les doublons (contrainte unique user_id + group_id)
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
}