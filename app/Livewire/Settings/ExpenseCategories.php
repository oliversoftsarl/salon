<?php

namespace App\Livewire\Settings;

use App\Models\CashMovement;
use App\Models\Setting;
use Illuminate\Support\Str;
use Livewire\Component;

class ExpenseCategories extends Component
{
    public string $new_key = '';
    public string $new_label = '';

    public bool $showEditModal = false;
    public ?string $editing_original_key = null;
    public string $editing_key = '';
    public string $editing_label = '';

    public function getCategoriesProperty(): array
    {
        $configured = Setting::getValue('cash.exit_categories', []);

        if (!is_array($configured) || empty($configured)) {
            $labels = CashMovement::getCategoryLabels();
            $base = [];

            foreach (CashMovement::$defaultExitCategories as $key) {
                $base[$key] = $labels[$key] ?? Str::headline(str_replace('_', ' ', $key));
            }

            return $base;
        }

        $result = [];
        foreach ($configured as $key => $label) {
            $normalized = Str::slug((string) $key, '_');
            if ($normalized === '') {
                continue;
            }

            $clean = trim((string) $label);
            $result[$normalized] = $clean !== '' ? $clean : Str::headline(str_replace('_', ' ', $normalized));
        }

        return $result;
    }

    public function add(): void
    {
        $this->validate([
            'new_label' => ['required', 'string', 'max:100'],
            'new_key' => ['nullable', 'string', 'max:100'],
        ]);

        $rawKey = trim($this->new_key) !== '' ? $this->new_key : $this->new_label;
        $key = Str::slug($rawKey, '_');

        if ($key === '') {
            session()->flash('error', 'Clé de catégorie invalide.');
            return;
        }

        $categories = $this->categories;

        if (array_key_exists($key, $categories)) {
            session()->flash('error', 'Cette catégorie existe déjà.');
            return;
        }

        $categories[$key] = trim($this->new_label);
        ksort($categories);
        $this->saveCategories($categories);

        $this->new_key = '';
        $this->new_label = '';

        session()->flash('success', 'Catégorie ajoutée avec succès.');
    }

    public function openEdit(string $key): void
    {
        $categories = $this->categories;
        if (!array_key_exists($key, $categories)) {
            session()->flash('error', 'Catégorie introuvable.');
            return;
        }

        $this->editing_original_key = $key;
        $this->editing_key = $key;
        $this->editing_label = $categories[$key];
        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editing_key' => ['required', 'string', 'max:100'],
            'editing_label' => ['required', 'string', 'max:100'],
        ]);

        if ($this->editing_original_key === null) {
            session()->flash('error', 'Aucune catégorie sélectionnée.');
            return;
        }

        $newKey = Str::slug($this->editing_key, '_');
        if ($newKey === '') {
            session()->flash('error', 'Clé de catégorie invalide.');
            return;
        }

        $categories = $this->categories;
        if (!array_key_exists($this->editing_original_key, $categories)) {
            session()->flash('error', 'La catégorie n\'existe plus.');
            $this->closeEdit();
            return;
        }

        if ($newKey !== $this->editing_original_key && array_key_exists($newKey, $categories)) {
            session()->flash('error', 'La nouvelle clé existe déjà.');
            return;
        }

        unset($categories[$this->editing_original_key]);
        $categories[$newKey] = trim($this->editing_label);
        ksort($categories);
        $this->saveCategories($categories);

        $this->closeEdit();
        session()->flash('success', 'Catégorie modifiée avec succès.');
    }

    public function delete(string $key): void
    {
        $categories = $this->categories;

        if (!array_key_exists($key, $categories)) {
            return;
        }

        if (count($categories) <= 1) {
            session()->flash('error', 'Au moins une catégorie doit rester.');
            return;
        }

        unset($categories[$key]);
        $this->saveCategories($categories);

        session()->flash('success', 'Catégorie supprimée.');
    }

    public function closeEdit(): void
    {
        $this->showEditModal = false;
        $this->editing_original_key = null;
        $this->editing_key = '';
        $this->editing_label = '';
    }

    protected function saveCategories(array $categories): void
    {
        Setting::setValue(
            'cash.exit_categories',
            $categories,
            'json',
            'cash',
            'Catégories configurables des sorties de caisse'
        );
    }

    public function render()
    {
        return view('livewire.settings.expense-categories', [
            'categories' => $this->categories,
        ])->layout('layouts.main', ['title' => 'Catégories de Dépenses']);
    }
}

