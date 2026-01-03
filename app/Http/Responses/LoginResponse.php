<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\JsonResponse;

class LoginResponse implements LoginResponseContract
{
    /**
     * Créer une réponse HTTP pour une connexion réussie.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = auth()->user();

        // Déterminer l'URL de redirection selon le rôle
        $redirectUrl = match($user->role) {
            'cashier' => route('pos.checkout'),
            'staff' => route('dashboard'),
            'admin' => route('dashboard'),
            default => route('dashboard'),
        };

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 200)
            : redirect()->intended($redirectUrl);
    }
}

