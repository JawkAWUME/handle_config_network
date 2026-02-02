<?php
// app/Traits/Exportable.php

namespace App\Traits;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

trait Exportable
{
    /**
     * Exporter les données au format Excel
     */
    public function exportToExcel($data, string $filename, string $exportClass): string
    {
        $filename = $this->generateFilename($filename, 'xlsx');
        $path = "exports/excel/{$filename}";
        
        Excel::store(new $exportClass($data), $path);
        
        return $path;
    }

    /**
     * Exporter les données au format PDF
     */
    public function exportToPDF($data, string $filename, string $view): string
    {
        $filename = $this->generateFilename($filename, 'pdf');
        $path = "exports/pdf/{$filename}";
        
        $pdf = PDF::loadView($view, ['data' => $data])
            ->setPaper('a4', 'landscape')
            ->setOptions(['defaultFont' => 'helvetica']);
        
        Storage::put($path, $pdf->output());
        
        return $path;
    }

    /**
     * Exporter les données au format CSV
     */
    public function exportToCSV($data, string $filename, array $headers = []): string
    {
        $filename = $this->generateFilename($filename, 'csv');
        $path = "exports/csv/{$filename}";
        
        $handle = fopen(storage_path("app/{$path}"), 'w');
        
        // Ajouter les en-têtes
        if (!empty($headers)) {
            fputcsv($handle, $headers);
        }
        
        // Ajouter les données
        foreach ($data as $row) {
            fputcsv($handle, (array) $row);
        }
        
        fclose($handle);
        
        return $path;
    }

    /**
     * Exporter les données au format JSON
     */
    public function exportToJSON($data, string $filename): string
    {
        $filename = $this->generateFilename($filename, 'json');
        $path = "exports/json/{$filename}";
        
        Storage::put($path, json_encode($data, JSON_PRETTY_PRINT));
        
        return $path;
    }

    /**
     * Exporter les configurations au format texte
     */
    public function exportConfigurationsToText($devices, string $type): string
    {
        $filename = $this->generateFilename("configurations_{$type}", 'zip');
        $zipPath = "exports/zip/{$filename}";
        
        $zip = new \ZipArchive();
        $fullPath = Storage::path($zipPath);
        
        if ($zip->open($fullPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($devices as $device) {
                if ($device->configuration) {
                    $content = $this->generateConfigFileContent($device);
                    $deviceFilename = $this->generateDeviceFilename($device);
                    
                    $zip->addFromString("{$type}/{$deviceFilename}", $content);
                }
            }
            
            // Ajouter un fichier README
            $readme = $this->generateReadmeContent($type, count($devices));
            $zip->addFromString('README.txt', $readme);
            
            $zip->close();
        }
        
        return $zipPath;
    }

    /**
     * Générer un nom de fichier unique
     */
    protected function generateFilename(string $baseName, string $extension): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $safeBaseName = Str::slug($baseName);
        
        return "{$safeBaseName}_{$timestamp}.{$extension}";
    }

    /**
     * Générer le contenu du fichier de configuration
     */
    protected function generateConfigFileContent($device): string
    {
        $siteName = $device->site->name ?? 'No Site';
        $deviceType = class_basename($device);
        $backupDate = $device->last_backup ? 
            $device->last_backup->format('Y-m-d H:i:s') : 
            'Never backed up';
        
        return <<<CONFIG
==========================================
CONFIGURATION EXPORT
==========================================
Device: {$device->name}
Type: {$deviceType}
Site: {$siteName}
Last Backup: {$backupDate}
Exported: {$this->getCurrentTimestamp()}
==========================================

{$device->configuration}

==========================================
END OF CONFIGURATION
==========================================
CONFIG;
    }

    /**
     * Générer le nom de fichier pour un appareil
     */
    protected function generateDeviceFilename($device): string
    {
        $siteSlug = Str::slug($device->site->name ?? 'no-site');
        $deviceSlug = Str::slug($device->name);
        $date = Carbon::now()->format('Y-m-d');
        
        return "{$siteSlug}_{$deviceSlug}_{$date}.txt";
    }

    /**
     * Générer le contenu du README
     */
    protected function generateReadmeContent(string $type, int $count): string
    {
        $timestamp = $this->getCurrentTimestamp();
        
        return <<<README
NETWORK CONFIGURATION EXPORT
============================

Export Type: {$type}
Devices Count: {$count}
Export Date: {$timestamp}
Generated By: Network Configuration Manager

STRUCTURE:
----------
- Each device configuration is saved in a separate text file
- Files are organized by device type
- Filename format: site_device_date.txt

SECURITY NOTICE:
---------------
This archive contains sensitive network configuration data.
Please handle with appropriate security measures:
1. Do not share with unauthorized personnel
2. Store in a secure location
3. Delete after use if no longer needed

CONTACT:
-------
For questions about this export, contact the network team.

============================
README;
    }

    /**
     * Obtenir le timestamp actuel formaté
     */
    protected function getCurrentTimestamp(): string
    {
        return Carbon::now()->format('Y-m-d H:i:s');
    }

    /**
     * Vérifier l'espace disque disponible
     */
    public function checkExportDiskSpace(): array
    {
        $total = disk_total_space(storage_path('app/exports'));
        $free = disk_free_space(storage_path('app/exports'));
        $used = $total - $free;
        
        return [
            'total' => $this->formatBytes($total),
            'free' => $this->formatBytes($free),
            'used' => $this->formatBytes($used),
            'used_percentage' => round(($used / $total) * 100, 2),
            'status' => ($free / $total * 100) < 10 ? 'warning' : 'ok'
        ];
    }

    /**
     * Formater les octets en unités lisibles
     */
    protected function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Nettoyer les anciens fichiers d'export
     */
    public function cleanupOldExports(int $days = 7): array
    {
        $cutoff = Carbon::now()->subDays($days);
        $deleted = 0;
        
        $directories = ['exports/excel', 'exports/pdf', 'exports/csv', 'exports/json', 'exports/zip'];
        
        foreach ($directories as $directory) {
            $files = Storage::files($directory);
            
            foreach ($files as $file) {
                $timestamp = Storage::lastModified($file);
                $fileDate = Carbon::createFromTimestamp($timestamp);
                
                if ($fileDate->lt($cutoff)) {
                    Storage::delete($file);
                    $deleted++;
                }
            }
        }
        
        return [
            'deleted_count' => $deleted,
            'cutoff_date' => $cutoff->format('Y-m-d'),
            'message' => "Deleted {$deleted} old export files"
        ];
    }
}