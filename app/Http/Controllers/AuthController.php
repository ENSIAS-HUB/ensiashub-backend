<?php

namespace App\Http\Controllers;

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
            $email = $socialUser->getEmail();

            // LA LISTE BLANCHE
            $localUser = User::where('emailInstitutionnel', $email)->first();

            if (!$localUser) {
                // On redirige vers ton interface Next.js avec un paramètre d'erreur
                return redirect('http://localhost:3000/login?error=access_denied');
            }

            $localUser->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'photoProfil' => $localUser->photoProfil ?? $socialUser->getAvatar(),
            ]);

            // -------------------------------------------------------------
            // GÉNÉRATION DU TOKEN POUR NEXT.JS
            // -------------------------------------------------------------
            // Au lieu de Auth::login(), on crée un jeton d'accès sécurisé
            $token = $localUser->createToken('auth_token')->plainTextToken;

            // On redirige vers ton Next.js en lui passant le token dans l'URL
            return redirect('http://localhost:3000/dashboard?token=' . $token);

        } catch (\Exception $e) {
            Log::error("Erreur SSO {$provider} : " . $e->getMessage());
            return redirect('http://localhost:3000/login?error=server_error');
        }
    }
}