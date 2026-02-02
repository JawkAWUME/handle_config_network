<?php
// app/Actions/ExportConfigurationData.php

namespace App\Actions;

use App\Models\Site;
use App\Models\SwitchModel;
use App\Models\Router;
use App\Models\Firewall;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Carbon\Carbon;

class ExportConfigurationData
{
    /**
     * Exporter les données au format Excel
     */
    public function exportToExcel(array $options = []): string
    {
        $type = $options['type'] ?? 'all';
        $siteId = $options['site_id'] ?? null;
        $date = Carbon::now()->format('Y-m-d_His');
        
        $filename = "export_configuration_{$type}_{$date}.xlsx";
        $filepath = "exports/{$filename}";
        
        switch ($type) {
            case 'switches':
                Excel::store(new \App\Exports\SwitchesExport($siteId), $filepath);
                break;
            case 'routers':
                Excel::store(new \App\Exports\RoutersExport($siteId), $filepath);
                break;
            case 'firewalls':
                Excel::store(new \App\Exports\FirewallsExport($siteId), $filepath);
                break;
            case 'sites':
                Excel::store(new \App\Exports\SitesExport(), $filepath);
                break;
            case 'all':
            default:
                $this->exportAllToExcel($filepath);
                break;
        }
        
        return $filepath;
    }

    /**
     * Exporter tous les équipements dans un seul fichier Excel avec plusieurs onglets
     */
    protected function exportAllToExcel(string $filepath): void
    {
        Excel::store(new class {
            public function __construct()
            {
                $this->switches = SwitchModel::with('site')->get();
                $this->routers = Router::with('site')->get();
                $this->firewalls = Firewall::with('site')->get();
                $this->sites = Site::withCount(['switches', 'routers', 'firewalls'])->get();
            }
            
            public function sheets(): array
            {
                return [
                    'Sites' => new \App\Exports\SitesExport(),
                    'Switches' => new \App\Exports\SwitchesExport(),
                    'Routers' => new \App\Exports\RoutersExport(),
                    'Firewalls' => new \App\Exports\FirewallsExport(),
                ];
            }
        }, $filepath);
    }

    /**
     * Exporter les données au format PDF
     */
    public function exportToPDF(array $options = []): string
    {
        $type = $options['type'] ?? 'all';
        $siteId = $options['site_id'] ?? null;
        $date = Carbon::now()->format('Y-m-d_His');
        
        $filename = "export_configuration_{$type}_{$date}.pdf";
        $filepath = "exports/{$filename}";
        
        $data = $this->prepareExportData($type, $siteId);
        
        $pdf = PDF::loadView('exports.configuration-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'helvetica',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true
            ]);
        
        Storage::put($filepath, $pdf->output());
        
        return $filepath;
    }

    /**
     * Exporter les configurations complètes dans des fichiers texte
     */
    public function exportConfigurationsToText(array $options = []): string
    {
        $deviceIds = $options['device_ids'] ?? [];
        $deviceType = $options['device_type'] ?? 'all';
        $siteId = $options['site_id'] ?? null;
        
        $date = Carbon::now()->format('Y-m-d_His');
        $zipFilename = "configurations_export_{$date}.zip";
        $zipPath = Storage::path("exports/{$zipFilename}");
        
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Exporter les switches
            if (in_array($deviceType, ['all', 'switch'])) {
                $this->addDevicesToZip($zip, SwitchModel::class, $deviceIds, $siteId, 'switches');
            }
            
            // Exporter les routeurs
            if (in_array($deviceType, ['all', 'router'])) {
                $this->addDevicesToZip($zip, Router::class, $deviceIds, $siteId, 'routers');
            }
            
            // Exporter les firewalls
            if (in_array($deviceType, ['all', 'firewall'])) {
                $this->addDevicesToZip($zip, Firewall::class, $deviceIds, $siteId, 'firewalls');
            }
            
            // Ajouter un fichier README
            $readmeContent = $this->generateReadme();
            $zip->addFromString('README.txt', $readmeContent);
            
            $zip->close();
        }
        
        return "exports/{$zipFilename}";
    }

    /**
     * Ajouter les configurations d'un type d'appareil au ZIP
     */
    protected function addDevicesToZip(ZipArchive $zip, string $modelClass, array $deviceIds, ?int $siteId, string $folderName): void
    {
        $query = $modelClass::query();
        
        if (!empty($deviceIds)) {
            $query->whereIn('id', $deviceIds);
        }
        
        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        
        $devices = $query->with('site')->get();
        
        foreach ($devices as $device) {
            if ($device->configuration) {
                $filename = sprintf(
                    '%s/%s_%s_%s.txt',
                    $folderName,
                    $device->site->name ?? 'no_site',
                    $device->name,
                    Carbon::parse($device->updated_at)->format('Y-m-d')
                );
                
                $content = sprintf(
                    "=== CONFIGURATION EXPORT ===\n" .
                    "Device: %s\n" .
                    "Type: %s\n" .
                    "Site: %s\n" .
                    "IP Management: %s\n" .
                    "Last Updated: %s\n" .
                    "Exported: %s\n" .
                    "=============================\n\n%s",
                    $device->name,
                    class_basename($device),
                    $device->site->name ?? 'N/A',
                    $device->management_ip ?? $device->ip_nms ?? 'N/A',
                    $device->updated_at->format('Y-m-d H:i:s'),
                    Carbon::now()->format('Y-m-d H:i:s'),
                    $device->configuration
                );
                
                $zip->addFromString($filename, $content);
            }
        }
    }

    /**
     * Générer un fichier README pour l'export
     */
    protected function generateReadme(): string
    {
        return <<<README
EXPORT DE CONFIGURATIONS RÉSEAU
================================

Date d'export: {date}
Généré par: {user}
Plateforme: Gestion des Configurations Réseau

STRUCTURE DU DOSSIER:
---------------------
- switches/     : Configurations des switches
- routers/      : Configurations des routeurs
- firewalls/    : Configurations des firewalls

FORMAT DES FICHIERS:
-------------------
Chaque fichier contient:
1. En-tête avec les métadonnées
2. Configuration complète de l'équipement

MÉTADONNÉES:
-----------
- Device: Nom de l'équipement
- Type: Type d'équipement (Switch/Router/Firewall)
- Site: Site d'appartenance
- IP Management: Adresse IP de management
- Last Updated: Dernière mise à jour
- Exported: Date d'export

CONSIGNES DE SÉCURITÉ:
---------------------
1. Ces fichiers contiennent des informations sensibles
2. Ne pas partager avec des personnes non autorisées
3. Stocker dans un endroit sécurisé
4. Supprimer après utilisation si nécessaire

CONTACT:
-------
Pour toute question concernant cet export, contacter l'équipe réseau.

{signature}
README;
    }

    /**
     * Préparer les données pour l'export
     */
    protected function prepareExportData(string $type, ?int $siteId): array
    {
        $data = [
            'export_date' => Carbon::now()->format('d/m/Y H:i:s'),
            'exported_by' => auth()->user()->name ?? 'Système',
            'site' => null
        ];

        if ($siteId) {
            $data['site'] = Site::find($siteId);
        }

        switch ($type) {
            case 'switches':
                $query = SwitchModel::with('site');
                if ($siteId) $query->where('site_id', $siteId);
                $data['devices'] = $query->get();
                $data['device_type'] = 'Switches';
                break;
                
            case 'routers':
                $query = Router::with('site');
                if ($siteId) $query->where('site_id', $siteId);
                $data['devices'] = $query->get();
                $data['device_type'] = 'Routeurs';
                break;
                
            case 'firewalls':
                $query = Firewall::with('site');
                if ($siteId) $query->where('site_id', $siteId);
                $data['devices'] = $query->get();
                $data['device_type'] = 'Firewalls';
                break;
                
            case 'sites':
                $data['sites'] = Site::withCount(['switches', 'routers', 'firewalls'])->get();
                $data['device_type'] = 'Sites';
                break;
                
            case 'all':
            default:
                $data['sites'] = Site::withCount(['switches', 'routers', 'firewalls'])->get();
                $data['switches'] = SwitchModel::with('site')->get();
                $data['routers'] = Router::with('site')->get();
                $data['firewalls'] = Firewall::with('site')->get();
                $data['device_type'] = 'Tous les équipements';
                break;
        }

        return $data;
    }

    /**
     * Générer un rapport d'audit
     */
    public function generateAuditReport(array $options = []): string
    {
        $startDate = $options['start_date'] ?? Carbon::now()->subMonth();
        $endDate = $options['end_date'] ?? Carbon::now();
        $format = $options['format'] ?? 'pdf';
        
        $data = [
            'period' => [
                'start' => Carbon::parse($startDate)->format('d/m/Y'),
                'end' => Carbon::parse($endDate)->format('d/m/Y')
            ],
            'summary' => $this->getAuditSummary($startDate, $endDate),
            'changes_by_user' => $this->getChangesByUser($startDate, $endDate),
            'changes_by_device' => $this->getChangesByDevice($startDate, $endDate),
            'backup_statistics' => $this->getBackupStatistics($startDate, $endDate),
            'export_date' => Carbon::now()->format('d/m/Y H:i:s')
        ];
        
        if ($format === 'pdf') {
            $filename = "audit_report_{$startDate}_{$endDate}.pdf";
            $filepath = "exports/{$filename}";
            
            $pdf = PDF::loadView('exports.audit-report-pdf', $data)
                ->setPaper('a4', 'portrait');
            
            Storage::put($filepath, $pdf->output());
            
            return $filepath;
        } else {
            // Export Excel pour l'audit
            $filename = "audit_report_{$startDate}_{$endDate}.xlsx";
            $filepath = "exports/{$filename}";
            
            Excel::store(new \App\Exports\AuditReportExport($data), $filepath);
            
            return $filepath;
        }
    }

    /**
     * Obtenir le résumé de l'audit
     */
    protected function getAuditSummary($startDate, $endDate): array
    {
        return [
            'total_changes' => \App\Models\ConfigurationHistory::whereBetween('created_at', [$startDate, $endDate])->count(),
            'backups_created' => \App\Models\ConfigurationHistory::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('change_type', ['backup', 'manual_backup', 'auto_backup'])
                ->count(),
            'restorations' => \App\Models\ConfigurationHistory::whereBetween('created_at', [$startDate, $endDate])
                ->where('change_type', 'restore')
                ->count(),
            'unique_users' => \App\Models\ConfigurationHistory::whereBetween('created_at', [$startDate, $endDate])
                ->distinct('user_id')
                ->count('user_id'),
            'unique_devices' => \App\Models\ConfigurationHistory::whereBetween('created_at', [$startDate, $endDate])
                ->distinct('device_id')
                ->count('device_id')
        ];
    }

    /**
     * Obtenir les changements par utilisateur
     */
    protected function getChangesByUser($startDate, $endDate): array
    {
        return \App\Models\ConfigurationHistory::whereBetween('created_at', [$startDate, $endDate])
            ->with('user')
            ->selectRaw('user_id, count(*) as changes_count')
            ->groupBy('user_id')
            ->orderByDesc('changes_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'user' => $item->user->name ?? 'Utilisateur inconnu',
                    'changes_count' => $item->changes_count
                ];
            })
            ->toArray();
    }

    /**
     * Obtenir les changements par appareil
     */
    protected function getChangesByDevice($startDate, $endDate): array
    {
        return \App\Models\ConfigurationHistory::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('device_type, device_id, count(*) as changes_count')
            ->groupBy('device_type', 'device_id')
            ->orderByDesc('changes_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $device = $item->device;
                return [
                    'device' => $device->name ?? 'Appareil inconnu',
                    'device_type' => class_basename($item->device_type),
                    'changes_count' => $item->changes_count
                ];
            })
            ->toArray();
    }

    /**
     * Obtenir les statistiques de backup
     */
    protected function getBackupStatistics($startDate, $endDate): array
    {
        return [
            'total_backups' => \App\Models\ConfigurationHistory::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('change_type', ['backup', 'manual_backup', 'auto_backup'])
                ->count(),
            'by_device_type' => \App\Models\ConfigurationHistory::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('change_type', ['backup', 'manual_backup', 'auto_backup'])
                ->selectRaw('device_type, count(*) as count')
                ->groupBy('device_type')
                ->get()
                ->map(function ($item) {
                    return [
                        'device_type' => class_basename($item->device_type),
                        'count' => $item->count
                    ];
                })
                ->toArray(),
            'avg_backup_size' => \App\Models\ConfigurationHistory::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('change_type', ['backup', 'manual_backup', 'auto_backup'])
                ->avg('config_size')
        ];
    }
}