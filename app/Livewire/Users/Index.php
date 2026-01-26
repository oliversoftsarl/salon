<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\StaffProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
// ... existing code ...
use Illuminate\Support\Str;
use App\Notifications\PasswordResetSetByAdmin;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $editingId = null;

    // Form
    public string $name = '';
    public string $email = '';
    public string $role = 'staff';
    public bool $active = true;
    public string $password = '';
    public string $password_confirmation = '';
    public string $staff_category = '';

    // Catégories de staff disponibles
    public array $staffCategories = [
        '' => '-- Sélectionner une fonction --',
        'Coiffeur/Coiffeuse' => 'Coiffeur/Coiffeuse',
        'Masseur/Masseuse' => 'Masseur/Masseuse',
        'Esthéticien(ne)' => 'Esthéticien(ne)',
        'Manucure' => 'Manucure',
        'Pédicure' => 'Pédicure',
        'Maquilleur/Maquilleuse' => 'Maquilleur/Maquilleuse',
        'Barbier' => 'Barbier',
        'Réceptionniste' => 'Réceptionniste',
        'Caissier/Caissière' => 'Caissier/Caissière',
        'Manager' => 'Manager',
        'Assistant(e)' => 'Assistant(e)',
        'Autre' => 'Autre',
    ];

    protected function rules(): array
    {
        $uniqueEmail = Rule::unique('users', 'email');
        if ($this->editingId) {
            $uniqueEmail = $uniqueEmail->ignore($this->editingId);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $uniqueEmail],
            'role' => ['required', Rule::in(['admin','staff','cashier'])],
            'active' => ['boolean'],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8', 'same:password_confirmation'],
            'password_confirmation' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8'],
            'staff_category' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected array $messages = [
        'name.required' => 'Le nom est requis.',
        'email.required' => 'L’email est requis.',
        'email.email' => 'L’email est invalide.',
        'email.unique' => 'Cet email est déjà utilisé.',
        'role.required' => 'Le rôle est requis.',
        'role.in' => 'Rôle invalide.',
        'password.required' => 'Le mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.same' => 'La confirmation ne correspond pas.',
    ];

    public function updated($property): void
    {
        $this->validateOnly($property, $this->rules(), $this->messages);
    }

    public function render()
    {
        $users = User::query()
            ->with('staffProfile')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('role', 'like', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.users.index', compact('users'))
            ->layout('layouts.main', ['title' => 'Utilisateurs']);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = 0;
    }

    public function edit(int $id): void
    {
        $u = User::with('staffProfile')->findOrFail($id);
        $this->editingId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->role = $u->role;
        $this->active = (bool)$u->active;
        $this->password = '';
        $this->password_confirmation = '';
        $this->staff_category = $u->staffProfile?->role_title ?? '';
    }

    public function save(): void
    {
        $data = $this->validate($this->rules(), $this->messages);

        // Préservation du mot de passe si vide en édition
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password'], $data['password_confirmation']);
        }

        if ($this->editingId && $this->editingId > 0) {
            $u = User::findOrFail($this->editingId);

            // Sécurités "dernier admin"
            if ($u->role === 'admin') {
                // si on rétrograde un admin
                if (isset($data['role']) && $data['role'] !== 'admin' && $this->isLastActiveAdmin($u->id)) {
                    $this->addError('role', 'Impossible de rétrograder le dernier administrateur actif.');
                    return;
                }
                // si on désactive un admin
                if (array_key_exists('active', $data) && $u->active && !$data['active'] && $this->isLastActiveAdmin($u->id)) {
                    $this->addError('active', 'Impossible de désactiver le dernier administrateur actif.');
                    return;
                }
            }

            $u->update($data);

            // Mettre à jour ou créer le profil staff si une catégorie est sélectionnée
            if (!empty($this->staff_category)) {
                StaffProfile::updateOrCreate(
                    ['user_id' => $u->id],
                    [
                        'display_name' => $u->name,
                        'role_title' => $this->staff_category,
                        'hourly_rate' => $u->staffProfile?->hourly_rate ?? 0,
                    ]
                );
            }

            session()->flash('success', 'Utilisateur mis à jour.');
        } else {
            $user = User::create($data);

            // Créer le profil staff si une catégorie est sélectionnée
            if (!empty($this->staff_category)) {
                StaffProfile::create([
                    'user_id' => $user->id,
                    'display_name' => $user->name,
                    'role_title' => $this->staff_category,
                    'hourly_rate' => 0,
                ]);
            }

            session()->flash('success', 'Utilisateur créé.');
        }

        $this->resetForm();
        $this->editingId = null;
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $u = User::findOrFail($id);
        if (auth()->id() === $u->id && $u->active) {
            $this->addError('active', 'Vous ne pouvez pas vous désactiver vous-même.');
            return;
        }
        $u->active = !$u->active;
        $u->save();
        session()->flash('success', $u->active ? 'Utilisateur activé.' : 'Utilisateur désactivé.');
    }

    public function delete(int $id): void
    {
        if (auth()->id() === $id) {
            $this->addError('delete', 'Vous ne pouvez pas supprimer votre propre compte.');
            return;
        }

        $u = User::findOrFail($id);

        if ($u->role === 'admin' && $this->isLastActiveAdmin($u->id)) {
            $this->addError('delete', 'Impossible de supprimer le dernier administrateur actif.');
            return;
        }

        $u->delete();
        session()->flash('success', 'Utilisateur supprimé avec succès.');
    }

    private function isLastActiveAdmin(int $excludeUserId = 0): bool
    {
        $count = User::query()
            ->where('role', 'admin')
            ->where('active', true)
            ->when($excludeUserId > 0, fn($q) => $q->where('id', '!=', $excludeUserId))
            ->count();

        return $count === 0;
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->role = 'staff';
        $this->active = true;
        $this->password = '';
        $this->password_confirmation = '';
        $this->staff_category = '';
    }
}
