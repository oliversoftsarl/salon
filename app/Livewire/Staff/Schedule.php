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
}
