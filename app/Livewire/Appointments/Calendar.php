<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class Calendar extends Component
{
    public string $weekStart; // ISO week start date
    public ?int $client_id = null;
    public ?int $staff_id = null;
    public ?int $service_id = null;
    public string $start_at = '';
    public string $end_at = '';

    protected function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'staff_id' => ['required', 'exists:users,id'],
            'service_id' => ['required', 'exists:services,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
        ];
    }

    protected array $messages = [
        'client_id.required' => 'Le client est requis.',
        'client_id.exists' => 'Le client sélectionné est invalide.',
        'staff_id.required' => 'Le membre du staff est requis.',
        'staff_id.exists' => 'Le staff sélectionné est invalide.',
        'service_id.required' => 'Le service est requis.',
        'service_id.exists' => 'Le service sélectionné est invalide.',
        'start_at.required' => 'La date/heure de début est requise.',
        'start_at.date' => 'La date/heure de début est invalide.',
        'end_at.required' => 'La date/heure de fin est requise.',
        'end_at.date' => 'La date/heure de fin est invalide.',
        'end_at.after' => 'La fin doit être après le début.',
    ];

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName, $this->rules(), $this->messages);
    }

    public function mount(): void
    {
        $this->weekStart = Carbon::now()->startOfWeek()->toDateString();
    }

    public function render()
    {
        $start = Carbon::parse($this->weekStart)->startOfWeek();
        $end = (clone $start)->endOfWeek();

        $appointments = Appointment::query()
            ->whereBetween('start_at', [$start, $end])
            ->with(['client', 'service', 'staff'])
            ->get();

        // Clients: fabrique un label robuste quel que soit le schéma
        $clients = Client::query()
            ->orderBy('id')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'label' => $this->clientLabel($c),
            ]);

        return view('livewire.appointments.calendar', [
            'days' => collect(range(0, 6))->map(fn($i) => (clone $start)->addDays($i)),
            'appointments' => $appointments,
            'clients' => $clients,
            'services' => Service::orderBy('name')->get(['id', 'name', 'duration_minutes']),
            'staff' => User::orderBy('name')->get(['id', 'name']),
        ])->layout('layouts.main', ['title' => 'Rendez-vous']);
    }

    private function clientLabel(object $c): string
    {
        // Essaie successivement plusieurs champs usuels
        $candidates = [
            'name',
            'full_name',
            fn() => isset($c->first_name, $c->last_name) ? trim("{$c->first_name} {$c->last_name}") : null,
            'first_name',
            'last_name',
            'email',
            'phone',
        ];

        foreach ($candidates as $candidate) {
            $value = is_callable($candidate) ? $candidate() : ($c->{$candidate} ?? null);
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return "Client #{$c->id}";
    }

    public function previousWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->toDateString();
    }

    public function createAppointment(): void
    {
        $this->validate($this->rules(), $this->messages);

        $start = Carbon::parse($this->start_at);
        $end = Carbon::parse($this->end_at);

        // Vérifie chevauchements avec autres rendez-vous
        $overlapAppointment = Appointment::query()
            ->where('staff_id', $this->staff_id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end])
                    ->orWhere(function ($qq) use ($start, $end) {
                        $qq->where('start_at', '<=', $start)->where('end_at', '>=', $end);
                    });
            })
            ->exists();

        if ($overlapAppointment) {
            $this->addError('start_at', 'Chevauchement avec un autre rendez-vous pour ce staff.');
            return;
        }

        Appointment::create([
            'client_id' => $this->client_id,
            'staff_id' => $this->staff_id,
            'service_id' => $this->service_id,
            'start_at' => $start,
            'end_at' => $end,
            'status' => 'scheduled',
        ]);

        $this->reset(['client_id', 'staff_id', 'service_id', 'start_at', 'end_at']);
        session()->flash('success', 'Rendez-vous créé.');
    }
}
