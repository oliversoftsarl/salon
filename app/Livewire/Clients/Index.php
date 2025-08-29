<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $editingId = null;

    // Champs UI
    public string $name = '';
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $notes = null;

    public ?string $birthdate = null;
    public ?string $gender = null;
    public ?int $loyalty_point = 0;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:16'],
            'loyalty_point' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->when($this->hasColumn('first_name'), fn($x) => $x->orWhere('first_name', 'like', $term))
                      ->when($this->hasColumn('last_name'), fn($x) => $x->orWhere('last_name', 'like', $term))
                      ->when(! $this->hasColumn('first_name') && $this->hasColumn('name'), fn($x) => $x->orWhere('name', 'like', $term))
                      ->orWhere('email', 'like', $term)
                      ->when($this->hasColumn('phone'), fn($x) => $x->orWhere('phone', 'like', $term))
                      ->when($this->hasColumn('phone_number'), fn($x) => $x->orWhere('phone_number', 'like', $term));
                });
            })
            ->orderBy($this->hasColumn('first_name') ? 'first_name' : ($this->hasColumn('name') ? 'name' : 'id'))
            ->paginate(10);

        $clients->getCollection()->transform(function ($c) {
            if ($this->hasColumn('first_name')) {
                $c->name = trim(($c->first_name ?? '').' '.($c->last_name ?? ''));
            }
            return $c;
        });

        return view('livewire.clients.index', compact('clients'))
            ->layout('layouts.main', ['title' => 'Clients']);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = 0;
    }

    public function edit(int $id): void
    {
        $c = Client::findOrFail($id);
        $this->editingId = $c->id;

        $this->name = $this->hasColumn('first_name')
            ? trim(($c->first_name ?? '').' '.($c->last_name ?? ''))
            : (string)($c->name ?? $c->names ?? '');

        $this->email = $c->email;
        $this->phone = $c->phone ?? $c->phone_number ?? null;
        $this->notes = $c->notes ?? null;

        $this->birthdate = $c->birthdate?->toDateString() ?? null;
        $this->gender = $c->gender ?? null;
        $this->loyalty_point = (int)($c->loyalty_point ?? 0);
    }

    public function save(): void
    {
        $this->validate();

        $payload = $this->buildClientPayload();

        if ($this->editingId && $this->editingId > 0) {
            Client::findOrFail($this->editingId)->update($payload);
            session()->flash('success', 'Client mis à jour.');
        } else {
            Client::create($payload);
            session()->flash('success', 'Client créé.');
        }

        $this->resetForm();
        $this->editingId = null;
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Client::findOrFail($id)->delete();
        session()->flash('success', 'Client supprimé.');
        $this->resetPage();
    }

    private function buildClientPayload(): array
    {
        $payload = [];

        if ($this->hasColumn('first_name')) {
            $parts = preg_split('/\s+/', trim($this->name));
            $first = $parts[0] ?? $this->name;
            $last  = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : null;
            $payload['first_name'] = $first;
            if ($this->hasColumn('last_name')) {
                $payload['last_name'] = $last;
            }
        } elseif ($this->hasColumn('name')) {
            $payload['name'] = $this->name;
        } elseif ($this->hasColumn('names')) {
            $payload['names'] = $this->name;
        }

        if ($this->hasColumn('email')) {
            $payload['email'] = $this->email;
        }

        if ($this->hasColumn('phone')) {
            $payload['phone'] = $this->phone;
        } elseif ($this->hasColumn('phone_number')) {
            $payload['phone_number'] = $this->phone;
        }

        if ($this->hasColumn('birthdate')) {
            $payload['birthdate'] = $this->birthdate ?: null;
        }
        if ($this->hasColumn('gender')) {
            $payload['gender'] = $this->gender ?: null;
        }
        if ($this->hasColumn('loyalty_point')) {
            $payload['loyalty_point'] = (int)($this->loyalty_point ?? 0);
        }
        if ($this->hasColumn('notes')) {
            $payload['notes'] = $this->notes ?: null;
        }

        return $payload;
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->email = null;
        $this->phone = null;
        $this->notes = null;
        $this->birthdate = null;
        $this->gender = null;
        $this->loyalty_point = 0;
    }

    private function hasColumn(string $col): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = collect(Schema::getColumnListing((new Client)->getTable()))->flip();
        }
        return $cache->has($col);
    }
}
