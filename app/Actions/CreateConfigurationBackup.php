<?php
// app/Actions/CreateConfigurationBackup.php

namespace App\Actions;

use App\Models\ConfigurationHistory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CreateConfigurationBackup
{
    /**
     * Exécuter la création d'un backup de configuration
     */
    public function execute($device, ?string $notes = null, bool $isManual = true)
    {
        // Vérifier si l'appareil a une configuration
        if (empty($device->configuration)) {
            throw new \Exception('Aucune configuration à sauvegarder');
        }

        // Générer un nom de fichier unique
        $filename = $this->generateFilename($device);
        
        // Sauvegarder dans le système de fichiers
        $filePath = $this->saveToFile($device, $filename);
        
        // Calculer le checksum
        $checksum = md5($device->configuration);
        
        // Créer l'entrée dans l'historique
        $backup = ConfigurationHistory::create([
            'device_type' => get_class($device),
            'device_id' => $device->id,
            'configuration' => $device->configuration,
            'configuration_file' => $filePath,
            'config_size' => strlen($device->configuration),
            'config_checksum' => $checksum,
            'user_id' => auth()->id(),
            'change_type' => $isManual ? 'manual_backup' : 'auto_backup',
            'notes' => $notes ?? ($isManual ? 'Backup manuel' : 'Backup automatique'),
            'ip_address' => request()->ip()
        ]);

        // Mettre à jour la date du dernier backup
        $device->update(['last_backup' => Carbon::now()]);

        // Déclencher l'événement
        event(new \App\Events\BackupCreated($backup, $device));

        return $backup;
    }

    /**
     * Générer un nom de fichier pour le backup
     */
    protected function generateFilename($device): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $deviceType = class_basename($device);
        $deviceName = Str::slug($device->name);
        
        return sprintf(
            '%s_%s_%s_%s.txt',
            $deviceType,
            $device->id,
            $deviceName,
            $timestamp
        );
    }

    /**
     * Sauvegarder la configuration dans un fichier
     */
    protected function saveToFile($device, string $filename): string
    {
        $directory = 'config-backups/' . Carbon::now()->format('Y/m/d');
        
        // Créer le répertoire s'il n'existe pas
        Storage::makeDirectory($directory);
        
        $filePath = $directory . '/' . $filename;
        
        // Sauvegarder le contenu
        Storage::put($filePath, $device->configuration);
        
        return $filePath;
    }

    /**
     * Créer un backup pour plusieurs appareils
     */
    public function executeForMultiple(array $deviceIds, string $deviceType, ?string $notes = null): array
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        $modelClass = $this->getModelClass($deviceType);
        
        foreach ($deviceIds as $deviceId) {
            try {
                $device = $modelClass::findOrFail($deviceId);
                $backup = $this->execute($device, $notes, true);
                
                $results['success'][] = [
                    'device_id' => $deviceId,
                    'device_name' => $device->name,
                    'backup_id' => $backup->id,
                    'filename' => basename($backup->configuration_file)
                ];
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'device_id' => $deviceId,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Obtenir la classe du modèle à partir du type
     */
    protected function getModelClass(string $deviceType): string
    {
        $models = [
            'switch' => \App\Models\SwitchModel::class,
            'router' => \App\Models\Router::class,
            'firewall' => \App\Models\Firewall::class
        ];

        if (!isset($models[$deviceType])) {
            throw new \Exception("Type d'appareil non supporté: {$deviceType}");
        }

        return $models[$deviceType];
    }

    /**
     * Vérifier l'espace disque disponible
     */
    public function checkDiskSpace(): array
    {
        $totalSpace = disk_total_space(storage_path());
        $freeSpace = disk_free_space(storage_path());
        $usedSpace = $totalSpace - $freeSpace;
        
        return [
            'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
            'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
            'used_gb' => round($usedSpace / 1024 / 1024 / 1024, 2),
            'used_percentage' => round(($usedSpace / $totalSpace) * 100, 2),
            'status' => $freeSpace < (1024 * 1024 * 1024) ? 'warning' : 'ok' // < 1GB
        ];
    }

    /**
     * Nettoyer les anciens backups
     */
    public function cleanupOldBackups(int $daysToKeep = 30): array
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        // Supprimer les entrées de la base de données
        $deletedCount = ConfigurationHistory::where('created_at', '<', $cutoffDate)
            ->whereIn('change_type', ['auto_backup', 'manual_backup'])
            ->delete();
        
        // Supprimer les fichiers (à implémenter selon votre structure)
        // Note: Cette partie dépend de votre logique de stockage
        
        return [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->format('Y-m-d'),
            'message' => "Supprimé {$deletedCount} backups plus anciens que {$daysToKeep} jours"
        ];
    }
}