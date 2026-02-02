<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les permissions (menus)
        $permissions = [
            // Menu Principal
            ['name' => 'dashboard', 'display_name' => 'Tableau de bord', 'group' => 'main', 'route_name' => 'dashboard', 'icon' => 'ni ni-tv-2', 'order' => 1],

            // Ventes
            ['name' => 'pos', 'display_name' => 'Point de Vente', 'group' => 'sales', 'route_name' => 'pos.checkout', 'icon' => 'ni ni-cart', 'order' => 10],
            ['name' => 'pos.transactions', 'display_name' => 'Liste des Ventes', 'group' => 'sales', 'route_name' => 'pos.transactions', 'icon' => 'ni ni-bullet-list-67', 'order' => 11],
            ['name' => 'clients', 'display_name' => 'Clients', 'group' => 'sales', 'route_name' => 'clients.index', 'icon' => 'ni ni-single-02', 'order' => 12],
            ['name' => 'appointments', 'display_name' => 'Rendez-vous', 'group' => 'sales', 'route_name' => 'appointments.calendar', 'icon' => 'ni ni-calendar-grid-58', 'order' => 13],

            // Services & Produits
            ['name' => 'services', 'display_name' => 'Services', 'group' => 'main', 'route_name' => 'services.index', 'icon' => 'ni ni-scissors', 'order' => 20],
            ['name' => 'products', 'display_name' => 'Produits', 'group' => 'main', 'route_name' => 'products.index', 'icon' => 'ni ni-box-2', 'order' => 21],

            // Inventaire
            ['name' => 'inventory.supplies', 'display_name' => 'Approvisionnements', 'group' => 'inventory', 'route_name' => 'inventory.supplies', 'icon' => 'ni ni-delivery-fast', 'order' => 30],
            ['name' => 'inventory.consumptions', 'display_name' => 'Consommations', 'group' => 'inventory', 'route_name' => 'inventory.consumptions', 'icon' => 'ni ni-archive-2', 'order' => 31],
            ['name' => 'inventory.stock-sheet', 'display_name' => 'Fiche de Stock', 'group' => 'inventory', 'route_name' => 'inventory.stock-sheet', 'icon' => 'ni ni-single-copy-04', 'order' => 32],

            // Staff & RH
            ['name' => 'staff.schedule', 'display_name' => 'Gestion Staff', 'group' => 'staff', 'route_name' => 'staff.schedule', 'icon' => 'ni ni-single-02', 'order' => 40],
            ['name' => 'staff.performance', 'display_name' => 'Performance', 'group' => 'staff', 'route_name' => 'staff.performance', 'icon' => 'ni ni-chart-bar-32', 'order' => 41],
            ['name' => 'staff.debts', 'display_name' => 'Dettes Staff', 'group' => 'staff', 'route_name' => 'staff.debts', 'icon' => 'ni ni-credit-card', 'order' => 42],
            ['name' => 'payroll', 'display_name' => 'Gestion Paie', 'group' => 'staff', 'route_name' => 'payroll.index', 'icon' => 'ni ni-money-coins', 'order' => 43],

            // Finance
            ['name' => 'cash', 'display_name' => 'Gestion Caisse', 'group' => 'finance', 'route_name' => 'cash.register', 'icon' => 'ni ni-money-coins', 'order' => 50],

            // Équipements
            ['name' => 'equipment', 'display_name' => 'Équipements', 'group' => 'inventory', 'route_name' => 'equipment.index', 'icon' => 'fas fa-tools', 'order' => 33],

            // Paramètres
            ['name' => 'users', 'display_name' => 'Utilisateurs', 'group' => 'settings', 'route_name' => 'users.index', 'icon' => 'ni ni-badge', 'order' => 60],
            ['name' => 'roles', 'display_name' => 'Rôles & Permissions', 'group' => 'settings', 'route_name' => 'settings.roles', 'icon' => 'ni ni-key-25', 'order' => 61],
            ['name' => 'settings.exchange-rates', 'display_name' => 'Taux de Change', 'group' => 'settings', 'route_name' => 'settings.exchange-rates', 'icon' => 'ni ni-curved-next', 'order' => 62],
            ['name' => 'settings.revenue', 'display_name' => 'Paramètres Recette', 'group' => 'settings', 'route_name' => 'settings.revenue', 'icon' => 'ni ni-settings-gear-65', 'order' => 63],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name']],
                $perm
            );
        }

        // Créer les rôles système
        $adminRole = Role::updateOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrateur',
                'description' => 'Accès complet à toutes les fonctionnalités',
                'is_system' => true,
            ]
        );

        $staffRole = Role::updateOrCreate(
            ['name' => 'staff'],
            [
                'display_name' => 'Staff',
                'description' => 'Employé du salon avec accès limité',
                'is_system' => true,
            ]
        );

        $cashierRole = Role::updateOrCreate(
            ['name' => 'cashier'],
            [
                'display_name' => 'Caissier',
                'description' => 'Accès au point de vente uniquement',
                'is_system' => true,
            ]
        );

        $managerRole = Role::updateOrCreate(
            ['name' => 'manager'],
            [
                'display_name' => 'Manager',
                'description' => 'Gestionnaire avec accès étendu',
                'is_system' => true,
            ]
        );

        // Attribuer toutes les permissions à l'admin
        $allPermissions = Permission::pluck('id')->toArray();
        $adminRole->syncPermissions($allPermissions);

        // Permissions pour le staff
        $staffPermissions = Permission::whereIn('name', [
            'dashboard', 'pos', 'pos.transactions', 'clients', 'appointments',
            'services', 'products', 'inventory.consumptions'
        ])->pluck('id')->toArray();
        $staffRole->syncPermissions($staffPermissions);

        // Permissions pour le caissier
        $cashierPermissions = Permission::whereIn('name', [
            'pos', 'pos.transactions'
        ])->pluck('id')->toArray();
        $cashierRole->syncPermissions($cashierPermissions);

        // Permissions pour le manager
        $managerPermissions = Permission::whereIn('name', [
            'dashboard', 'pos', 'pos.transactions', 'clients', 'appointments',
            'services', 'products', 'inventory.supplies', 'inventory.consumptions',
            'inventory.stock-sheet', 'staff.performance', 'cash'
        ])->pluck('id')->toArray();
        $managerRole->syncPermissions($managerPermissions);

        // Mettre à jour les utilisateurs existants avec leurs rôles
        User::where('role', 'admin')->update(['role_id' => $adminRole->id]);
        User::where('role', 'staff')->update(['role_id' => $staffRole->id]);
        User::where('role', 'cashier')->update(['role_id' => $cashierRole->id]);
    }
}
