<?php

namespace App\Livewire\Settings;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RolesPermissions extends Component
{
    // Liste
    public string $search = '';
    public string $activeTab = 'roles'; // roles, permissions

    // Formulaire rôle
    public bool $showRoleForm = false;
    public ?int $editingRoleId = null;
    public string $role_name = '';
    public string $role_display_name = '';
    public string $role_description = '';
    public array $role_permissions = [];

    // Formulaire permission
    public bool $showPermissionForm = false;
    public ?int $editingPermissionId = null;
    public string $perm_name = '';
    public string $perm_display_name = '';
    public string $perm_group = 'main';
    public string $perm_route_name = '';
    public string $perm_icon = '';
    public int $perm_order = 0;
    public bool $perm_is_menu = true;

    // Modal de suppression
    public bool $showDeleteModal = false;
    public ?string $deleteType = null;
    public ?int $deleteId = null;
    public ?string $deleteInfo = null;

    protected function rules(): array
    {
        return [
            'role_name' => 'required|string|max:50|regex:/^[a-z_]+$/|unique:roles,name,' . $this->editingRoleId,
            'role_display_name' => 'required|string|max:100',
            'role_description' => 'nullable|string|max:255',
        ];
    }

    public function getRolesProperty()
    {
        return Role::withCount('permissions', 'users')
            ->when($this->search && $this->activeTab === 'roles', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('display_name', 'like', "%{$this->search}%");
            })
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function getPermissionsProperty()
    {
        return Permission::withCount('roles')
            ->when($this->search && $this->activeTab === 'permissions', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('display_name', 'like', "%{$this->search}%");
            })
            ->orderBy('group')
            ->orderBy('order')
            ->get();
    }

    public function getPermissionsGroupedProperty()
    {
        return Permission::orderBy('order')->get()->groupBy('group');
    }

    // === Gestion des Rôles ===

    public function openRoleForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetRoleForm();

        if ($id) {
            $role = Role::with('permissions')->findOrFail($id);
            $this->editingRoleId = $role->id;
            $this->role_name = $role->name;
            $this->role_display_name = $role->display_name;
            $this->role_description = $role->description ?? '';
            $this->role_permissions = $role->permissions->pluck('id')->toArray();
        }

        $this->showRoleForm = true;
    }

    public function closeRoleForm(): void
    {
        $this->showRoleForm = false;
        $this->resetRoleForm();
    }

    public function resetRoleForm(): void
    {
        $this->editingRoleId = null;
        $this->role_name = '';
        $this->role_display_name = '';
        $this->role_description = '';
        $this->role_permissions = [];
    }

    public function saveRole(): void
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $data = [
                'name' => $this->role_name,
                'display_name' => $this->role_display_name,
                'description' => $this->role_description ?: null,
            ];

            if ($this->editingRoleId) {
                $role = Role::findOrFail($this->editingRoleId);

                // Ne pas modifier le nom des rôles système
                if ($role->is_system) {
                    unset($data['name']);
                }

                $role->update($data);
            } else {
                $data['is_system'] = false;
                $role = Role::create($data);
            }

            // Synchroniser les permissions
            $role->syncPermissions($this->role_permissions);

            DB::commit();
            session()->flash('success', 'Rôle ' . ($this->editingRoleId ? 'modifié' : 'créé') . ' avec succès.');
            $this->closeRoleForm();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function togglePermission(int $permissionId): void
    {
        if (in_array($permissionId, $this->role_permissions)) {
            $this->role_permissions = array_diff($this->role_permissions, [$permissionId]);
        } else {
            $this->role_permissions[] = $permissionId;
        }
    }

    public function selectAllPermissions(): void
    {
        $this->role_permissions = Permission::pluck('id')->toArray();
    }

    public function deselectAllPermissions(): void
    {
        $this->role_permissions = [];
    }

    public function selectGroupPermissions(string $group): void
    {
        $groupPermissions = Permission::where('group', $group)->pluck('id')->toArray();
        $this->role_permissions = array_unique(array_merge($this->role_permissions, $groupPermissions));
    }

    // === Gestion des Permissions ===

    public function openPermissionForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetPermissionForm();

        if ($id) {
            $perm = Permission::findOrFail($id);
            $this->editingPermissionId = $perm->id;
            $this->perm_name = $perm->name;
            $this->perm_display_name = $perm->display_name;
            $this->perm_group = $perm->group ?? 'main';
            $this->perm_route_name = $perm->route_name ?? '';
            $this->perm_icon = $perm->icon ?? '';
            $this->perm_order = $perm->order;
            $this->perm_is_menu = $perm->is_menu;
        }

        $this->showPermissionForm = true;
    }

    public function closePermissionForm(): void
    {
        $this->showPermissionForm = false;
        $this->resetPermissionForm();
    }

    public function resetPermissionForm(): void
    {
        $this->editingPermissionId = null;
        $this->perm_name = '';
        $this->perm_display_name = '';
        $this->perm_group = 'main';
        $this->perm_route_name = '';
        $this->perm_icon = '';
        $this->perm_order = 0;
        $this->perm_is_menu = true;
    }

    public function savePermission(): void
    {
        $this->validate([
            'perm_name' => 'required|string|max:100|unique:permissions,name,' . $this->editingPermissionId,
            'perm_display_name' => 'required|string|max:100',
            'perm_group' => 'required|string',
            'perm_route_name' => 'nullable|string|max:100',
            'perm_icon' => 'nullable|string|max:100',
            'perm_order' => 'required|integer|min:0',
        ]);

        $data = [
            'name' => $this->perm_name,
            'display_name' => $this->perm_display_name,
            'group' => $this->perm_group,
            'route_name' => $this->perm_route_name ?: null,
            'icon' => $this->perm_icon ?: null,
            'order' => $this->perm_order,
            'is_menu' => $this->perm_is_menu,
        ];

        if ($this->editingPermissionId) {
            Permission::findOrFail($this->editingPermissionId)->update($data);
            session()->flash('success', 'Permission modifiée avec succès.');
        } else {
            Permission::create($data);
            session()->flash('success', 'Permission créée avec succès.');
        }

        $this->closePermissionForm();
    }

    // === Suppression ===

    public function confirmDelete(string $type, int $id): void
    {
        $this->deleteType = $type;
        $this->deleteId = $id;

        if ($type === 'role') {
            $role = Role::find($id);
            if ($role?->is_system) {
                session()->flash('error', 'Les rôles système ne peuvent pas être supprimés.');
                return;
            }
            $this->deleteInfo = $role?->display_name;
        } else {
            $perm = Permission::find($id);
            $this->deleteInfo = $perm?->display_name;
        }

        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteType = null;
        $this->deleteId = null;
        $this->deleteInfo = null;
    }

    public function delete(): void
    {
        if ($this->deleteType === 'role') {
            $role = Role::find($this->deleteId);
            if ($role?->is_system) {
                session()->flash('error', 'Les rôles système ne peuvent pas être supprimés.');
                $this->closeDeleteModal();
                return;
            }
            $role?->delete();
            session()->flash('success', 'Rôle supprimé avec succès.');
        } else {
            Permission::find($this->deleteId)?->delete();
            session()->flash('success', 'Permission supprimée avec succès.');
        }

        $this->closeDeleteModal();
    }

    public function render()
    {
        return view('livewire.settings.roles-permissions', [
            'roles' => $this->roles,
            'permissions' => $this->permissions,
            'permissionsGrouped' => $this->permissionsGrouped,
            'groupLabels' => Permission::$groupLabels,
        ])->layout('layouts.main', ['title' => 'Rôles & Permissions']);
    }
}
