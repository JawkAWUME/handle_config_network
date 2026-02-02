<?php

namespace App\Exports;

use App\Models\ConfigurationHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithEvents,
    WithColumnWidths,
    ShouldAutoSize,
    WithProperties,
    WithCustomStartCell
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

class ConfigurationHistoryExport implements
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
    protected ?string $type;
    protected ?string $action;
    protected ?Carbon $from;
    protected ?Carbon $to;
    protected int $row = 0;

    public function __construct(
        ?string $type = null,
        ?string $action = null,
        ?string $from = null,
        ?string $to = null
    ) {
        $this->type = $type;
        $this->action = $action;
        $this->from = $from ? Carbon::parse($from)->startOfDay() : null;
        $this->to = $to ? Carbon::parse($to)->endOfDay() : null;

        Log::info('[ConfigurationHistoryExport] Initialisé', [
            'type' => $type,
            'action' => $action,
            'période' => $from && $to ? "$from to $to" : 'all'
        ]);
    }

    /**
     * 🔍 Query optimisée avec eager loading et filtres
     */
    public function query()
    {
        $query = ConfigurationHistory::query()
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'name', 'email');
                }
            ])
            ->orderBy('created_at', 'desc');

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->action) {
            $query->where('action', $this->action);
        }

        if ($this->from && $this->to) {
            $query->whereBetween('created_at', [$this->from, $this->to]);
        } elseif ($this->from) {
            $query->where('created_at', '>=', $this->from);
        } elseif ($this->to) {
            $query->where('created_at', '<=', $this->to);
        }

        Log::info('[ConfigurationHistoryExport] Query exécutée', [
            'filtres' => [
                'type' => $this->type,
                'action' => $this->action,
                'from' => $this->from,
                'to' => $this->to
            ]
        ]);

        return $query;
    }

    /**
     * 🧠 Mapping optimisé avec traitement du JSON
     */
    public function map($history): array
    {
        $this->row++;
        
        // Traitement intelligent des valeurs JSON
        $oldValues = $this->formatJsonForExcel($history->old_values);
        $newValues = $this->formatJsonForExcel($history->new_values);
        
        // Détection du type de changement
        $changeType = $this->detectChangeType($history->action, $oldValues, $newValues);

        return [
            $this->row,
            $history->id,
            $this->formatType($history->type),
            $this->formatAction($history->action),
            $changeType,
            $oldValues,
            $newValues,
            $history->user?->name ?? 'Système',
            $history->user?->email ?? '',
            $history->ip_address,
            $history->user_agent ?? 'N/A',
            $history->created_at->format('d/m/Y H:i:s'),
            $history->created_at->diffForHumans(),
        ];
    }

    /**
     * 🏷️ Headers enrichis
     */
    public function headings(): array
    {
        return [
            '#',
            'ID',
            'Type',
            'Action',
            'Type de Changement',
            'Anciennes Valeurs',
            'Nouvelles Valeurs',
            'Utilisateur',
            'Email',
            'Adresse IP',
            'User Agent',
            'Date/Heure',
            'Il y a'
        ];
    }

    public function title(): string
    {
        $title = 'Historique des Configurations';
        
        if ($this->type || $this->action || $this->from || $this->to) {
            $filters = [];
            if ($this->type) $filters[] = "Type: {$this->type}";
            if ($this->action) $filters[] = "Action: {$this->action}";
            if ($this->from) $filters[] = "Du: {$this->from->format('d/m/Y')}";
            if ($this->to) $filters[] = "Au: {$this->to->format('d/m/Y')}";
            
            $title .= ' (' . implode(' | ', $filters) . ')';
        }
        
        return substr($title, 0, 31); // Limite Excel
    }

    /**
     * 🎨 Styles avancés
     */
    public function styles(Worksheet $sheet)
    {
        // Définir la plage de données
        $lastRow = $this->row + 1;
        $lastColumn = $sheet->getHighestColumn();
        $dataRange = "A2:{$lastColumn}{$lastRow}";
        
        return [
            // En-tête
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'] // slate-900
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
            ],
            
            // Colonnes de données JSON
            'F' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ],
            'G' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ],
            
            // Lignes alternées
            $dataRange => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB']
                    ]
                ]
            ],
            
            // Style pour les lignes paires
            'A2:' . $lastColumn . $lastRow => function($cell) {
                $row = $cell->getRow();
                if ($row > 1 && $row % 2 == 0) {
                    return [
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC']
                        ]
                    ];
                }
            }
        ];
    }

    /**
     * 📏 Largeurs de colonnes personnalisées
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,   // #
            'B' => 12,  // ID
            'C' => 15,  // Type
            'D' => 12,  // Action
            'E' => 20,  // Type de Changement
            'F' => 40,  // Anciennes Valeurs
            'G' => 40,  // Nouvelles Valeurs
            'H' => 20,  // Utilisateur
            'I' => 25,  // Email
            'J' => 15,  // IP
            'K' => 25,  // User Agent
            'L' => 20,  // Date/Heure
            'M' => 15,  // Il y a
        ];
    }

    /**
     * 🎯 Événements Excel
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Geler la première ligne
                $sheet->freezePane('A2');
                
                // Appliquer un filtre automatique
                $sheet->setAutoFilter("A1:{$highestColumn}1");
                
                // Ajuster la hauteur des lignes pour les JSON
                for ($row = 2; $row <= $highestRow; $row++) {
                    $oldValue = $sheet->getCell("F{$row}")->getValue();
                    $newValue = $sheet->getCell("G{$row}")->getValue();
                    
                    $lines = max(
                        substr_count($oldValue, "\n"),
                        substr_count($newValue, "\n")
                    );
                    
                    $height = max(20, 15 + ($lines * 12));
                    $sheet->getRowDimension($row)->setRowHeight($height);
                }
                
                // Ajouter un pied de page
                $sheet->mergeCells("A{$highestRow}:{$highestColumn}{$highestRow}");
                $sheet->setCellValue("A{$highestRow}", 
                    "Export généré le " . now()->format('d/m/Y à H:i:s') . 
                    " | Total: " . ($highestRow - 1) . " enregistrements"
                );
                $sheet->getStyle("A{$highestRow}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'color' => ['rgb' => '6B7280']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB']
                    ]
                ]);
                
                // Protection des cellules (en-têtes seulement)
                $sheet->getProtection()->setSheet(true);
                $sheet->getStyle("A2:{$highestColumn}{$highestRow}")
                    ->getProtection()
                    ->setLocked(false);
            },
        ];
    }

    /**
     * 📋 Propriétés du fichier Excel
     */
    public function properties(): array
    {
        return [
            'title' => $this->title(),
            'subject' => 'Historique des modifications de configuration',
            'category' => 'Audit',
            'company' => config('app.name'),
            'manager' => 'Système d\'Audit',
            'created' => now()->timestamp,
        ];
    }

    /**
     * 🔧 Méthodes utilitaires privées
     */
    private function formatJsonForExcel(?array $data): string
    {
        if (empty($data)) {
            return '—';
        }
        
        $formatted = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            $formatted[] = "• {$key}: " . (string) $value;
        }
        
        return implode("\n", $formatted);
    }
    
    private function formatType(string $type): string
    {
        $types = [
            'router' => 'Routeur',
            'switch' => 'Switch',
            'firewall' => 'Firewall',
            'site' => 'Site',
            'user' => 'Utilisateur',
        ];
        
        return $types[$type] ?? ucfirst($type);
    }
    
    private function formatAction(string $action): string
    {
        $actions = [
            'create' => 'Création',
            'update' => 'Modification',
            'delete' => 'Suppression',
            'restore' => 'Restauration',
        ];
        
        return $actions[$action] ?? ucfirst($action);
    }
    
    private function detectChangeType(string $action, string $old, string $new): string
    {
        if ($action === 'create') return 'Nouvel élément';
        if ($action === 'delete') return 'Suppression';
        if ($action === 'restore') return 'Restauration';
        
        if ($old === '—' && $new !== '—') return 'Ajout de données';
        if ($old !== '—' && $new === '—') return 'Suppression de données';
        
        return 'Modification';
    }
}