<?php

namespace App\Exports;

use App\Models\Firewall;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithColumnFormatting,
    WithEvents,
    ShouldAutoSize,
    WithProperties
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{
    NumberFormat,
    Border,
    Fill,
    Alignment
};

/**
 * ---------------------------------------------------------------------
 * FirewallExport
 * ---------------------------------------------------------------------
 * Export avancé des firewalls avec :
 * - Filtres dynamiques
 * - Mapping enrichi
 * - Styles professionnels
 * - Sécurité future
 * - Logs
 * - Helpers privés
 * - Extensible queue / multi-sheet
 * ---------------------------------------------------------------------
 */
class FirewallExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithColumnFormatting,
    WithEvents,
    WithProperties,
    ShouldAutoSize
{
    /* ===============================================================
     | Constantes métier
     =============================================================== */
    private const STATUS_ACTIVE = 'Actif';
    private const STATUS_INACTIVE = 'Inactif';

    private const DATE_FORMAT = 'Y-m-d H:i:s';

    /* ===============================================================
     | Filtres
     =============================================================== */
    protected ?string $vendor;
    protected ?string $status;
    protected ?Carbon $fromDate;
    protected ?Carbon $toDate;

    /* ===============================================================
     | Méta
     =============================================================== */
    protected int $rowCounter = 0;

    /* ===============================================================
     | Constructeur
     =============================================================== */
    public function __construct(
        ?string $vendor = null,
        ?string $status = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ) {
        $this->vendor   = $this->sanitizeString($vendor);
        $this->status   = $this->sanitizeString($status);
        $this->fromDate = $this->parseDate($fromDate);
        $this->toDate   = $this->parseDate($toDate);

        Log::info('[FirewallExport] Initialisé', [
            'vendor' => $this->vendor,
            'status' => $this->status,
            'from'   => $this->fromDate,
            'to'     => $this->toDate,
        ]);
    }

    /* ===============================================================
     | Query principale
     =============================================================== */
    public function query(): Builder
    {
        $query = Firewall::query()
            ->with([
                'site:id,name',
                'createdBy:id,name,email'
            ])
            ->orderByDesc('created_at');

        $this->applyVendorFilter($query);
        $this->applyStatusFilter($query);
        $this->applyDateFilters($query);

        return $query;
    }

    /* ===============================================================
     | Mapping ligne par ligne
     =============================================================== */
    public function map($firewall): array
    {
        $this->rowCounter++;

        return [
            $this->rowCounter,
            $firewall->id,
            $this->normalizeString($firewall->name),
            $this->formatIp($firewall->ip_address),
            $this->normalizeString($firewall->vendor),
            $this->normalizeString($firewall->model),
            $this->resolveSiteName($firewall),
            $this->resolveStatus($firewall),
            $this->resolveCreator($firewall),
            $this->formatDate($firewall->created_at),
            $this->formatDate($firewall->updated_at),
        ];
    }

    /* ===============================================================
     | Headings
     =============================================================== */
    public function headings(): array
    {
        return [
            '#',
            'ID',
            'Nom',
            'Adresse IP',
            'Vendor',
            'Modèle',
            'Site',
            'Statut',
            'Créé par',
            'Créé le',
            'Mis à jour le',
        ];
    }

    /* ===============================================================
     | Propriétés du fichier
     =============================================================== */
    public function properties(): array
    {
        return [
            'creator'        => 'Système Firewall',
            'lastModifiedBy' => 'Santeado',
            'title'          => 'Export Firewalls',
            'description'    => 'Export détaillé des firewalls',
            'subject'        => 'Infrastructure réseau',
            'keywords'       => 'firewall,security,network',
            'category'       => 'Audit',
        ];
    }

    /* ===============================================================
     | Nom de l’onglet
     =============================================================== */
    public function title(): string
    {
        return 'Firewalls';
    }

    /* ===============================================================
     | Styles Excel
     =============================================================== */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F172A'],
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    /* ===============================================================
     | Formats colonnes
     =============================================================== */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_DATE_DATETIME,
            'K' => NumberFormat::FORMAT_DATE_DATETIME,
        ];
    }

    /* ===============================================================
     | Events Excel
     =============================================================== */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:K1');

                // Alignement vertical global
                $sheet->getStyle('A:K')->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);
            },
        ];
    }

    /* ===============================================================
     | Helpers privés
     =============================================================== */
    private function sanitizeString(?string $value): ?string
    {
        return $value ? trim(strip_tags($value)) : null;
    }

    private function parseDate(?string $date): ?Carbon
    {
        try {
            return $date ? Carbon::parse($date) : null;
        } catch (\Throwable $e) {
            Log::warning('[FirewallExport] Date invalide', ['date' => $date]);
            return null;
        }
    }

    private function formatDate($date): string
    {
        return $date ? $date->format(self::DATE_FORMAT) : '—';
    }

    private function formatIp(?string $ip): string
    {
        return $ip ?? 'N/A';
    }

    private function normalizeString(?string $value): string
    {
        return $value ?: '—';
    }

    private function resolveSiteName($firewall): string
    {
        return $firewall->site?->name ?? 'Non assigné';
    }

    private function resolveCreator($firewall): string
    {
        return $firewall->createdBy?->name ?? 'System';
    }

    private function resolveStatus($firewall): string
    {
        return $firewall->status
            ? self::STATUS_ACTIVE
            : self::STATUS_INACTIVE;
    }

    private function applyVendorFilter(Builder $query): void
    {
        if ($this->vendor) {
            $query->where('vendor', $this->vendor);
        }
    }

    private function applyStatusFilter(Builder $query): void
    {
        if ($this->status !== null) {
            $query->where('status', $this->status === 'active');
        }
    }

    private function applyDateFilters(Builder $query): void
    {
        if ($this->fromDate) {
            $query->whereDate('created_at', '>=', $this->fromDate);
        }

        if ($this->toDate) {
            $query->whereDate('created_at', '<=', $this->toDate);
        }
    }
}
