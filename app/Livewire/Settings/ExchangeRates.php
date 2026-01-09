<?php

namespace App\Livewire\Settings;

use App\Models\ExchangeRate;
use Livewire\Component;
use Livewire\WithPagination;

class ExchangeRates extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;

    // Formulaire
    public float $form_rate = 0;
    public string $form_effective_date = '';
    public bool $form_is_active = true;
    public string $form_notes = '';

    protected function rules(): array
    {
        return [
            'form_rate' => ['required', 'numeric', 'min:0.0001'],
            'form_effective_date' => ['required', 'date'],
            'form_is_active' => ['boolean'],
            'form_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function mount(): void
    {
        $this->form_effective_date = now()->toDateString();
    }

    public function getCurrentRateProperty()
    {
        return ExchangeRate::getCurrentRate();
    }

    public function getRatesProperty()
    {
        return ExchangeRate::usdToCdf()
            ->orderByDesc('effective_date')
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $rate = ExchangeRate::findOrFail($id);

        $this->editingId = $rate->id;
        $this->form_rate = $rate->rate;
        $this->form_effective_date = $rate->effective_date->toDateString();
        $this->form_is_active = $rate->is_active;
        $this->form_notes = $rate->notes ?? '';

        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'from_currency' => ExchangeRate::CURRENCY_USD,
            'to_currency' => ExchangeRate::CURRENCY_CDF,
            'rate' => $this->form_rate,
            'effective_date' => $this->form_effective_date,
            'is_active' => $this->form_is_active,
            'notes' => $this->form_notes ?: null,
            'created_by' => auth()->id(),
        ];

        // Si on active ce taux, désactiver les autres pour la même date ou antérieurs
        if ($this->form_is_active) {
            ExchangeRate::usdToCdf()
                ->where('id', '!=', $this->editingId ?? 0)
                ->where('effective_date', '<=', $this->form_effective_date)
                ->update(['is_active' => false]);
        }

        if ($this->editingId) {
            ExchangeRate::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Taux de change modifié avec succès.');
        } else {
            ExchangeRate::create($data);
            session()->flash('success', 'Taux de change créé avec succès.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function activate(int $id): void
    {
        $rate = ExchangeRate::findOrFail($id);

        // Désactiver tous les autres taux
        ExchangeRate::usdToCdf()
            ->where('id', '!=', $id)
            ->update(['is_active' => false]);

        // Activer celui-ci
        $rate->update(['is_active' => true]);

        session()->flash('success', 'Taux de change activé.');
    }

    public function delete(int $id): void
    {
        $rate = ExchangeRate::findOrFail($id);

        if ($rate->is_active) {
            session()->flash('error', 'Impossible de supprimer le taux actif. Activez d\'abord un autre taux.');
            return;
        }

        $rate->delete();
        session()->flash('success', 'Taux de change supprimé.');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->form_rate = ExchangeRate::getCurrentRateValue();
        $this->form_effective_date = now()->toDateString();
        $this->form_is_active = true;
        $this->form_notes = '';
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.settings.exchange-rates', [
            'currentRate' => $this->currentRate,
            'rates' => $this->rates,
        ])->layout('layouts.main', ['title' => 'Taux de Change']);
    }
}

