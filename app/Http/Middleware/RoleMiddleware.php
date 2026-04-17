<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Vérifier par le nom du rôle (ancien système + nouveau)
        if (method_exists($user, 'hasRole') && $user->hasRole($roles)) {
            return $next($request);
        }

        // Pour les rôles customs : vérifier si le rôle a la permission correspondant à la route
        if ($user->roleModel) {
            $routeName = $request->route()?->getName();
            if ($routeName && $user->hasPermission($this->routeToPermission($routeName))) {
                return $next($request);
            }
        }

        abort(403, 'Accès refusé.');
    }

    /**
     * Convertir un nom de route en nom de permission
     */
    protected function routeToPermission(string $routeName): string
    {
        // Mapping route -> permission
        $map = [
            'dashboard' => 'dashboard',
            'pos.checkout' => 'pos',
            'pos.transactions' => 'pos.transactions',
            'clients.index' => 'clients',
            'appointments.calendar' => 'appointments',
            'services.index' => 'services',
            'services.print' => 'services',
            'products.index' => 'products',
            'staff.schedule' => 'staff.schedule',
            'staff.performance' => 'staff.performance',
            'staff.debts' => 'staff.debts',
            'cash.register' => 'cash',
            'users.index' => 'users',
            'settings.exchange-rates' => 'settings.exchange-rates',
            'settings.revenue' => 'settings.revenue',
            'settings.roles' => 'roles',
            'payroll.index' => 'payroll',
            'inventory.supplies' => 'inventory.supplies',
            'inventory.consumptions' => 'inventory.consumptions',
            'inventory.stock-sheet' => 'inventory.stock-sheet',
            'inventory.stock-sheet.pdf' => 'inventory.stock-sheet',
            'equipment.index' => 'equipment',
            'download.receipt' => 'pos',
        ];

        return $map[$routeName] ?? $routeName;
    }
}
