<?php
// app/Repositories/ConfigurationHistoryRepository.php

namespace App\Repositories;

use App\Repositories\Contracts\ConfigurationHistoryRepositoryInterface;
use App\Models\ConfigurationHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConfigurationHistoryRepository implements ConfigurationHistoryRepositoryInterface
{
    protected $model;
    protected $cacheTtl = 3600; // 1 heure

    public function __construct(ConfigurationHistory $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return Cache::remember('configuration_history.all', $this->cacheTtl, function () {
            return $this->model->with(['device', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(1000)
                ->get();
        });
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with(['device', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?ConfigurationHistory
    {
        return Cache::remember("configuration_history.{$id}", $this->cacheTtl, function () use ($id) {
            return $this->model->with(['device', 'user', 'restoredFrom'])->find($id);
        });
    }

    public function findOrFail(int $id): ConfigurationHistory
    {
        return $this->model->with(['device', 'user', 'restoredFrom'])->findOrFail($id);
    }

    public function create(array $data): ConfigurationHistory
    {
        $history = $this->model->create($data);
        $this->clearHistoryCache();
        return $history;
    }

    public function update(int $id, array $data): bool
    {
        $history = $this->findOrFail($id);
        $result = $history->update($data);
        
        $this->clearHistoryCache($id);
        return $result;
    }

    public function delete(int $id): bool
    {
        $history = $this->findOrFail($id);
        $result = $history->delete();
        
        $this->clearHistoryCache($id);
        return $result;
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        $search = $this->model->with(['device', 'user']);

        if (!empty($query)) {
            $search->where(function ($q) use ($query) {
                $q->where('notes', 'like', "%{$query}%")
                  ->orWhere('change_type', 'like', "%{$query}%");
            });
        }

        // Appliquer les filtres
        if (!empty($filters['device_type'])) {
            $search->where('device_type', $filters['device_type']);
        }

        if (!empty($filters['device_id'])) {
            $search->where('device_id', $filters['device_id']);
        }

        if (!empty($filters['user_id'])) {
            $search->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['change_type'])) {
            $search->where('change_type', $filters['change_type']);
        }

        if (!empty($filters['start_date'])) {
            $search->where('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $search->where('created_at', '<=', $filters['end_date']);
        }

        return $search->orderBy('created_at', 'desc')->paginate(20);
    }

    public function getForDevice(string $deviceType, int $deviceId): Collection
    {
        return Cache::remember("configuration_history.device.{$deviceType}.{$deviceId}", $this->cacheTtl, function () use ($deviceType, $deviceId) {
            return $this->model->where('device_type', $deviceType)
                ->where('device_id', $deviceId)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function getForUser(int $userId): Collection
    {
        return Cache::remember("configuration_history.user.{$userId}", $this->cacheTtl, function () use ($userId) {
            return $this->model->where('user_id', $userId)
                ->with('device')
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();
        });
    }

    public function getBackups(): Collection
    {
        return Cache::remember('configuration_history.backups', $this->cacheTtl, function () {
            return $this->model->whereIn('change_type', ['backup', 'manual_backup', 'auto_backup'])
                ->with(['device', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(500)
                ->get();
        });
    }

    public function getRecentChanges(int $days = 7): Collection
    {
        return Cache::remember("configuration_history.recent.{$days}", 300, function () use ($days) {
            return $this->model->where('created_at', '>=', Carbon::now()->subDays($days))
                ->with(['device', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function getStatistics(): array
    {
        return Cache::remember('configuration_history.statistics', $this->cacheTtl, function () {
            $total = $this->model->count();
            
            // Par type de changement
            $byChangeType = $this->model->select('change_type', DB::raw('count(*) as count'))
                ->groupBy('change_type')
                ->orderBy('count', 'desc')
                ->get();

            // Par type d'appareil
            $byDeviceType = $this->model->select('device_type', DB::raw('count(*) as count'))
                ->groupBy('device_type')
                ->orderBy('count', 'desc')
                ->get();

            // Par utilisateur
            $byUser = $this->model->select('user_id', DB::raw('count(*) as count'))
                ->with('user')
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Changements récents (24h)
            $recent24h = $this->model->where('created_at', '>=', Carbon::now()->subDay())
                ->count();

            // Taille totale des configurations
            $totalSize = $this->model->sum('config_size');

            return [
                'total' => $total,
                'by_change_type' => $byChangeType,
                'by_device_type' => $byDeviceType,
                'by_user' => $byUser,
                'recent_24h' => $recent24h,
                'total_size_bytes' => $totalSize,
                'total_size_formatted' => $this->formatBytes($totalSize),
                'avg_size_bytes' => $total > 0 ? round($totalSize / $total) : 0
            ];
        });
    }

    public function getByChangeType(string $changeType): Collection
    {
        return Cache::remember("configuration_history.change_type.{$changeType}", $this->cacheTtl, function () use ($changeType) {
            return $this->model->where('change_type', $changeType)
                ->with(['device', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(200)
                ->get();
        });
    }

    public function getBetweenDates(string $startDate, string $endDate): Collection
    {
        $cacheKey = "configuration_history.dates.{$startDate}.{$endDate}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($startDate, $endDate) {
            return $this->model->whereBetween('created_at', [$startDate, $endDate])
                ->with(['device', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function compareConfigurations(int $historyId1, int $historyId2): array
    {
        $config1 = $this->findOrFail($historyId1);
        $config2 = $this->findOrFail($historyId2);

        // Vérifier que les configurations sont pour le même appareil
        if ($config1->device_type !== $config2->device_type || $config1->device_id !== $config2->device_id) {
            throw new \Exception('Les configurations ne sont pas pour le même appareil');
        }

        $diff = $this->generateDiff($config1->configuration, $config2->configuration);

        return [
            'config1' => [
                'id' => $config1->id,
                'created_at' => $config1->created_at,
                'user' => $config1->user->name ?? 'Inconnu',
                'change_type' => $config1->change_type
            ],
            'config2' => [
                'id' => $config2->id,
                'created_at' => $config2->created_at,
                'user' => $config2->user->name ?? 'Inconnu',
                'change_type' => $config2->change_type
            ],
            'device' => $config1->device->name ?? 'Appareil inconnu',
            'device_type' => class_basename($config1->device_type),
            'diff' => $diff,
            'summary' => $this->analyzeDiff($diff)
        ];
    }

    public function validateConfiguration(int $historyId): array
    {
        $history = $this->findOrFail($historyId);
        
        $validation = [
            'history_id' => $historyId,
            'checksum_valid' => false,
            'device_exists' => false,
            'config_size' => $history->config_size,
            'config_age_days' => $history->created_at->diffInDays(),
            'warnings' => [],
            'errors' => []
        ];

        // Vérifier le checksum
        if ($history->configuration) {
            $currentChecksum = md5($history->configuration);
            $validation['checksum_valid'] = $currentChecksum === $history->config_checksum;
            
            if (!$validation['checksum_valid']) {
                $validation['errors'][] = 'Checksum invalide: la configuration pourrait être corrompue';
            }
        } else {
            $validation['errors'][] = 'Configuration vide';
        }

        // Vérifier que l'appareil existe toujours
        $validation['device_exists'] = (bool) $history->device;
        
        if (!$validation['device_exists']) {
            $validation['warnings'][] = "L'appareil associé n'existe plus";
        }

        // Vérifier l'âge de la configuration
        if ($validation['config_age_days'] > 365) {
            $validation['warnings'][] = "Cette configuration a plus d'un an ({$validation['config_age_days']} jours)";
        }

        // Vérifier la taille de la configuration
        if ($validation['config_size'] < 100) {
            $validation['warnings'][] = 'La configuration semble très petite (<100 octets)';
        } elseif ($validation['config_size'] > 1048576) { // 1MB
            $validation['warnings'][] = 'Configuration très volumineuse (>1MB)';
        }

        $validation['is_valid'] = empty($validation['errors']);
        $validation['can_restore'] = $validation['is_valid'] && $validation['device_exists'];

        return $validation;
    }

    public function cleanupOldEntries(int $daysToKeep = 90): int
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        // Compter avant suppression pour le retour
        $countToDelete = $this->model->where('created_at', '<', $cutoffDate)
            ->whereIn('change_type', ['auto_backup']) // Seulement les backups automatiques
            ->count();

        // Supprimer les entrées
        $deleted = $this->model->where('created_at', '<', $cutoffDate)
            ->whereIn('change_type', ['auto_backup'])
            ->delete();

        $this->clearHistoryCache();

        return $deleted;
    }

    /**
     * Générer un diff entre deux configurations
     */
    private function generateDiff(?string $config1, ?string $config2): string
    {
        if (empty($config1) || empty($config2)) {
            return 'Une des configurations est vide';
        }

        $lines1 = explode("\n", $config1);
        $lines2 = explode("\n", $config2);
        
        $diff = '';
        $maxLines = max(count($lines1), count($lines2));

        for ($i = 0; $i < $maxLines; $i++) {
            $line1 = $lines1[$i] ?? '';
            $line2 = $lines2[$i] ?? '';
            
            if ($line1 !== $line2) {
                $diff .= sprintf("Ligne %d:\n", $i + 1);
                $diff .= sprintf("  - %s\n", $line1);
                $diff .= sprintf("  + %s\n", $line2);
                $diff .= "\n";
            }
        }

        return $diff ?: 'Les configurations sont identiques';
    }

    /**
     * Analyser le diff
     */
    private function analyzeDiff(string $diff): array
    {
        if ($diff === 'Les configurations sont identiques') {
            return [
                'identical' => true,
                'changes_count' => 0,
                'message' => 'Aucun changement détecté'
            ];
        }

        $lines = explode("\n", $diff);
        $changesCount = 0;
        $addedLines = 0;
        $removedLines = 0;

        foreach ($lines as $line) {
            if (str_starts_with($line, 'Ligne')) {
                $changesCount++;
            }
            if (str_starts_with($line, '  - ') && !str_starts_with($line, '  - (vide)')) {
                $removedLines++;
            }
            if (str_starts_with($line, '  + ') && !str_starts_with($line, '  + (vide)')) {
                $addedLines++;
            }
        }

        return [
            'identical' => false,
            'changes_count' => $changesCount,
            'added_lines' => $addedLines,
            'removed_lines' => $removedLines,
            'summary' => sprintf(
                '%d changement(s), %d ligne(s) ajoutée(s), %d ligne(s) supprimée(s)',
                $changesCount,
                $addedLines,
                $removedLines
            )
        ];
    }

    /**
     * Formatter les octets en unités lisibles
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Obtenir l'historique des restaurations
     */
    public function getRestorationHistory(): Collection
    {
        return Cache::remember('configuration_history.restorations', $this->cacheTtl, function () {
            return $this->model->where('change_type', 'restore')
                ->with(['device', 'user', 'restoredFrom'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Vérifier les intégrités des configurations
     */
    public function checkIntegrity(): array
    {
        $histories = $this->model->whereNotNull('config_checksum')->get();
        
        $results = [
            'total_checked' => 0,
            'valid' => 0,
            'invalid' => 0,
            'details' => []
        ];

        foreach ($histories as $history) {
            $results['total_checked']++;
            
            $currentChecksum = md5($history->configuration ?? '');
            $isValid = $currentChecksum === $history->config_checksum;
            
            if ($isValid) {
                $results['valid']++;
            } else {
                $results['invalid']++;
            }

            $results['details'][] = [
                'id' => $history->id,
                'device' => $history->device->name ?? 'Inconnu',
                'device_type' => class_basename($history->device_type),
                'created_at' => $history->created_at->format('Y-m-d H:i:s'),
                'is_valid' => $isValid,
                'expected_checksum' => $history->config_checksum,
                'actual_checksum' => $currentChecksum
            ];
        }

        return $results;
    }

    /**
     * Vider le cache de l'historique
     */
    private function clearHistoryCache(?int $historyId = null): void
    {
        Cache::forget('configuration_history.all');
        Cache::forget('configuration_history.statistics');
        Cache::forget('configuration_history.backups');
        Cache::forget('configuration_history.restorations');
        
        // Nettoyer les caches par période
        for ($i = 1; $i <= 30; $i++) {
            Cache::forget("configuration_history.recent.{$i}");
        }
        
        if ($historyId) {
            Cache::forget("configuration_history.{$historyId}");
        }
        
        // Nettoyer les caches par type de changement
        $changeTypes = ['create', 'update', 'backup', 'restore', 'auto_backup', 'manual_backup'];
        foreach ($changeTypes as $type) {
            Cache::forget("configuration_history.change_type.{$type}");
        }
    }
}