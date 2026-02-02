<?php

namespace App\Livewire\Staff;

use App\Models\StaffBreak;
use App\Models\StaffProfile;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class Schedule extends Component
{
    public ?int $selected_user_id = null;

    // Edition availability sous forme JSON texte pour MVP
    public string $availability_json = '';

    // Pause form
    public string $break_start_at = '';
    public string $break_end_at = '';
    public bool $break_recurring = false;

    // Profile edit form
    public string $edit_display_name = '';
    public string $edit_role_title = '';
    public string $edit_phone = '';
    public string $edit_hourly_rate = '';
    public bool $showProfileModal = false;

    // Rôles prédéfinis pour le staff
    public array $availableRoles = [
        'Coiffeur/Coiffeuse' => 'Coiffeur/Coiffeuse',
        'Masseuse/Masseur' => 'Masseuse/Masseur',
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

    public function render()
    {
        $users = User::orderBy('name')->get(['id', 'name']);
        $profile = $this->selected_user_id
            ? StaffProfile::firstOrCreate(['user_id' => $this->selected_user_id], [
                'display_name' => User::find($this->selected_user_id)?->name ?? 'Staff',
                'role_title' => 'Staff',
                'hourly_rate' => 0,
                'availability' => null,
            ])
            : null;

        if ($profile && $this->availability_json === '') {
            $this->availability_json = $profile->availability ? json_encode($profile->availability, JSON_PRETTY_PRINT) : '';
        }

        $breaks = $this->selected_user_id
            ? StaffBreak::where('staff_id', $this->selected_user_id)->orderByDesc('start_at')->limit(50)->get()
            : collect();

        return view('livewire.staff.schedule', compact('users', 'profile', 'breaks'))
            ->layout('layouts.main', ['title' => 'Staff & Pauses']);
    }

    public function saveAvailability(): void
    {
        if (!$this->selected_user_id) return;

        $data = null;
        if (trim($this->availability_json) !== '') {
            try {
                $parsed = json_decode($this->availability_json, true, 512, JSON_THROW_ON_ERROR);
                $data = $parsed;
            } catch (\Throwable $e) {
                $this->addError('availability_json', 'JSON invalide');
                return;
            }
        }

        $profile = StaffProfile::firstOrCreate(
            ['user_id' => $this->selected_user_id],
            ['display_name' => User::find($this->selected_user_id)?->name ?? 'Staff', 'role_title' => 'Staff', 'hourly_rate' => 0]
        );
        $profile->availability = $data;
        $profile->save();

        session()->flash('success', 'Disponibilités enregistrées.');
    }

    public function addBreak(): void
    {
        $this->validate([
            'selected_user_id' => ['required', 'exists:users,id'],
            'break_start_at' => ['required', 'date'],
            'break_end_at' => ['required', 'date', 'after:break_start_at'],
        ]);

        $start = Carbon::parse($this->break_start_at);
        $end = Carbon::parse($this->break_end_at);

        // Empêcher chevauchement avec autres pauses
        $overlap = StaffBreak::where('staff_id', $this->selected_user_id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                  ->orWhereBetween('end_at', [$start, $end])
                  ->orWhere(function ($qq) use ($start, $end) {
                      $qq->where('start_at', '<=', $start)->where('end_at', '>=', $end);
                  });
            })->exists();

        if ($overlap) {
            $this->addError('break_start_at', 'Chevauche une pause existante.');
            return;
        }

        StaffBreak::create([
            'staff_id' => $this->selected_user_id,
            'start_at' => $start,
            'end_at' => $end,
            'recurring' => $this->break_recurring,
        ]);

        $this->reset(['break_start_at', 'break_end_at', 'break_recurring']);
        session()->flash('success', 'Pause ajoutée.');
    }

    public function deleteBreak(int $id): void
    {
        StaffBreak::findOrFail($id)->delete();
        session()->flash('success', 'Pause supprimée.');
    }

    public function openProfileModal(): void
    {
        if (!$this->selected_user_id) return;

        $profile = StaffProfile::where('user_id', $this->selected_user_id)->first();
        if ($profile) {
            $this->edit_display_name = $profile->display_name;
            $this->edit_role_title = $profile->role_title;
            $this->edit_phone = $profile->phone ?? '';
            $this->edit_hourly_rate = (string) $profile->hourly_rate;
        } else {
            $user = User::find($this->selected_user_id);
            $this->edit_display_name = $user?->name ?? 'Staff';
            $this->edit_role_title = 'Coiffeur/Coiffeuse';
            $this->edit_phone = '';
            $this->edit_hourly_rate = '0';
        }
        $this->showProfileModal = true;
    }

    public function closeProfileModal(): void
    {
        $this->showProfileModal = false;
        $this->resetValidation();
    }

    public function saveProfile(): void
    {
        $this->validate([
            'edit_display_name' => ['required', 'string', 'max:255'],
            'edit_role_title' => ['required', 'string', 'max:255'],
            'edit_phone' => ['nullable', 'string', 'max:20'],
            'edit_hourly_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $profile = StaffProfile::updateOrCreate(
            ['user_id' => $this->selected_user_id],
            [
                'display_name' => $this->edit_display_name,
                'role_title' => $this->edit_role_title,
                'phone' => $this->edit_phone ?: null,
                'hourly_rate' => $this->edit_hourly_rate,
            ]
        );

        $this->showProfileModal = false;
        session()->flash('success', 'Profil du staff mis à jour avec succès.');
    }
}
