<?php

namespace App\Exports;

use App\Models\CashMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CashMovementsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected string $dateFrom,
        protected string $dateTo,
        protected string $filterType = 'all',
        protected string $filterCategory = '',
        protected string $search = ''
    ) {
    }

    public function collection()
    {
        return CashMovement::query()
            ->with(['user', 'createdBy', 'transaction'])
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->filterType !== 'all', fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory !== '', fn($q) => $q->where('category', $this->filterCategory))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('description', 'like', $term)
                        ->orWhere('reference', 'like', $term);
                });
            })
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Type',
            'Categorie',
            'Description',
            'Reference',
            'Mode de paiement',
            'Montant (FC)',
            'Employe concerne',
            'Cree par',
            'ID Transaction',
            'Notes',
        ];
    }

    public function map($movement): array
    {
        return [
            $movement->date?->format('d/m/Y') ?? '-',
            $movement->type === 'entry' ? 'Entree' : 'Sortie',
            $movement->category_label,
            $movement->description ?? '-',
            $movement->reference ?? '-',
            $movement->payment_method_label,
            ($movement->type === 'entry' ? '+' : '-') . number_format((float) $movement->amount, 0, ',', ' '),
            $movement->user?->name ?? '-',
            $movement->createdBy?->name ?? '-',
            $movement->transaction_id ?? '-',
            $movement->notes ?? '-',
        ];
    }
}

