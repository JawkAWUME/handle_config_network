<?php

namespace App\Exports;

use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithEvents,
    WithColumnWidths,
    ShouldAutoSize,
    WithProperties
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{
    Alignment,
    Border,
    Fill,
    Font,
    NumberFormat
};

class SiteExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithEvents,
    WithColumnWidths,
    ShouldAutoSize,
    WithProperties
{
    protected int $row = 0;

    public function query(): Builder
    {
        return Site::withCount([
                'firewalls',
                'routers',
                'switches',
                'servers',
                'users'
            ])
            ->with([
                'contacts' => function ($query) {
                    $query->select('id', 'site_id', 'name', 'role', 'phone', 'email');
                }
            ])
            ->orderBy('name');
    }

    public function map($site): array
    {
        $this->row++;
        
        // Contacts principaux
        $mainContacts = $site->contacts->take(2)->map(function ($contact) {
            return "{$contact->name} ({$contact->role})";
        })->implode("\n");
        
        // Calcul de l'occupation totale
        $totalDevices = $site->firewalls_count + $site->routers_count + 
                       $site->switches_count + $site->servers_count;
        
        // Statut du site basé sur l'occupation
        $status = $this->getSiteStatus($totalDevices, $site->capacity ?? 0);

        return [
            $this->row,
            $site->code ?? '—',
            $site->name,
            $site->city,
            $site->country ?? 'FR',
            $site->address ?? '—',
            $site->postal_code ?? '—',
            $site->phone ?? '—',
            $mainContacts ?: '—',
            $site->firewalls_count,
            $site->routers_count,
            $site->switches_count,
            $site->servers_count,
            $site->users_count,
            $totalDevices,
            $site->capacity ?? '∞',
            $status,
            $site->notes ?? '—',
            $site->created_at?->format('d/m/Y'),
            $site->updated_at?->format('d/m/Y'),
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Code',
            'Nom du Site',
            'Ville',
            'Pays',
            'Adresse',
            'Code Postal',
            'Téléphone',
            'Contacts',
            'Firewalls',
            'Routeurs',
            'Switches',
            'Serveurs',
            'Utilisateurs',
            'Total Équipements',
            'Capacité Max',
            'Statut',
            'Notes',
            'Date Création',
            'Dernière MàJ'
        ];
    }

    public function title(): string
    {
        return 'Inventaire des Sites';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // En-tête
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'] // emerald-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '047857'] // emerald-700
                    ]
                ]
            ],
            
            // Colonnes numériques
            'J:O' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            
            // Notes
            'R' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 10,
            'C' => 25,
            'D' => 15,
            'E' => 10,
            'F' => 25,
            'G' => 12,
            'H' => 15,
            'I' => 20,
            'J' => 10,
            'K' => 10,
            'L' => 10,
            'M' => 10,
            'N' => 10,
            'O' => 12,
            'P' => 12,
            'Q' => 12,
            'R' => 30,
            'S' => 12,
            'T' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Geler l'en-tête
                $sheet->freezePane('A2');
                
                // Filtrer automatiquement
                $sheet->setAutoFilter("A1:{$highestColumn}1");
                
                // Bordures pour toutes les données
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Lignes alternées
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->setStartColor(['rgb' => 'F0FDF4']); // emerald-50
                    }
                }
                
                // Format conditionnel pour le statut
                $this->applyConditionalFormatting($sheet, $highestRow);
                
                // Ajouter des totaux
                $this->addTotals($sheet, $highestRow, $highestColumn);
            },
        ];
    }

    public function properties(): array
    {
        return [
            'title' => $this->title(),
            'subject' => 'Inventaire des sites',
            'category' => 'Sites',
            'company' => config('app.name'),
            'manager' => 'Gestion des Sites',
            'created' => now()->timestamp,
        ];
    }

    /**
     * Méthodes utilitaires privées
     */
    private function getSiteStatus(int $totalDevices, $capacity): string
    {
        if ($capacity === 0 || $capacity === '∞') return 'Normal';
        
        $occupationRate = ($totalDevices / $capacity) * 100;
        
        if ($occupationRate >= 90) return 'Saturé';
        if ($occupationRate >= 70) return 'Élevé';
        if ($occupationRate >= 50) return 'Modéré';
        return 'Faible';
    }
    
    private function applyConditionalFormatting($sheet, $highestRow): void
    {
        // Format conditionnel pour la colonne Statut (Q)
        $statusRange = "Q2:Q{$highestRow}";
        
        // Vous pouvez ajouter des formats conditionnels ici
        // Exemple : colorier en rouge les sites "Saturé"
    }
    
    private function addTotals($sheet, $highestRow, $highestColumn): void
    {
        $totalRow = $highestRow + 2;
        
        // Fusionner les cellules pour le titre du total
        $sheet->mergeCells("A{$totalRow}:D{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", "TOTAUX");
        $sheet->getStyle("A{$totalRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']]
        ]);
        
        $totalRow++;
        
        // Calculer et afficher les totaux
        $totals = [
            'Firewalls' => "=SUM(J2:J{$highestRow})",
            'Routeurs' => "=SUM(K2:K{$highestRow})",
            'Switches' => "=SUM(L2:L{$highestRow})",
            'Serveurs' => "=SUM(M2:M{$highestRow})",
            'Total Équipements' => "=SUM(O2:O{$highestRow})",
        ];
        
        $row = $totalRow;
        foreach ($totals as $label => $formula) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $formula);
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }
    }
}