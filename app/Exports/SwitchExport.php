<?php

namespace App\Exports;

use App\Models\SwitchModel;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithEvents,
    WithColumnWidths,
    WithColumnFormatting,
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
    NumberFormat,
    Conditional
};

class SwitchExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithEvents,
    WithColumnWidths,
    WithColumnFormatting,
    ShouldAutoSize,
    WithProperties
{
    protected int $row = 0;
    protected ?string $vendor;
    protected ?string $site;

    public function __construct(?string $vendor = null, ?string $site = null)
    {
        $this->vendor = $vendor;
        $this->site = $site;
    }

    public function query(): Builder
    {
        $query = SwitchModel::with([
                'site' => function ($query) {
                    $query->select('id', 'name', 'code');
                },
                'ports' => function ($query) {
                    $query->select('id', 'switch_id', 'number', 'status', 'vlan_id', 'connected_to');
                }
            ])
            ->withCount([
                'ports',
                'ports as ports_up_count' => function ($query) {
                    $query->where('status', 'up');
                },
                'ports as ports_down_count' => function ($query) {
                    $query->where('status', 'down');
                },
                'ports as ports_trunk_count' => function ($query) {
                    $query->where('type', 'trunk');
                },
                'ports as ports_access_count' => function ($query) {
                    $query->where('type', 'access');
                }
            ])
            ->orderBy('site_id')
            ->orderBy('name');

        if ($this->vendor) {
            $query->where('vendor', 'like', "%{$this->vendor}%");
        }

        if ($this->site) {
            $query->whereHas('site', function ($query) {
                $query->where('name', 'like', "%{$this->site}%")
                      ->orWhere('code', 'like', "%{$this->site}%");
            });
        }

        return $query;
    }

    public function map($switch): array
    {
        $this->row++;
        
        // Calcul des statistiques
        $utilizationRate = $switch->ports_total > 0 ? 
            round(($switch->ports_used / $switch->ports_total) * 100, 1) : 0;
        
        $availabilityRate = $switch->ports_total > 0 ? 
            round(($switch->ports_up_count / $switch->ports_total) * 100, 1) : 0;
        
        // VLANs utilisés (exemple)
        $usedVlans = $this->getUsedVlans($switch);

        return [
            $this->row,
            $switch->id,
            $switch->name,
            $switch->ip_address,
            $switch->vendor,
            $switch->model,
            $switch->firmware_version ?? 'N/A',
            $switch->site?->name ?? '—',
            $switch->site?->code ?? '—',
            $switch->ports_total,
            $switch->ports_used,
            $switch->ports_up_count,
            $switch->ports_down_count,
            $switch->ports_trunk_count,
            $switch->ports_access_count,
            $utilizationRate . '%',
            $availabilityRate . '%',
            $usedVlans,
            $switch->stack_member ?? 'Standalone',
            $switch->status ?? 'unknown',
            $switch->created_at?->format('d/m/Y'),
            $switch->updated_at?->format('d/m/Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'ID',
            'Nom',
            'Adresse IP',
            'Fabricant',
            'Modèle',
            'Firmware',
            'Site',
            'Code Site',
            'Ports Totaux',
            'Ports Utilisés',
            'Ports Actifs',
            'Ports Inactifs',
            'Ports Trunk',
            'Ports Access',
            'Taux Utilisation',
            'Taux Disponibilité',
            'VLANs Utilisés',
            'Type Stack',
            'Statut',
            'Date Création',
            'Dernière MàJ'
        ];
    }

    public function title(): string
    {
        $title = 'Inventaire Switches';
        
        $filters = [];
        if ($this->vendor) $filters[] = "Fabricant: {$this->vendor}";
        if ($this->site) $filters[] = "Site: {$this->site}";
        
        if (!empty($filters)) {
            $title .= ' (' . implode(' | ', $filters) . ')';
        }
        
        return substr($title, 0, 31);
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
                    'startColor' => ['rgb' => '7C3AED'] // violet-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '5B21B6'] // violet-700
                    ]
                ]
            ],
            
            // Colonnes numériques
            'J:Q' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            
            // Taux
            'P:Q' => [
                'font' => ['bold' => true]
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_TEXT,
            'P' => NumberFormat::FORMAT_PERCENTAGE_00,
            'Q' => NumberFormat::FORMAT_PERCENTAGE_00,
            'U' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'V' => NumberFormat::FORMAT_DATE_DATETIME,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 10,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 12,
            'H' => 20,
            'I' => 10,
            'J' => 10,
            'K' => 10,
            'L' => 10,
            'M' => 10,
            'N' => 10,
            'O' => 10,
            'P' => 12,
            'Q' => 12,
            'R' => 15,
            'S' => 12,
            'T' => 12,
            'U' => 12,
            'V' => 16,
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
                            ->setStartColor(['rgb' => 'F5F3FF']); // violet-50
                    }
                }
                
                // Format conditionnel pour les taux
                $this->applyConditionalFormatting($sheet, $highestRow);
                
                // Graphiques de synthèse (formules)
                $this->addSummaryCharts($sheet, $highestRow, $highestColumn);
            },
        ];
    }

    public function properties(): array
    {
        return [
            'title' => $this->title(),
            'subject' => 'Inventaire des switches réseau',
            'category' => 'Infrastructure',
            'company' => config('app.name'),
            'manager' => 'Gestion Réseau',
            'created' => now()->timestamp,
            'keywords' => 'switch, réseau, ports, VLAN, infrastructure',
        ];
    }

    /**
     * Méthodes utilitaires privées
     */
    private function getUsedVlans($switch): string
    {
        // Récupérer les VLANs utilisés sur les ports
        $vlans = [];
        foreach ($switch->ports as $port) {
            if ($port->vlan_id && !in_array($port->vlan_id, $vlans)) {
                $vlans[] = $port->vlan_id;
            }
        }
        
        return !empty($vlans) ? implode(', ', array_slice($vlans, 0, 5)) . 
               (count($vlans) > 5 ? '...' : '') : '—';
    }
    
    private function applyConditionalFormatting($sheet, $highestRow): void
    {
        // Taux d'utilisation (P)
        $utilRange = "P2:P{$highestRow}";
        // Taux de disponibilité (Q)
        $availRange = "Q2:Q{$highestRow}";
        
        // Vous pouvez ajouter ici des formats conditionnels
        // Exemple : vert si disponibilité > 95%, rouge si < 80%
    }
    
    private function addSummaryCharts($sheet, $highestRow, $highestColumn): void
    {
        $summaryRow = $highestRow + 3;
        
        // Titre de la synthèse
        $sheet->mergeCells("A{$summaryRow}:D{$summaryRow}");
        $sheet->setCellValue("A{$summaryRow}", "SYNTHÈSE STATISTIQUE");
        $sheet->getStyle("A{$summaryRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '5B21B6']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDE9FE']]
        ]);
        
        // Statistiques calculées
        $stats = [
            'Total Switches' => $highestRow - 1,
            'Ports Totaux' => "=SUM(J2:J{$highestRow})",
            'Ports Utilisés' => "=SUM(K2:K{$highestRow})",
            'Ports Actifs' => "=SUM(L2:L{$highestRow})",
            'Taux Utilisation Moyen' => "=AVERAGE(P2:P{$highestRow})",
            'Taux Disponibilité Moyen' => "=AVERAGE(Q2:Q{$highestRow})",
        ];
        
        $row = $summaryRow + 1;
        foreach ($stats as $label => $value) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $value);
            
            // Format pour les pourcentages
            if (strpos($label, 'Taux') !== false) {
                $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('0.0%');
            } else {
                $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');
            }
            
            $row++;
        }
        
        // Répartition par fabricant
        $row += 2;
        $sheet->setCellValue("A{$row}", "RÉPARTITION PAR FABRICANT");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        
        // Ici vous pourriez ajouter des formules pour compter par fabricant
        // Exemple: =COUNTIF(E2:E{$highestRow}, "Cisco")
    }
}