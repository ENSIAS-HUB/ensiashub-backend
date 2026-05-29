<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AutoGroupAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function redirect($provider)
    {
        if (!in_array($provider, ['google', 'microsoft'])) {
            return response()->json(['error' => 'Fournisseur non supporté.'], 422);
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email = strtolower(trim($request->email));
        $user  = User::whereRaw('LOWER("emailInstitutionnel") = ?', [$email])->first();

        if (!$user || !$user->password || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'      => $user->id,
                'name'    => trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')),
                'email'   => $user->emailInstitutionnel,
                'avatar'  => $user->photoProfil,
                'role'    => is_array($user->roles) ? ($user->roles[0] ?? 'etudiant') : ($user->roles ?? 'etudiant'),
                'filiere' => $user->filiere,
                'annee'   => $user->annee,
            ],
        ]);
    }

    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            if (!$socialUser || !$socialUser->getEmail() || !$socialUser->getId()) {
                Log::warning("Socialite returned incomplete user from {$provider}");
                return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?error=oauth_failed');
            }

            $email = strtolower(trim($socialUser->getEmail()));

            // Microsoft = comptes universitaires uniquement (@um5.ac.ma).
            // Google   = ouvert (comptes personnels @gmail.com, etc.).
            if ($provider === 'microsoft' && !str_ends_with($email, '@um5.ac.ma')) {
                return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?error=invalid_domain');
            }

            $localUser = User::whereRaw('LOWER("emailInstitutionnel") = ?', [$email])->first();

            if (!$localUser) {
                return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?error=access_denied');
            }

            $localUser->update([
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
                'photoProfil' => $localUser->photoProfil ?? $socialUser->getAvatar(),
            ]);

            $token = $localUser->createToken('auth_token')->plainTextToken;

            if (!$localUser->filiere || !$localUser->annee) {
                return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/complete-profile?token=' . $token);
            }

            // Auto-assign filière group
            app(AutoGroupAssignmentService::class)->assignUserToFiliereGroup($localUser);

            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/callback?token=' . $token);

        } catch (\Exception $e) {
            Log::error("Erreur SSO {$provider} : " . $e->getMessage());
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?error=server_error');
        }
    }
}