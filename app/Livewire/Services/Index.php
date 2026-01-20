<?php

namespace App\Livewire\Services;

use App\Models\Service;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $editingId = null;

    public string $name = '';
    public ?string $description = null;
    public int $duration_minutes = 30;
    public string $price = '0.00';
    public string $service_type = 'other';
    public bool $active = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'price' => ['required', 'numeric', 'min:0'],
            'service_type' => ['required', Rule::in(['home', 'woman', 'other'])],
            'active' => ['boolean'],
        ];
    }

    protected array $messages = [
        'name.required' => 'Le nom est requis.',
        'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
        'duration_minutes.required' => 'La durée est requise.',
        'duration_minutes.integer' => 'La durée doit être un entier.',
        'duration_minutes.min' => 'La durée minimale est 5 minutes.',
        'duration_minutes.max' => 'La durée maximale est 1440 minutes.',
        'price.required' => 'Le prix est requis.',
        'price.numeric' => 'Le prix doit être un nombre.',
        'price.min' => 'Le prix ne peut pas être négatif.',
        'service_type.required' => 'Le type de service est requis.',
        'service_type.in' => 'Le type de service est invalide.',
    ];

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName, $this->rules(), $this->messages);
    }

    public function render()
    {
        $services = Service::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.services.index', [
            'services' => $services,
        ])->layout('layouts.main', ['title' => 'Services']);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = 0;
    }

    public function edit(int $id): void
    {
        $service = Service::findOrFail($id);
        $this->editingId = $service->id;
        $this->name = $service->name;
        $this->description = $service->description;
        $this->duration_minutes = $service->duration_minutes;
        $this->price = (string) $service->price;
        $this->service_type = $service->service_type;
        $this->active = (bool) $service->active;
    }

    public function save(): void
    {
        $data = $this->validate($this->rules(), $this->messages);

        if ($this->editingId && $this->editingId > 0) {
            $service = Service::findOrFail($this->editingId);
            $service->update($data);
            session()->flash('success', 'Service mis à jour.');
        } else {
            Service::create($data);
            session()->flash('success', 'Service créé.');
        }

        $this->resetForm();
        $this->editingId = null;
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Service::findOrFail($id)->delete();
        session()->flash('success', 'Service supprimé.');
        $this->resetPage();
    }

    public function printList()
    {
        return redirect()->route('services.print');
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->description = null;
        $this->duration_minutes = 30;
        $this->price = '0.00';
        $this->service_type = 'other';
        $this->active = true;
    }
}
