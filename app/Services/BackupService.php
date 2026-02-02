<?php

namespace App\Services;

use App\Services\Contracts\BackupServiceInterface;
use App\Repositories\BackupRepository;
use App\Repositories\ConfigurationHistoryRepository;
use App\Repositories\DeviceRepository;
use App\Events\ConfigurationBackupCreated;
use App\Events\BulkConfigurationBackup;
use App\Models\Firewall;
use App\Models\Router;
use App\Models\SwitchModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class BackupService implements BackupServiceInterface
{
    protected array $supportedDevices = [
        'firewall' => Firewall::class,
        'router' => Router::class,
        'switch' => SwitchModel::class,
    ];

    public function __construct(
        protected BackupRepository $backupRepository,
        protected DeviceRepository $deviceRepository,
        protected ConfigurationHistoryRepository $historyRepository
    ) {}

    public function executeScheduledBackups(): array
    {
        Log::info('[BackupService] Démarrage des backups planifiés');
        
        $results = [];
        $backupCount = 0;
        $errorCount = 0;
        
        foreach ($this->supportedDevices as $type => $modelClass) {
            try {
                Log::debug("[BackupService] Recherche des {$type}s nécessitant un backup");
                
                $devices = $modelClass::where('status', true)
                    ->where(function ($query) {
                        $query->whereNull('last_backup')
                            ->orWhere('last_backup', '<', now()->subDays(7));
                    })
                    ->get();
                
                foreach ($devices as $device) {
                    try {
                        $result = $this->createDeviceBackup($device, 'scheduled');
                        
                        if ($result['success']) {
                            $backupCount++;
                            $results['success'][] = [
                                'device_type' => $type,
                                'device_id' => $device->id,
                                'device_name' => $device->name,
                                'backup_id' => $result['backup_id'],
                                'timestamp' => now()->toISOString(),
                            ];
                        } else {
                            $errorCount++;
                            $results['errors'][] = [
                                'device_type' => $type,
                                'device_id' => $device->id,
                                'device_name' => $device->name,
                                'error' => $result['error'],
                                'timestamp' => now()->toISOString(),
                            ];
                        }
                        
                        // Pause courte pour éviter la surcharge
                        usleep(100000); // 100ms
                        
                    } catch (Exception $e) {
                        $errorCount++;
                        Log::error("[BackupService] Erreur sur {$type} {$device->id}: " . $e->getMessage());
                        $results['errors'][] = [
                            'device_type' => $type,
                            'device_id' => $device->id,
                            'device_name' => $device->name,
                            'error' => $e->getMessage(),
                            'timestamp' => now()->toISOString(),
                        ];
                    }
                }
                
            } catch (Exception $e) {
                Log::error("[BackupService] Erreur lors du traitement des {$type}s: " . $e->getMessage());
                $results['critical_errors'][] = [
                    'type' => $type,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        // Événement pour le backup groupé
        event(new BulkConfigurationBackup($results, 'scheduled', [
            'userId' => auth()->id(),
            'metadata' => [
                'start_time' => now()->subSeconds(30)->toISOString(),
                'duration' => 30,
            ]
        ]));
        
        $summary = [
            'total_backups' => $backupCount,
            'total_errors' => $errorCount,
            'timestamp' => now()->toISOString(),
            'execution_time' => microtime(true) - LARAVEL_START,
        ];
        
        Log::info("[BackupService] Backup planifiés terminés", $summary);
        
        return array_merge($results, ['summary' => $summary]);
    }

    public function checkDevicesNeedingBackup(int $daysThreshold = 7): array
    {
        $devicesNeedingBackup = [];
        $now = now();
        
        foreach ($this->supportedDevices as $type => $modelClass) {
            $devices = $modelClass::where('status', true)->get();
            
            foreach ($devices as $device) {
                $lastBackup = $device->last_backup;
                $daysSinceBackup = $lastBackup ? $lastBackup->diffInDays($now) : PHP_INT_MAX;
                
                if ($daysSinceBackup > $daysThreshold) {
                    $devicesNeedingBackup[] = [
                        'type' => $type,
                        'id' => $device->id,
                        'name' => $device->name,
                        'last_backup' => $lastBackup?->toISOString(),
                        'days_since_backup' => $daysSinceBackup,
                        'priority' => $this->calculateBackupPriority($device, $daysSinceBackup),
                        'site' => $device->site?->name,
                    ];
                }
            }
        }
        
        // Trier par priorité (critique d'abord)
        usort($devicesNeedingBackup, fn($a, $b) => $b['priority'] <=> $a['priority']);
        
        return [
            'devices' => $devicesNeedingBackup,
            'total' => count($devicesNeedingBackup),
            'threshold_days' => $daysThreshold,
            'generated_at' => $now->toISOString(),
        ];
    }

    public function configureBackupSchedule(array $scheduleConfig): bool
    {
        $requiredKeys = ['frequency', 'time', 'devices', 'retention_days'];
        
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $scheduleConfig)) {
                throw new \InvalidArgumentException("La clé '{$key}' est requise dans la configuration");
            }
        }
        
        $validFrequencies = ['daily', 'weekly', 'monthly', 'hourly'];
        if (!in_array($scheduleConfig['frequency'], $validFrequencies)) {
            throw new \InvalidArgumentException("Fréquence invalide. Valeurs acceptées: " . implode(', ', $validFrequencies));
        }
        
        try {
            // Sauvegarder la configuration dans le fichier de configuration ou la base de données
            $configFile = config_path('backup_schedule.php');
            $configData = array_merge(
                $this->getCurrentScheduleConfig(),
                $scheduleConfig,
                ['last_modified' => now()->toISOString(), 'modified_by' => auth()->id()]
            );
            
            file_put_contents($configFile, '<?php return ' . var_export($configData, true) . ';');
            
            Log::info('[BackupService] Planification de backup configurée', [
                'frequency' => $scheduleConfig['frequency'],
                'time' => $scheduleConfig['time'],
                'modified_by' => auth()->id(),
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::error('[BackupService] Erreur lors de la configuration de la planification: ' . $e->getMessage());
            throw $e;
        }
    }

    public function testDeviceConnectivity($device): array
    {
        $results = [
            'device_id' => $device->id,
            'device_type' => class_basename($device),
            'device_name' => $device->name,
            'tests' => [],
            'overall_status' => 'unknown',
            'timestamp' => now()->toISOString(),
        ];
        
        try {
            // Test de ping sur l'IP de management
            if ($device->ip_nms ?? $device->management_ip ?? $device->ip_address) {
                $ip = $device->ip_nms ?? $device->management_ip ?? $device->ip_address;
                $pingResult = $this->pingDevice($ip);
                $results['tests']['ping'] = $pingResult;
            }
            
            // Test des credentials SSH/Telnet (simulé)
            $authResult = $this->testAuthentication($device);
            $results['tests']['authentication'] = $authResult;
            
            // Test de récupération de configuration
            $configResult = $this->testConfigurationRetrieval($device);
            $results['tests']['configuration_retrieval'] = $configResult;
            
            // Déterminer le statut global
            $successfulTests = array_filter($results['tests'], fn($test) => $test['status'] === 'success');
            $results['overall_status'] = count($successfulTests) === count($results['tests']) ? 'success' : 'partial';
            
            if (empty($successfulTests)) {
                $results['overall_status'] = 'failed';
            }
            
            Log::info("[BackupService] Test de connectivité pour {$device->name}", [
                'device_id' => $device->id,
                'status' => $results['overall_status'],
            ]);
            
        } catch (Exception $e) {
            Log::error("[BackupService] Erreur lors du test de connectivité: " . $e->getMessage());
            $results['tests']['exception'] = [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ];
            $results['overall_status'] = 'failed';
        }
        
        return $results;
    }

    public function syncWithFilesystem(): array
    {
        Log::info('[BackupService] Synchronisation avec le système de fichiers');
        
        $results = [
            'added' => [],
            'updated' => [],
            'deleted' => [],
            'errors' => [],
        ];
        
        try {
            // Scanne le répertoire de backups
            $backupFiles = Storage::files('backups');
            
            foreach ($backupFiles as $file) {
                try {
                    $fileInfo = $this->parseBackupFilename($file);
                    
                    if (!$fileInfo) {
                        Log::warning("[BackupService] Fichier ignoré: format invalide - {$file}");
                        continue;
                    }
                    
                    // Vérifier si le backup existe déjà dans la base
                    $existingBackup = $this->backupRepository->findByChecksum(
                        md5(Storage::get($file))
                    );
                    
                    if (!$existingBackup) {
                        // Ajouter un nouvel enregistrement
                        $backup = $this->backupRepository->create([
                            'device_type' => $fileInfo['device_type'],
                            'device_id' => $fileInfo['device_id'],
                            'filename' => basename($file),
                            'path' => $file,
                            'size' => Storage::size($file),
                            'checksum' => md5(Storage::get($file)),
                            'created_at' => Carbon::createFromTimestamp(Storage::lastModified($file)),
                            'source' => 'filesystem_sync',
                        ]);
                        
                        $results['added'][] = [
                            'file' => $file,
                            'backup_id' => $backup->id,
                            'device_type' => $fileInfo['device_type'],
                            'device_id' => $fileInfo['device_id'],
                        ];
                        
                        Log::debug("[BackupService] Backup ajouté depuis fichiers: {$file}");
                    }
                    
                } catch (Exception $e) {
                    Log::error("[BackupService] Erreur lors du traitement du fichier {$file}: " . $e->getMessage());
                    $results['errors'][] = [
                        'file' => $file,
                        'error' => $e->getMessage(),
                    ];
                }
            }
            
            // Nettoyer les enregistrements orphelins
            $this->cleanOrphanedBackups($backupFiles);
            
            $summary = [
                'total_files' => count($backupFiles),
                'added' => count($results['added']),
                'errors' => count($results['errors']),
                'timestamp' => now()->toISOString(),
            ];
            
            Log::info('[BackupService] Synchronisation terminée', $summary);
            
            return array_merge($results, ['summary' => $summary]);
            
        } catch (Exception $e) {
            Log::error('[BackupService] Erreur lors de la synchronisation: ' . $e->getMessage());
            throw $e;
        }
    }

    public function compressOldBackups(int $daysThreshold = 30): array
    {
        Log::info("[BackupService] Compression des vieux backups (> {$daysThreshold} jours)");
        
        $results = [
            'compressed' => [],
            'skipped' => [],
            'errors' => [],
            'space_saved' => 0,
        ];
        
        try {
            $cutoffDate = now()->subDays($daysThreshold);
            $backups = $this->backupRepository->getOldBackups($cutoffDate);
            
            foreach ($backups as $backup) {
                try {
                    if (!Storage::exists($backup->path)) {
                        Log::warning("[BackupService] Fichier non trouvé: {$backup->path}");
                        continue;
                    }
                    
                    // Vérifier si déjà compressé
                    if (pathinfo($backup->path, PATHINFO_EXTENSION) === 'gz') {
                        $results['skipped'][] = [
                            'backup_id' => $backup->id,
                            'reason' => 'déjà compressé',
                        ];
                        continue;
                    }
                    
                    $originalSize = Storage::size($backup->path);
                    $content = Storage::get($backup->path);
                    $compressed = gzencode($content, 9);
                    
                    $newPath = $backup->path . '.gz';
                    Storage::put($newPath, $compressed);
                    
                    // Supprimer l'original
                    Storage::delete($backup->path);
                    
                    // Mettre à jour l'enregistrement
                    $this->backupRepository->update($backup->id, [
                        'path' => $newPath,
                        'size' => strlen($compressed),
                        'compressed' => true,
                        'compressed_at' => now(),
                        'original_size' => $originalSize,
                    ]);
                    
                    $spaceSaved = $originalSize - strlen($compressed);
                    $results['space_saved'] += $spaceSaved;
                    
                    $results['compressed'][] = [
                        'backup_id' => $backup->id,
                        'original_path' => $backup->path,
                        'new_path' => $newPath,
                        'original_size' => $this->formatBytes($originalSize),
                        'new_size' => $this->formatBytes(strlen($compressed)),
                        'space_saved' => $this->formatBytes($spaceSaved),
                        'compression_ratio' => round(($spaceSaved / $originalSize) * 100, 2) . '%',
                    ];
                    
                    Log::debug("[BackupService] Backup compressé: {$backup->path} -> {$newPath}");
                    
                } catch (Exception $e) {
                    Log::error("[BackupService] Erreur lors de la compression du backup {$backup->id}: " . $e->getMessage());
                    $results['errors'][] = [
                        'backup_id' => $backup->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }
            
            $summary = [
                'total_processed' => count($backups),
                'compressed' => count($results['compressed']),
                'skipped' => count($results['skipped']),
                'errors' => count($results['errors']),
                'total_space_saved' => $this->formatBytes($results['space_saved']),
                'timestamp' => now()->toISOString(),
            ];
            
            Log::info('[BackupService] Compression terminée', $summary);
            
            return array_merge($results, ['summary' => $summary]);
            
        } catch (Exception $e) {
            Log::error('[BackupService] Erreur lors de la compression: ' . $e->getMessage());
            throw $e;
        }
    }

    public function restoreFromFilesystem(string $filePath, string $deviceType, int $deviceId): bool
    {
        try {
            if (!Storage::exists($filePath)) {
                throw new Exception("Fichier non trouvé: {$filePath}");
            }
            
            $content = Storage::get($filePath);
            
            // Vérifier si c'est un fichier compressé
            if (pathinfo($filePath, PATHINFO_EXTENSION) === 'gz') {
                $content = gzdecode($content);
                if ($content === false) {
                    throw new Exception("Échec de la décompression du fichier");
                }
            }
            
            $configData = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Format JSON invalide: " . json_last_error_msg());
            }
            
            // Trouver le modèle approprié
            $modelClass = $this->supportedDevices[$deviceType] ?? null;
            if (!$modelClass) {
                throw new Exception("Type d'appareil non supporté: {$deviceType}");
            }
            
            $device = $modelClass::find($deviceId);
            if (!$device) {
                throw new Exception("Appareil non trouvé: {$deviceType} #{$deviceId}");
            }
            
            // Sauvegarder la configuration actuelle
            $preRestoreBackup = $this->createDeviceBackup($device, 'pre_restore');
            
            // Restaurer la configuration
            $device->update(['configuration' => $configData]);
            
            // Créer un historique
            $this->historyRepository->create([
                'device_type' => $modelClass,
                'device_id' => $deviceId,
                'change_type' => 'restore_filesystem',
                'configuration' => $configData,
                'notes' => "Restauration depuis fichier: {$filePath}",
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
            ]);
            
            Log::info("[BackupService] Restauration réussie depuis fichiers", [
                'file' => $filePath,
                'device_type' => $deviceType,
                'device_id' => $deviceId,
                'user_id' => auth()->id(),
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::error("[BackupService] Échec de la restauration: " . $e->getMessage(), [
                'file' => $filePath,
                'device_type' => $deviceType,
                'device_id' => $deviceId,
            ]);
            throw $e;
        }
    }

    private function createDeviceBackup($device, string $type = 'manual'): array
    {
        try {
            $deviceType = class_basename($device);
            $timestamp = now()->format('Ymd_His');
            $filename = "backup_{$deviceType}_{$device->id}_{$timestamp}.json";
            $filePath = "backups/{$deviceType}/{$filename}";
            
            // Préparer les données de backup
            $backupData = [
                'device' => [
                    'id' => $device->id,
                    'name' => $device->name,
                    'type' => $deviceType,
                    'site' => $device->site?->name,
                ],
                'configuration' => $device->configuration ?? [],
                'security_policies' => $device->security_policies ?? [],
                'interfaces' => $device->interfaces ?? [],
                'metadata' => [
                    'backup_type' => $type,
                    'timestamp' => now()->toISOString(),
                    'user_id' => auth()->id(),
                    'version' => '1.0',
                ],
            ];
            
            $content = json_encode($backupData, JSON_PRETTY_PRINT);
            Storage::put($filePath, $content);
            
            // Créer l'enregistrement dans la base
            $backup = $this->backupRepository->create([
                'device_type' => get_class($device),
                'device_id' => $device->id,
                'filename' => $filename,
                'path' => $filePath,
                'size' => strlen($content),
                'checksum' => md5($content),
                'backup_type' => $type,
                'user_id' => auth()->id(),
                'status' => 'success',
            ]);
            
            // Mettre à jour le timestamp du dernier backup
            $device->update(['last_backup' => now()]);
            
            // Déclencher l'événement
            event(new ConfigurationBackupCreated($backup, [
                'userId' => auth()->id(),
                'metadata' => [
                    'device_name' => $device->name,
                    'backup_type' => $type,
                ]
            ]));
            
            Log::info("[BackupService] Backup créé pour {$device->name}", [
                'device_id' => $device->id,
                'backup_id' => $backup->id,
                'type' => $type,
            ]);
            
            return [
                'success' => true,
                'backup_id' => $backup->id,
                'file_path' => $filePath,
                'size' => strlen($content),
            ];
            
        } catch (Exception $e) {
            Log::error("[BackupService] Erreur lors du backup de {$device->name}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'device_id' => $device->id,
                'device_name' => $device->name,
            ];
        }
    }

    private function calculateBackupPriority($device, int $daysSinceBackup): int
    {
        $priority = 0;
        
        // Plus le backup est ancien, plus la priorité est haute
        $priority += min($daysSinceBackup * 10, 100);
        
        // Priorité plus haute pour les équipements critiques
        if ($device->high_availability ?? false) {
            $priority += 30;
        }
        
        if ($device instanceof Firewall) {
            $priority += 20; // Les firewalls sont critiques
        }
        
        // Équipement en production
        if ($device->site && $device->site->status === 'active') {
            $priority += 15;
        }
        
        return min($priority, 100);
    }

    private function pingDevice(string $ip): array
    {
        // Implémentation simplifiée - en production utiliser une librairie comme Symfony/Process
        $command = sprintf('ping -c 1 -W 1 %s', escapeshellarg($ip));
        exec($command, $output, $result);
        
        return [
            'status' => $result === 0 ? 'success' : 'failed',
            'ip' => $ip,
            'command' => $command,
            'result' => $result,
            'output' => $output,
        ];
    }

    private function testAuthentication($device): array
    {
        // Simulation d'authentification
        // En production, utiliser SSH ou Telnet avec les credentials
        $hasCredentials = !empty($device->username) && !empty($device->password);
        
        return [
            'status' => $hasCredentials ? 'success' : 'failed',
            'message' => $hasCredentials ? 'Credentials disponibles' : 'Credentials manquants',
            'username' => $device->username ? '***' : null,
            'has_password' => !empty($device->password),
        ];
    }

    private function testConfigurationRetrieval($device): array
    {
        // Simulation de récupération de configuration
        $hasConfig = !empty($device->configuration);
        
        return [
            'status' => $hasConfig ? 'success' : 'warning',
            'message' => $hasConfig ? 'Configuration disponible' : 'Configuration manquante',
            'config_size' => strlen($device->configuration ?? ''),
            'has_configuration_file' => !empty($device->configuration_file),
        ];
    }

    private function parseBackupFilename(string $filename): ?array
    {
        // Format: backup_DeviceType_ID_Timestamp.json
        $pattern = '/^backup_(\w+)_(\d+)_(\d{8}_\d{6})\.json$/';
        
        if (preg_match($pattern, basename($filename), $matches)) {
            return [
                'device_type' => $matches[1],
                'device_id' => (int) $matches[2],
                'timestamp' => $matches[3],
            ];
        }
        
        return null;
    }

    private function cleanOrphanedBackups(array $existingFiles): void
    {
        $orphans = $this->backupRepository->findOrphanedBackups($existingFiles);
        
        foreach ($orphans as $orphan) {
            try {
                $this->backupRepository->delete($orphan->id);
                Log::warning("[BackupService] Backup orphelin supprimé: {$orphan->id}");
            } catch (Exception $e) {
                Log::error("[BackupService] Erreur lors de la suppression du backup orphelin: " . $e->getMessage());
            }
        }
    }

    private function getCurrentScheduleConfig(): array
    {
        $configFile = config_path('backup_schedule.php');
        
        if (file_exists($configFile)) {
            return require $configFile;
        }
        
        return [
            'frequency' => 'daily',
            'time' => '02:00',
            'devices' => ['firewall', 'router', 'switch'],
            'retention_days' => 90,
            'compression_days' => 30,
            'notifications' => true,
            'enabled' => true,
        ];
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