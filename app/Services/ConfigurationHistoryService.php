<?php

namespace App\Services;

use App\Repositories\ConfigurationHistoryRepository;
use App\Models\ConfigurationHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;
use Carbon\Carbon;

class ConfigurationHistoryService
{
    public function __construct(
        protected ConfigurationHistoryRepository $historyRepository
    ) {}

    public function logChange(int $deviceId, string $deviceType, array $oldConfig, array $newConfig, array $metadata = []): ConfigurationHistory
    {
        try {
            Log::info('[ConfigurationHistoryService] Journalisation d\'un changement de configuration', [
                'device_type' => $deviceType,
                'device_id' => $deviceId,
                'change_type' => $metadata['change_type'] ?? 'update',
            ]);
            
            // Calculer le diff
            $diff = $this->calculateDiff($oldConfig, $newConfig);
            
            // Préparer les données
            $historyData = [
                'device_type' => $deviceType,
                'device_id' => $deviceId,
                'configuration' => json_encode($newConfig),
                'pre_change_config' => json_encode($oldConfig),
                'post_change_config' => json_encode($newConfig),
                'change_summary' => json_encode([
                    'diff' => $diff,
                    'fields_changed' => array_keys($diff),
                    'change_count' => count($diff),
                ]),
                'change_type' => $metadata['change_type'] ?? 'update',
                'user_id' => auth()->id() ?? $metadata['user_id'] ?? null,
                'ip_address' => request()->ip() ?? $metadata['ip_address'] ?? '0.0.0.0',
                'notes' => $metadata['notes'] ?? null,
                'config_size' => strlen(json_encode($newConfig)),
                'config_checksum' => md5(json_encode($newConfig)),
            ];
            
            // Créer l'entrée d'historique
            $history = $this->historyRepository->create($historyData);
            
            Log::debug('[ConfigurationHistoryService] Changement journalisé', [
                'history_id' => $history->id,
                'device_id' => $deviceId,
            ]);
            
            return $history;
            
        } catch (Exception $e) {
            Log::error('[ConfigurationHistoryService] Erreur lors de la journalisation: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getHistory(int $deviceId, string $deviceType, array $options = [])
    {
        try {
            $query = $this->historyRepository->query()
                ->where('device_type', $deviceType)
                ->where('device_id', $deviceId)
                ->with('user')
                ->latest();
            
            // Appliquer les filtres
            if (!empty($options['change_type'])) {
                $query->where('change_type', $options['change_type']);
            }
            
            if (!empty($options['start_date'])) {
                $query->where('created_at', '>=', Carbon::parse($options['start_date']));
            }
            
            if (!empty($options['end_date'])) {
                $query->where('created_at', '<=', Carbon::parse($options['end_date']));
            }
            
            if (!empty($options['user_id'])) {
                $query->where('user_id', $options['user_id']);
            }
            
            // Pagination ou récupération complète
            if (!empty($options['paginate'])) {
                return $query->paginate($options['per_page'] ?? 20);
            }
            
            return $query->get();
            
        } catch (Exception $e) {
            Log::error('[ConfigurationHistoryService] Erreur lors de la récupération de l\'historique: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getHistoryStatistics(string $deviceType = null, array $options = []): array
    {
        try {
            $query = $this->historyRepository->query();
            
            if ($deviceType) {
                $query->where('device_type', $deviceType);
            }
            
            if (!empty($options['start_date'])) {
                $query->where('created_at', '>=', Carbon::parse($options['start_date']));
            }
            
            if (!empty($options['end_date'])) {
                $query->where('created_at', '<=', Carbon::parse($options['end_date']));
            }
            
            $stats = [
                'total_changes' => $query->count(),
                'by_change_type' => $query->groupBy('change_type')
                    ->selectRaw('change_type, count(*) as count')
                    ->pluck('count', 'change_type')
                    ->toArray(),
                'by_device_type' => $this->historyRepository->query()
                    ->groupBy('device_type')
                    ->selectRaw('device_type, count(*) as count')
                    ->pluck('count', 'device_type')
                    ->toArray(),
                'by_user' => $query->whereNotNull('user_id')
                    ->groupBy('user_id')
                    ->with('user')
                    ->selectRaw('user_id, count(*) as count')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->user->name ?? 'Unknown' => $item->count];
                    })
                    ->toArray(),
                'recent_changes' => $query->latest()
                    ->limit(10)
                    ->with(['user', 'device'])
                    ->get()
                    ->map(function ($history) {
                        return [
                            'id' => $history->id,
                            'device_type' => class_basename($history->device_type),
                            'device_name' => $history->device->name ?? 'Unknown',
                            'change_type' => $history->change_type,
                            'user' => $history->user->name ?? 'System',
                            'timestamp' => $history->created_at->format('Y-m-d H:i:s'),
                            'notes' => $history->notes,
                        ];
                    }),
            ];
            
            // Ajouter des statistiques temporelles
            if (!empty($options['start_date']) && !empty($options['end_date'])) {
                $stats['changes_by_day'] = $this->getChangesByDay(
                    Carbon::parse($options['start_date']),
                    Carbon::parse($options['end_date']),
                    $deviceType
                );
            }
            
            return $stats;
            
        } catch (Exception $e) {
            Log::error('[ConfigurationHistoryService] Erreur lors du calcul des statistiques: ' . $e->getMessage());
            return [];
        }
    }

    public function restoreFromHistory(int $historyId): bool
    {
        try {
            $history = $this->historyRepository->find($historyId);
            
            if (!$history) {
                throw new Exception("Entrée d'historique non trouvée: {$historyId}");
            }
            
            Log::info('[ConfigurationHistoryService] Restauration depuis l\'historique', [
                'history_id' => $historyId,
                'device_type' => $history->device_type,
                'device_id' => $history->device_id,
            ]);
            
            // Trouver le modèle approprié
            $device = $history->device;
            
            if (!$device) {
                throw new Exception("Appareil non trouvé: {$history->device_type} #{$history->device_id}");
            }
            
            // Sauvegarder l'état actuel avant restauration
            $preRestoreHistory = $this->logChange(
                $device->id,
                get_class($device),
                $device->toArray(),
                $device->toArray(),
                [
                    'change_type' => 'pre_restore_backup',
                    'notes' => "Backup avant restauration depuis historique #{$historyId}",
                ]
            );
            
            // Restaurer la configuration
            $config = json_decode($history->configuration, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Configuration corrompue: " . json_last_error_msg());
            }
            
            // Mettre à jour l'appareil
            $device->update(['configuration' => $config]);
            
            // Créer une entrée pour la restauration
            $this->logChange(
                $device->id,
                get_class($device),
                $device->fresh()->toArray(),
                $config,
                [
                    'change_type' => 'restore',
                    'notes' => "Restauration depuis historique #{$historyId}",
                    'restored_from' => $historyId,
                ]
            );
            
            Log::info('[ConfigurationHistoryService] Restauration réussie', [
                'history_id' => $historyId,
                'device_id' => $device->id,
                'device_name' => $device->name,
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::error('[ConfigurationHistoryService] Erreur lors de la restauration: ' . $e->getMessage());
            throw $e;
        }
    }

    public function exportHistory(int $historyId): array
    {
        try {
            $history = $this->historyRepository->find($historyId);
            
            if (!$history) {
                throw new Exception("Entrée d'historique non trouvée: {$historyId}");
            }
            
            $device = $history->device;
            $timestamp = $history->created_at->format('Ymd_His');
            $deviceType = class_basename($history->device_type);
            
            // Préparer les données d'export
            $exportData = [
                'metadata' => [
                    'export_type' => 'configuration_history',
                    'history_id' => $historyId,
                    'device_type' => $deviceType,
                    'device_id' => $history->device_id,
                    'device_name' => $device->name ?? 'Unknown',
                    'change_type' => $history->change_type,
                    'timestamp' => $history->created_at->toISOString(),
                    'exported_at' => now()->toISOString(),
                    'exported_by' => auth()->user()?->name ?? 'System',
                ],
                'history_details' => [
                    'user' => $history->user?->name ?? 'System',
                    'ip_address' => $history->ip_address,
                    'notes' => $history->notes,
                    'config_size' => $history->config_size,
                    'checksum' => $history->config_checksum,
                ],
                'configuration' => json_decode($history->configuration, true) ?? [],
                'pre_change_config' => json_decode($history->pre_change_config ?? '{}', true) ?? [],
                'post_change_config' => json_decode($history->post_change_config ?? '{}', true) ?? [],
                'change_summary' => json_decode($history->change_summary ?? '{}', true) ?? [],
            ];
            
            // Créer le fichier d'export
            $filename = "history_export_{$deviceType}_{$history->device_id}_{$timestamp}.json";
            $filepath = "exports/history/{$filename}";
            
            Storage::put($filepath, json_encode($exportData, JSON_PRETTY_PRINT));
            
            // Journaliser l'export
            $this->logExportActivity($history);
            
            return [
                'file_path' => Storage::path($filepath),
                'filename' => $filename,
                'size' => Storage::size($filepath),
                'download_url' => route('history.export.download', ['file' => $filename]),
            ];
            
        } catch (Exception $e) {
            Log::error('[ConfigurationHistoryService] Erreur lors de l\'export: ' . $e->getMessage());
            throw $e;
        }
    }

    public function cleanupOldHistory(int $daysThreshold = 365): array
    {
        try {
            Log::info('[ConfigurationHistoryService] Nettoyage de l\'historique ancien', [
                'threshold_days' => $daysThreshold,
            ]);
            
            $cutoffDate = now()->subDays($daysThreshold);
            $oldHistories = $this->historyRepository->getOldHistories($cutoffDate);
            
            $results = [
                'archived' => 0,
                'deleted' => 0,
                'errors' => [],
            ];
            
            foreach ($oldHistories as $history) {
                try {
                    // Archiver les configurations importantes
                    if ($this->shouldArchive($history)) {
                        $this->archiveHistory($history);
                        $results['archived']++;
                    }
                    
                    // Supprimer les entrées non critiques
                    $deleted = $this->historyRepository->delete($history->id);
                    
                    if ($deleted) {
                        $results['deleted']++;
                    }
                    
                } catch (Exception $e) {
                    Log::error("[ConfigurationHistoryService] Erreur lors du nettoyage de l'historique {$history->id}: " . $e->getMessage());
                    $results['errors'][] = [
                        'history_id' => $history->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }
            
            Log::info('[ConfigurationHistoryService] Nettoyage terminé', $results);
            
            return $results;
            
        } catch (Exception $e) {
            Log::error('[ConfigurationHistoryService] Erreur lors du nettoyage: ' . $e->getMessage());
            throw $e;
        }
    }

    public function compareConfigurations(int $historyId1, int $historyId2): array
    {
        try {
            $history1 = $this->historyRepository->find($historyId1);
            $history2 = $this->historyRepository->find($historyId2);
            
            if (!$history1 || !$history2) {
                throw new Exception("Une ou plusieurs entrées d'historique non trouvées");
            }
            
            if ($history1->device_type !== $history2->device_type || 
                $history1->device_id !== $history2->device_id) {
                throw new Exception("Les configurations ne sont pas du même appareil");
            }
            
            $config1 = json_decode($history1->configuration, true) ?? [];
            $config2 = json_decode($history2->configuration, true) ?? [];
            
            $comparison = [
                'history_ids' => [$historyId1, $historyId2],
                'timestamps' => [
                    $history1->created_at->toISOString(),
                    $history2->created_at->toISOString(),
                ],
                'users' => [
                    $history1->user?->name ?? 'System',
                    $history2->user?->name ?? 'System',
                ],
                'change_types' => [$history1->change_type, $history2->change_type],
                'diff' => $this->calculateDetailedDiff($config1, $config2),
                'summary' => [
                    'config1_size' => $history1->config_size,
                    'config2_size' => $history2->config_size,
                    'size_difference' => $history2->config_size - $history1->config_size,
                    'fields_changed' => count($this->calculateDiff($config1, $config2)),
                    'is_identical' => $history1->config_checksum === $history2->config_checksum,
                ],
            ];
            
            return $comparison;
            
        } catch (Exception $e) {
            Log::error('[ConfigurationHistoryService] Erreur lors de la comparaison: ' . $e->getMessage());
            throw $e;
        }
    }

    private function calculateDiff(array $oldConfig, array $newConfig): array
    {
        $diff = [];
        
        foreach ($newConfig as $key => $value) {
            if (!array_key_exists($key, $oldConfig) || $oldConfig[$key] != $value) {
                $diff[$key] = [
                    'old' => $oldConfig[$key] ?? null,
                    'new' => $value,
                ];
            }
        }
        
        // Vérifier les clés supprimées
        foreach ($oldConfig as $key => $value) {
            if (!array_key_exists($key, $newConfig)) {
                $diff[$key] = [
                    'old' => $value,
                    'new' => null,
                    'action' => 'deleted',
                ];
            }
        }
        
        return $diff;
    }

    private function calculateDetailedDiff(array $config1, array $config2): array
    {
        $diff = [];
        
        // Comparaison récursive
        $this->recursiveDiff($config1, $config2, '', $diff);
        
        return $diff;
    }
    
    private function recursiveDiff(array $array1, array $array2, string $path, array &$diff): void
    {
        $keys = array_unique(array_merge(array_keys($array1), array_keys($array2)));
        
        foreach ($keys as $key) {
            $currentPath = $path ? "{$path}.{$key}" : $key;
            
            if (!array_key_exists($key, $array1)) {
                // Clé ajoutée
                $diff[$currentPath] = [
                    'type' => 'added',
                    'old' => null,
                    'new' => $array2[$key],
                ];
            } elseif (!array_key_exists($key, $array2)) {
                // Clé supprimée
                $diff[$currentPath] = [
                    'type' => 'deleted',
                    'old' => $array1[$key],
                    'new' => null,
                ];
            } elseif (is_array($array1[$key]) && is_array($array2[$key])) {
                // Tableau imbriqué
                $this->recursiveDiff($array1[$key], $array2[$key], $currentPath, $diff);
            } elseif ($array1[$key] != $array2[$key]) {
                // Valeur modifiée
                $diff[$currentPath] = [
                    'type' => 'modified',
                    'old' => $array1[$key],
                    'new' => $array2[$key],
                ];
            }
        }
    }

    private function getChangesByDay(Carbon $startDate, Carbon $endDate, ?string $deviceType): array
    {
        $query = $this->historyRepository->query()
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        if ($deviceType) {
            $query->where('device_type', $deviceType);
        }
        
        return $query->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
    }

    private function shouldArchive(ConfigurationHistory $history): bool
    {
        // Archiver les configurations importantes
        $importantTypes = ['create', 'backup', 'restore', 'major_update'];
        
        return in_array($history->change_type, $importantTypes) ||
               !empty($history->notes) ||
               $history->config_size > 10000; // Configurations volumineuses
    }

    private function archiveHistory(ConfigurationHistory $history): void
    {
        $archiveData = [
            'history_id' => $history->id,
            'device_type' => $history->device_type,
            'device_id' => $history->device_id,
            'change_type' => $history->change_type,
            'configuration' => json_decode($history->configuration, true),
            'user' => $history->user?->name,
            'timestamp' => $history->created_at->toISOString(),
            'archived_at' => now()->toISOString(),
        ];
        
        $filename = "archive_history_{$history->id}_" . now()->format('Ymd_His') . ".json";
        $filepath = "archives/history/{$filename}";
        
        Storage::put($filepath, json_encode($archiveData, JSON_PRETTY_PRINT));
        
        Log::debug("[ConfigurationHistoryService] Historique archivé: {$history->id}", [
            'filepath' => $filepath,
        ]);
    }

    private function logExportActivity(ConfigurationHistory $history): void
    {
        try {
            \App\Models\AccessLog::create([
                'user_id' => auth()->id(),
                'action' => 'export_history',
                'device_type' => $history->device_type,
                'device_id' => $history->device_id,
                'parameters' => [
                    'history_id' => $history->id,
                    'change_type' => $history->change_type,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'result' => 'success',
            ]);
        } catch (Exception $e) {
            Log::error('[ConfigurationHistoryService] Erreur lors de la journalisation de l\'export: ' . $e->getMessage());
        }
    }
}