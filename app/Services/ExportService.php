<?php

namespace App\Services;

use App\Services\Contracts\ExportServiceInterface;
use App\Repositories\ConfigurationHistoryRepository;
use App\Repositories\AccessLogRepository;
use App\Repositories\DeviceRepository;
use App\Exports\{
    ConfigurationHistoryExport,
    RouterExport,
    FirewallExport,
    SwitchExport,
    SiteExport,
    AccessLogExport
};
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class ExportService implements ExportServiceInterface
{
    protected array $exportTypes = [
        'configuration_history' => ConfigurationHistoryExport::class,
        'routers' => RouterExport::class,
        'firewalls' => FirewallExport::class,
        'switches' => SwitchExport::class,
        'sites' => SiteExport::class,
        'access_logs' => AccessLogExport::class,
    ];

    public function __construct(
        protected ConfigurationHistoryRepository $configurationRepository,
        protected AccessLogRepository $accessLogRepository,
        protected DeviceRepository $deviceRepository
    ) {}

    public function exportToExcel(string $type, array $options = []): string
    {
        try {
            Log::info("[ExportService] Export Excel demandé", ['type' => $type, 'options' => $options]);
            
            if (!array_key_exists($type, $this->exportTypes)) {
                throw new \InvalidArgumentException("Type d'export non supporté: {$type}");
            }
            
            $exportClass = $this->exportTypes[$type];
            $timestamp = now()->format('Ymd_His');
            $filename = "export_{$type}_{$timestamp}.xlsx";
            $filepath = "exports/excel/{$filename}";
            
            // Appliquer les filtres
            $exportInstance = new $exportClass($options);
            
            // Générer le fichier Excel
            Excel::store($exportInstance, $filepath);
            
            $fullPath = Storage::path($filepath);
            $fileSize = filesize($fullPath);
            
            Log::info("[ExportService] Export Excel généré", [
                'type' => $type,
                'filename' => $filename,
                'size' => $this->formatBytes($fileSize),
                'user_id' => auth()->id(),
            ]);
            
            // Journaliser l'accès
            $this->logExportActivity($type, 'excel', $filename, $fileSize);
            
            return $fullPath;
            
        } catch (Exception $e) {
            Log::error("[ExportService] Erreur lors de l'export Excel: " . $e->getMessage());
            throw $e;
        }
    }

    public function exportToPDF(string $type, array $options = []): string
    {
        try {
            Log::info("[ExportService] Export PDF demandé", ['type' => $type]);
            
            // Préparer les données selon le type
            $data = $this->prepareExportData($type, $options);
            $template = "exports.{$type}";
            
            // Vérifier si le template existe
            if (!view()->exists($template)) {
                $template = 'exports.default';
            }
            
            // Options PDF
            $pdfOptions = array_merge([
                'paper' => 'A4',
                'orientation' => 'portrait',
                'encoding' => 'UTF-8',
            ], $options['pdf_options'] ?? []);
            
            // Générer le PDF
            $pdf = Pdf::loadView($template, array_merge($data, [
                'title' => $this->getExportTitle($type, $options),
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'generated_by' => auth()->user()?->name ?? 'System',
            ]));
            
            $timestamp = now()->format('Ymd_His');
            $filename = "export_{$type}_{$timestamp}.pdf";
            $filepath = "exports/pdf/{$filename}";
            
            // Sauvegarder le fichier
            Storage::put($filepath, $pdf->output());
            
            $fullPath = Storage::path($filepath);
            $fileSize = Storage::size($filepath);
            
            Log::info("[ExportService] Export PDF généré", [
                'type' => $type,
                'filename' => $filename,
                'size' => $this->formatBytes($fileSize),
                'user_id' => auth()->id(),
            ]);
            
            // Journaliser l'accès
            $this->logExportActivity($type, 'pdf', $filename, $fileSize);
            
            return $fullPath;
            
        } catch (Exception $e) {
            Log::error("[ExportService] Erreur lors de l'export PDF: " . $e->getMessage());
            throw $e;
        }
    }

    public function exportConfigurationsToText(array $options = []): string
    {
        try {
            Log::info("[ExportService] Export texte des configurations demandé");
            
            $deviceType = $options['device_type'] ?? null;
            $deviceId = $options['device_id'] ?? null;
            
            if ($deviceId && $deviceType) {
                // Export d'un seul appareil
                $config = $this->getDeviceConfiguration($deviceType, $deviceId);
                $content = $this->formatConfigurationText($config, $deviceType, $deviceId);
                $filename = "config_{$deviceType}_{$deviceId}_" . now()->format('Ymd_His') . ".txt";
            } else {
                // Export groupé
                $content = $this->exportAllConfigurations($options);
                $filename = "configurations_all_" . now()->format('Ymd_His') . ".txt";
            }
            
            $filepath = "exports/configurations/{$filename}";
            Storage::put($filepath, $content);
            
            $fileSize = strlen($content);
            
            Log::info("[ExportService] Export texte des configurations généré", [
                'device_type' => $deviceType,
                'device_id' => $deviceId,
                'filename' => $filename,
                'size' => $this->formatBytes($fileSize),
                'user_id' => auth()->id(),
            ]);
            
            // Journaliser l'accès
            $this->logExportActivity('configurations', 'text', $filename, $fileSize);
            
            return Storage::path($filepath);
            
        } catch (Exception $e) {
            Log::error("[ExportService] Erreur lors de l'export texte: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateAuditReport(array $options = []): string
    {
        try {
            Log::info("[ExportService] Génération du rapport d'audit");
            
            $startDate = $options['start_date'] ?? now()->subMonth()->format('Y-m-d');
            $endDate = $options['end_date'] ?? now()->format('Y-m-d');
            $reportType = $options['report_type'] ?? 'comprehensive';
            
            // Collecter les données d'audit
            $auditData = $this->collectAuditData($startDate, $endDate, $reportType);
            
            // Générer le rapport PDF
            $pdf = Pdf::loadView('reports.audit', array_merge($auditData, [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'report_type' => $reportType,
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'generated_by' => auth()->user()?->name ?? 'System',
            ]));
            
            $timestamp = now()->format('Ymd_His');
            $filename = "audit_report_{$timestamp}_{$reportType}.pdf";
            $filepath = "reports/audit/{$filename}";
            
            Storage::put($filepath, $pdf->output());
            
            Log::info("[ExportService] Rapport d'audit généré", [
                'period' => "{$startDate} - {$endDate}",
                'type' => $reportType,
                'filename' => $filename,
                'user_id' => auth()->id(),
            ]);
            
            return Storage::path($filepath);
            
        } catch (Exception $e) {
            Log::error("[ExportService] Erreur lors de la génération du rapport d'audit: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateStatisticsReport(array $options = []): string
    {
        try {
            Log::info("[ExportService] Génération du rapport statistique");
            
            $period = $options['period'] ?? 'monthly';
            $level = $options['level'] ?? 'overview';
            
            // Calculer les statistiques
            $stats = $this->calculateStatistics($period, $level);
            
            // Générer le rapport
            $pdf = Pdf::loadView('reports.statistics', array_merge($stats, [
                'period' => $period,
                'level' => $level,
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'generated_by' => auth()->user()?->name ?? 'System',
            ]));
            
            $timestamp = now()->format('Ymd_His');
            $filename = "statistics_report_{$period}_{$level}_{$timestamp}.pdf";
            $filepath = "reports/statistics/{$filename}";
            
            Storage::put($filepath, $pdf->output());
            
            Log::info("[ExportService] Rapport statistique généré", [
                'period' => $period,
                'level' => $level,
                'filename' => $filename,
                'user_id' => auth()->id(),
            ]);
            
            return Storage::path($filepath);
            
        } catch (Exception $e) {
            Log::error("[ExportService] Erreur lors de la génération du rapport statistique: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateInventoryReport(array $options = []): string
    {
        try {
            Log::info("[ExportService] Génération du rapport d'inventaire");
            
            $siteId = $options['site_id'] ?? null;
            $deviceType = $options['device_type'] ?? null;
            $format = $options['format'] ?? 'pdf';
            
            // Collecter l'inventaire
            $inventory = $this->collectInventoryData($siteId, $deviceType);
            
            if ($format === 'excel') {
                // Générer un export Excel
                $exportClass = $this->exportTypes['sites'] ?? SiteExport::class;
                $exportInstance = new $exportClass($options);
                
                $timestamp = now()->format('Ymd_His');
                $filename = "inventory_{$timestamp}.xlsx";
                $filepath = "reports/inventory/{$filename}";
                
                Excel::store($exportInstance, $filepath);
            } else {
                // Générer un PDF
                $pdf = Pdf::loadView('reports.inventory', array_merge($inventory, [
                    'site_filter' => $siteId,
                    'device_filter' => $deviceType,
                    'generated_at' => now()->format('d/m/Y H:i:s'),
                    'generated_by' => auth()->user()?->name ?? 'System',
                ]));
                
                $timestamp = now()->format('Ymd_His');
                $filename = "inventory_report_{$timestamp}.pdf";
                $filepath = "reports/inventory/{$filename}";
                
                Storage::put($filepath, $pdf->output());
            }
            
            Log::info("[ExportService] Rapport d'inventaire généré", [
                'site_id' => $siteId,
                'device_type' => $deviceType,
                'format' => $format,
                'filename' => $filename,
                'user_id' => auth()->id(),
            ]);
            
            return Storage::path($filepath);
            
        } catch (Exception $e) {
            Log::error("[ExportService] Erreur lors de la génération du rapport d'inventaire: " . $e->getMessage());
            throw $e;
        }
    }

    private function prepareExportData(string $type, array $options): array
    {
        return match($type) {
            'configuration_history' => [
                'histories' => $this->configurationRepository->getFiltered($options),
                'filters' => $options,
            ],
            'access_logs' => [
                'logs' => $this->accessLogRepository->getFiltered($options),
                'filters' => $options,
            ],
            'routers', 'firewalls', 'switches' => [
                'devices' => $this->deviceRepository->getByType($type, $options),
                'filters' => $options,
            ],
            'sites' => [
                'sites' => $this->deviceRepository->getSitesWithInventory($options),
                'filters' => $options,
            ],
            default => [],
        };
    }

    private function getDeviceConfiguration(string $deviceType, int $deviceId): ?array
    {
        $modelClass = $this->getModelClass($deviceType);
        
        if (!$modelClass) {
            throw new \InvalidArgumentException("Type d'appareil non supporté: {$deviceType}");
        }
        
        $device = $modelClass::find($deviceId);
        
        if (!$device) {
            throw new \Exception("Appareil non trouvé: {$deviceType} #{$deviceId}");
        }
        
        return [
            'device' => $device->toArray(),
            'configuration' => $device->configuration ?? null,
            'interfaces' => $device->interfaces ?? null,
            'security_policies' => $device->security_policies ?? null,
        ];
    }

    private function formatConfigurationText(array $config, string $deviceType, int $deviceId): string
    {
        $output = [];
        $output[] = "=" . str_repeat("=", 60);
        $output[] = "CONFIGURATION EXPORT";
        $output[] = "=" . str_repeat("=", 60);
        $output[] = sprintf("Device Type: %s", strtoupper($deviceType));
        $output[] = sprintf("Device ID: %d", $deviceId);
        $output[] = sprintf("Device Name: %s", $config['device']['name'] ?? 'N/A');
        $output[] = sprintf("Generated: %s", now()->format('Y-m-d H:i:s'));
        $output[] = sprintf("Generated By: %s", auth()->user()?->name ?? 'System');
        $output[] = str_repeat("-", 60);
        
        if (!empty($config['configuration'])) {
            $output[] = "RAW CONFIGURATION:";
            $output[] = str_repeat("-", 30);
            $output[] = is_array($config['configuration']) ? 
                json_encode($config['configuration'], JSON_PRETTY_PRINT) : 
                $config['configuration'];
            $output[] = "";
        }
        
        if (!empty($config['interfaces'])) {
            $output[] = "INTERFACES:";
            $output[] = str_repeat("-", 30);
            $interfaces = is_string($config['interfaces']) ? 
                json_decode($config['interfaces'], true) : $config['interfaces'];
            
            if ($interfaces) {
                foreach ($interfaces as $index => $interface) {
                    $output[] = sprintf("[%d] %s: %s", $index, $interface['name'] ?? 'Interface', $interface['ip_address'] ?? 'N/A');
                }
            }
            $output[] = "";
        }
        
        $output[] = "END OF CONFIGURATION";
        $output[] = "=" . str_repeat("=", 60);
        
        return implode("\n", $output);
    }

    private function exportAllConfigurations(array $options): string
    {
        $output = [];
        $output[] = "=" . str_repeat("=", 80);
        $output[] = "BULK CONFIGURATION EXPORT";
        $output[] = "=" . str_repeat("=", 80);
        $output[] = sprintf("Generated: %s", now()->format('Y-m-d H:i:s'));
        $output[] = sprintf("Generated By: %s", auth()->user()?->name ?? 'System');
        $output[] = sprintf("Filters: %s", json_encode($options, JSON_PRETTY_PRINT));
        $output[] = "";
        
        foreach (['firewall', 'router', 'switch'] as $deviceType) {
            $devices = $this->deviceRepository->getByType($deviceType, $options);
            
            if ($devices->isEmpty()) {
                continue;
            }
            
            $output[] = str_repeat("#", 80);
            $output[] = strtoupper($deviceType) . " CONFIGURATIONS";
            $output[] = str_repeat("#", 80);
            $output[] = "";
            
            foreach ($devices as $device) {
                $output[] = str_repeat("-", 60);
                $output[] = sprintf("Device: %s (ID: %d)", $device->name, $device->id);
                $output[] = sprintf("Site: %s", $device->site?->name ?? 'N/A');
                $output[] = sprintf("Model: %s %s", $device->brand, $device->model);
                $output[] = sprintf("Status: %s", $device->status ? 'Active' : 'Inactive');
                $output[] = str_repeat("-", 60);
                
                if (!empty($device->configuration)) {
                    $output[] = "Configuration:";
                    $output[] = is_array($device->configuration) ? 
                        json_encode($device->configuration, JSON_PRETTY_PRINT) : 
                        $device->configuration;
                } else {
                    $output[] = "No configuration available";
                }
                
                $output[] = "";
            }
        }
        
        $output[] = "=" . str_repeat("=", 80);
        $output[] = "END OF BULK EXPORT";
        $output[] = "=" . str_repeat("=", 80);
        
        return implode("\n", $output);
    }

    private function collectAuditData(string $startDate, string $endDate, string $reportType): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        return [
            'access_logs' => $this->accessLogRepository->getBetweenDates($start, $end),
            'configuration_changes' => $this->configurationRepository->getBetweenDates($start, $end),
            'backup_activities' => $this->configurationRepository->getBackupsBetweenDates($start, $end),
            'summary' => [
                'total_access_logs' => $this->accessLogRepository->countBetweenDates($start, $end),
                'total_config_changes' => $this->configurationRepository->countBetweenDates($start, $end),
                'total_backups' => $this->configurationRepository->countBackupsBetweenDates($start, $end),
                'unique_users' => $this->accessLogRepository->countUniqueUsersBetweenDates($start, $end),
                'most_active_user' => $this->accessLogRepository->getMostActiveUserBetweenDates($start, $end),
            ],
        ];
    }

    private function calculateStatistics(string $period, string $level): array
    {
        $endDate = now();
        $startDate = match($period) {
            'daily' => $endDate->copy()->subDay(),
            'weekly' => $endDate->copy()->subWeek(),
            'monthly' => $endDate->copy()->subMonth(),
            'quarterly' => $endDate->copy()->subMonths(3),
            'yearly' => $endDate->copy()->subYear(),
            default => $endDate->copy()->subMonth(),
        };
        
        return [
            'period' => $period,
            'level' => $level,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'device_stats' => $this->deviceRepository->getStatisticsByPeriod($startDate, $endDate),
            'backup_stats' => $this->configurationRepository->getBackupStatistics($startDate, $endDate),
            'user_activity' => $this->accessLogRepository->getUserActivityStats($startDate, $endDate),
            'trends' => $this->calculateTrends($startDate, $endDate),
        ];
    }

    private function collectInventoryData(?int $siteId, ?string $deviceType): array
    {
        return [
            'sites' => $this->deviceRepository->getSitesInventory($siteId),
            'devices_by_type' => [
                'firewalls' => $this->deviceRepository->getDevicesByType('firewall', $siteId),
                'routers' => $this->deviceRepository->getDevicesByType('router', $siteId),
                'switches' => $this->deviceRepository->getDevicesByType('switch', $siteId),
            ],
            'totals' => [
                'total_sites' => $this->deviceRepository->countSites($siteId),
                'total_firewalls' => $this->deviceRepository->countDevices('firewall', $siteId),
                'total_routers' => $this->deviceRepository->countDevices('router', $siteId),
                'total_switches' => $this->deviceRepository->countDevices('switch', $siteId),
                'total_devices' => $this->deviceRepository->countAllDevices($siteId),
            ],
            'filters' => [
                'site_id' => $siteId,
                'device_type' => $deviceType,
            ],
        ];
    }

    private function getModelClass(string $deviceType): ?string
    {
        return match(strtolower($deviceType)) {
            'firewall' => \App\Models\Firewall::class,
            'router' => \App\Models\Router::class,
            'switch' => \App\Models\SwitchModel::class,
            default => null,
        };
    }

    private function getExportTitle(string $type, array $options): string
    {
        $titles = [
            'configuration_history' => 'Historique des Configurations',
            'access_logs' => 'Journal d\'Accès',
            'routers' => 'Inventaire des Routeurs',
            'firewalls' => 'Inventaire des Firewalls',
            'switches' => 'Inventaire des Switches',
            'sites' => 'Inventaire des Sites',
        ];
        
        $title = $titles[$type] ?? 'Export';
        
        if (!empty($options['title'])) {
            $title = $options['title'];
        }
        
        return $title;
    }

    private function calculateTrends(Carbon $startDate, Carbon $endDate): array
    {
        // Calculer les tendances sur la période
        $previousPeriod = [
            'start' => $startDate->copy()->sub($endDate->diff($startDate)),
            'end' => $startDate->copy(),
        ];
        
        $currentStats = $this->deviceRepository->getStatisticsByPeriod($startDate, $endDate);
        $previousStats = $this->deviceRepository->getStatisticsByPeriod(
            $previousPeriod['start'], 
            $previousPeriod['end']
        );
        
        $trends = [];
        
        foreach ($currentStats as $key => $currentValue) {
            $previousValue = $previousStats[$key] ?? 0;
            
            if ($previousValue > 0) {
                $change = (($currentValue - $previousValue) / $previousValue) * 100;
            } else {
                $change = $currentValue > 0 ? 100 : 0;
            }
            
            $trends[$key] = [
                'current' => $currentValue,
                'previous' => $previousValue,
                'change' => round($change, 2),
                'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
            ];
        }
        
        return $trends;
    }

    private function logExportActivity(string $type, string $format, string $filename, int $fileSize): void
    {
        try {
            $this->accessLogRepository->create([
                'user_id' => auth()->id(),
                'action' => 'export',
                'parameters' => [
                    'type' => $type,
                    'format' => $format,
                    'filename' => $filename,
                    'size' => $fileSize,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'result' => 'success',
            ]);
        } catch (Exception $e) {
            Log::error("[ExportService] Échec de la journalisation de l'export: " . $e->getMessage());
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}