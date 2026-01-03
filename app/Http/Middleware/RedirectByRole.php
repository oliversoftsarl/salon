<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    /**
     * Redirige l'utilisateur vers la page appropriée selon son rôle.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Si l'utilisateur est un caissier et essaie d'accéder au dashboard
        if ($user->role === 'cashier' && $request->routeIs('dashboard')) {
            return redirect()->route('pos.checkout');
        }

        return $next($request);
    }
}

