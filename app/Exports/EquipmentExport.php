<?php

namespace App\Exports;

use App\Models\Equipment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquipmentExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected ?string $category;
    protected ?string $subCategory;
    protected ?string $status;
    protected ?string $condition;

    public function __construct(
        ?string $category = null,
        ?string $subCategory = null,
        ?string $status = null,
        ?string $condition = null
    ) {
        $this->category = $category;
        $this->subCategory = $subCategory;
        $this->status = $status;
        $this->condition = $condition;
    }

    public function collection()
    {
        return Equipment::query()
            ->with(['assignedUser'])
            ->when($this->category, fn($q) => $q->where('category', $this->category))
            ->when($this->subCategory, fn($q) => $q->where('sub_category', $this->subCategory))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->condition, fn($q) => $q->where('condition', $this->condition))
            ->orderBy('sub_category')
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Code',
            'Sous-Catégorie',
            'Catégorie',
            'Nom',
            'Marque',
            'Modèle',
            'N° Série',
            'Statut',
            'État',
            'Emplacement',
            'Date Achat',
            'Prix Achat (FC)',
            'Durée de Vie (mois)',
            'Fin de Vie',
            'Amortissement (%)',
            'Mois Restants',
            'Fin Garantie',
            'Prochaine Maintenance',
            'Fournisseur',
            'Assigné à',
            'Notes',
        ];
    }

    public function map($equipment): array
    {
        return [
            $equipment->code ?? '-',
            $equipment->sub_category_label,
            $equipment->category_label,
            $equipment->name,
            $equipment->brand ?? '-',
            $equipment->model ?? '-',
            $equipment->serial_number ?? '-',
            $equipment->status_label,
            $equipment->condition_label,
            $equipment->location ?? '-',
            $equipment->purchase_date?->format('d/m/Y') ?? '-',
            $equipment->purchase_price ? number_format($equipment->purchase_price, 0, ',', ' ') : '-',
            $equipment->lifespan_months ?? '-',
            $equipment->end_of_life_date?->format('d/m/Y') ?? '-',
            $equipment->lifespan_months ? $equipment->amortization_percent . '%' : '-',
            $equipment->remaining_months !== null ? $equipment->remaining_months : '-',
            $equipment->warranty_expiry?->format('d/m/Y') ?? '-',
            $equipment->next_maintenance?->format('d/m/Y') ?? '-',
            $equipment->supplier ?? '-',
            $equipment->assignedUser?->name ?? '-',
            $equipment->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50'],
                ],
            ],
        ];
    }
}
