<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin a toujours accès
        if ($user->role_name === 'admin' || $user->role === 'admin') {
            return $next($request);
        }

        // Vérifier la permission
        if (!$user->hasPermission($permission)) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
