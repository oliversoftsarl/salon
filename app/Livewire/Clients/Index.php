<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $editingId = null;

    public string $name = '';
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $notes = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(10);

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
        $this->name = $c->name;
        $this->email = $c->email;
        $this->phone = $c->phone;
        $this->notes = $c->notes;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId && $this->editingId > 0) {
            Client::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Client mis à jour.');
        } else {
            Client::create($data);
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

    private function resetForm(): void
    {
        $this->name = '';
        $this->email = null;
        $this->phone = null;
        $this->notes = null;
    }
}
