<?php

namespace App\Exports;

use App\Models\Router;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithColumnFormatting,
    WithEvents,
    WithColumnWidths,
    WithProperties,
    ShouldAutoSize,
    WithConditionalFormats
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{
    Alignment,
    Border,
    Color,
    Fill,
    Font,
    NumberFormat,
    Conditional
};

class RouterExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithColumnFormatting,
    WithEvents,
    WithColumnWidths,
    WithProperties,
    ShouldAutoSize,
    WithConditionalFormats
{
    protected ?string $vendor;
    protected ?Carbon $from;
    protected ?Carbon $to;
    protected ?string $status;
    protected int $row = 0;

    public function __construct(
        ?string $vendor = null,
        ?string $from = null,
        ?string $to = null,
        ?string $status = null
    ) {
        $this->vendor = $vendor;
        $this->from = $from ? Carbon::parse($from)->startOfDay() : null;
        $this->to = $to ? Carbon::parse($to)->endOfDay() : null;
        $this->status = $status;

        Log::info('[RouterExport] Initialisé', compact('vendor', 'from', 'to', 'status'));
    }

    public function query(): Builder
    {
        $query = Router::with([
                'site' => function ($query) {
                    $query->select('id', 'name', 'city');
                },
                'interfaces' => function ($query) {
                    $query->select('id', 'router_id', 'status');
                }
            ])
            ->withCount([
                'interfaces',
                'interfaces as interfaces_up_count' => function ($query) {
                    $query->where('status', 'up');
                },
                'interfaces as interfaces_down_count' => function ($query) {
                    $query->where('status', 'down');
                }
            ])
            ->orderBy('vendor')
            ->orderBy('name');

        if ($this->vendor) {
            $query->where('vendor', 'like', "%{$this->vendor}%");
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->from && $this->to) {
            $query->whereBetween('created_at', [$this->from, $this->to]);
        } elseif ($this->from) {
            $query->where('created_at', '>=', $this->from);
        } elseif ($this->to) {
            $query->where('created_at', '<=', $this->to);
        }

        return $query;
    }

    public function map($router): array
    {
        $this->row++;
        
        // Calcul de la santé du routeur
        $healthScore = $this->calculateHealthScore($router);
        $healthStatus = $this->getHealthStatus($healthScore);

        return [
            $this->row,
            $router->id,
            $router->name,
            $router->ip_address,
            $router->vendor,
            $router->model,
            $router->firmware_version ?? 'N/A',
            $router->site?->name ?? '—',
            $router->site?->city ?? '—',
            $router->interfaces_count ?? 0,
            $router->interfaces_up_count ?? 0,
            $router->interfaces_down_count ?? 0,
            $healthScore . '%',
            $healthStatus,
            $router->status ?? 'unknown',
            $router->created_at?->format('d/m/Y H:i'),
            $router->updated_at?->format('d/m/Y H:i'),
            $this->getLastBackupStatus($router),
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
            'Ville',
            'Interfaces Totales',
            'Interfaces Actives',
            'Interfaces Inactives',
            'Score de Santé',
            'État de Santé',
            'Statut',
            'Date Création',
            'Dernière MàJ',
            'Dernier Backup'
        ];
    }

    public function title(): string
    {
        $title = 'Inventaire Routeurs';
        
        $filters = [];
        if ($this->vendor) $filters[] = "Fabricant: {$this->vendor}";
        if ($this->status) $filters[] = "Statut: {$this->status}";
        if ($this->from) $filters[] = "Du: {$this->from->format('d/m/Y')}";
        if ($this->to) $filters[] = "Au: {$this->to->format('d/m/Y')}";
        
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
                    'startColor' => ['rgb' => '3B82F6'] // blue-500
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '1E40AF'] // blue-700
                    ]
                ]
            ],
            
            // Alignement des colonnes numériques
            'J:L' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            
            // Score de santé
            'M' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'font' => ['bold' => true]
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_TEXT, // IP comme texte
            'M' => NumberFormat::FORMAT_PERCENTAGE,
            'O' => NumberFormat::FORMAT_TEXT,
            'P' => NumberFormat::FORMAT_DATE_DATETIME,
            'Q' => NumberFormat::FORMAT_DATE_DATETIME,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 12,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 12,
            'H' => 20,
            'I' => 15,
            'J' => 10,
            'K' => 10,
            'L' => 10,
            'M' => 12,
            'N' => 15,
            'O' => 12,
            'P' => 16,
            'Q' => 16,
            'R' => 15,
        ];
    }

    public function conditionalFormats(): array
    {
        return [
            // Score de santé (vert > 80%, orange > 60%, rouge sinon)
            'M2:M1000' => [
                new Conditional([
                    'conditionType' => Conditional::CONDITION_CELLIS,
                    'operatorType' => Conditional::OPERATOR_GREATERTHANOREQUAL,
                    'comparisonValue' => '80',
                    'style' => [
                        'font' => [
                            'color' => ['rgb' => '065F46'] // green-900
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'D1FAE5'] // green-100
                        ]
                    ]
                ]),
                new Conditional([
                    'conditionType' => Conditional::CONDITION_CELLIS,
                    'operatorType' => Conditional::OPERATOR_BETWEEN,
                    'comparisonValue' => '60-79',
                    'style' => [
                        'font' => [
                            'color' => ['rgb' => '92400E'] // amber-900
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'FEF3C7'] // amber-100
                        ]
                    ]
                ]),
                new Conditional([
                    'conditionType' => Conditional::CONDITION_CELLIS,
                    'operatorType' => Conditional::OPERATOR_LESSTHAN,
                    'comparisonValue' => '60',
                    'style' => [
                        'font' => [
                            'color' => ['rgb' => '991B1B'] // red-900
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'FEE2E2'] // red-100
                        ]
                    ]
                ])
            ],
            
            // Interfaces inactives (rouge si > 0)
            'L2:L1000' => [
                new Conditional([
                    'conditionType' => Conditional::CONDITION_CELLIS,
                    'operatorType' => Conditional::OPERATOR_GREATERTHAN,
                    'comparisonValue' => '0',
                    'style' => [
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'DC2626'] // red-600
                        ]
                    ]
                ])
            ]
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
                
                // Bordures pour toutes les cellules
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
                            ->setStartColor(['rgb' => 'F8FAFC']); // slate-50
                    }
                }
                
                // Résumé statistique
                $this->addSummary($sheet, $highestRow, $highestColumn);
            },
        ];
    }

    public function properties(): array
    {
        return [
            'title' => $this->title(),
            'subject' => 'Inventaire des routeurs réseau',
            'category' => 'Infrastructure',
            'company' => config('app.name'),
            'manager' => 'Gestion Infrastructure',
            'created' => now()->timestamp,
            'keywords' => 'routeur, réseau, infrastructure, inventaire',
        ];
    }

    /**
     * Méthodes utilitaires privées
     */
    private function calculateHealthScore($router): int
    {
        $score = 100;
        
        // Pénalités
        if ($router->status !== 'active') $score -= 30;
        if ($router->interfaces_down_count > 0) {
            $downRate = ($router->interfaces_down_count / $router->interfaces_count) * 100;
            $score -= min(40, $downRate);
        }
        if (empty($router->firmware_version)) $score -= 10;
        
        return max(0, min(100, $score));
    }
    
    private function getHealthStatus(int $score): string
    {
        if ($score >= 80) return 'Excellent';
        if ($score >= 60) return 'Bon';
        if ($score >= 40) return 'Modéré';
        if ($score >= 20) return 'Critique';
        return 'Défaillant';
    }
    
    private function getLastBackupStatus($router): string
    {
        // Logique pour déterminer le statut du backup
        // À adapter selon votre système
        return 'À implémenter';
    }
    
    private function addSummary($sheet, $highestRow, $highestColumn): void
    {
        $summaryRow = $highestRow + 2;
        
        $sheet->mergeCells("A{$summaryRow}:D{$summaryRow}");
        $sheet->setCellValue("A{$summaryRow}", "RÉSUMÉ STATISTIQUE");
        $sheet->getStyle("A{$summaryRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E40AF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']]
        ]);
        
        $summaryRow++;
        $sheet->setCellValue("A{$summaryRow}", "Total Routeurs:");
        $sheet->setCellValue("B{$summaryRow}", $highestRow - 1);
        
        $summaryRow++;
        $sheet->setCellValue("A{$summaryRow}", "Export généré le:");
        $sheet->setCellValue("B{$summaryRow}", now()->format('d/m/Y à H:i:s'));
        
        $summaryRow++;
        $sheet->setCellValue("A{$summaryRow}", "Utilisateur:");
        $sheet->setCellValue("B{$summaryRow}", auth()->user()?->name ?? 'Système');
    }
}