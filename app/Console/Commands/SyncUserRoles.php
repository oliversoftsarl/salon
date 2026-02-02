<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class SyncUserRoles extends Command
{
    protected $signature = 'users:sync-roles';
    protected $description = 'Synchronise les role_id des utilisateurs basé sur leur champ role';

    public function handle()
    {
        $this->info('Synchronisation des rôles utilisateurs...');

        $roles = Role::all()->keyBy('name');

        if ($roles->isEmpty()) {
            $this->error('Aucun rôle trouvé. Exécutez d\'abord: php artisan db:seed --class=RolesAndPermissionsSeeder');
            return 1;
        }

        $updated = 0;
        $users = User::whereNull('role_id')->orWhere('role_id', 0)->get();

        foreach ($users as $user) {
            $roleName = $user->role ?? 'staff';
            $role = $roles->get($roleName);

            if ($role) {
                $user->role_id = $role->id;
                $user->save();
                $updated++;
                $this->line("  - {$user->name}: {$roleName} -> role_id={$role->id}");
            } else {
                $this->warn("  - {$user->name}: Rôle '{$roleName}' non trouvé");
            }
        }

        $this->info("Terminé. {$updated} utilisateur(s) mis à jour.");
        return 0;
    }
}
