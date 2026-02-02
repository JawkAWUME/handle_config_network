<?php

namespace App\Services;

use App\Repositories\SwitchRepository;
use App\Repositories\ConfigurationHistoryRepository;
use App\Events\SwitchCreated;
use App\Events\SwitchUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class SwitchService
{
    public function __construct(
        protected SwitchRepository $switchRepository,
        protected ConfigurationHistoryRepository $historyRepository
    ) {}

    public function createSwitch(array $data)
    {
        DB::beginTransaction();
        
        try {
            Log::info('[SwitchService] Création d\'un nouveau switch', ['data' => $this->sanitizeData($data)]);
            
            // Valider les données
            $this->validateSwitchData($data);
            
            // Calculer le nombre de ports utilisés si non fourni
            if (!isset($data['ports_used']) && isset($data['ports_total'])) {
                $data['ports_used'] = $this->estimatePortsUsed($data);
            }
            
            // Créer le switch
            $switch = $this->switchRepository->create($data);
            
            // Créer l'historique initial
            $this->historyRepository->create([
                'device_type' => \App\Models\SwitchModel::class,
                'device_id' => $switch->id,
                'change_type' => 'create',
                'configuration' => $switch->configuration ?? null,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Création initiale du switch',
            ]);
            
            // Déclencher l'événement
            event(new SwitchCreated($switch, [
                'userId' => auth()->id(),
                'ipAddress' => request()->ip(),
                'metadata' => [
                    'ports_total' => $switch->ports_total,
                    'ports_used' => $switch->ports_used,
                    'site' => $switch->site?->name,
                ]
            ]));
            
            DB::commit();
            
            Log::info('[SwitchService] Switch créé avec succès', ['switch_id' => $switch->id]);
            
            return $switch;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[SwitchService] Erreur lors de la création du switch: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateSwitch(int $switchId, array $data)
    {
        DB::beginTransaction();
        
        try {
            $switch = $this->switchRepository->find($switchId);
            
            if (!$switch) {
                throw new Exception("Switch non trouvé: {$switchId}");
            }
            
            Log::info('[SwitchService] Mise à jour du switch', [
                'switch_id' => $switchId,
                'changes' => $this->getChangedFields($switch, $data),
            ]);
            
            // Sauvegarder l'ancien état
            $originalData = $switch->toArray();
            
            // Mettre à jour le switch
            $updated = $this->switchRepository->update($switchId, $data);
            
            if (!$updated) {
                throw new Exception("Échec de la mise à jour du switch");
            }
            
            // Récupérer le switch mis à jour
            $switch = $switch->fresh();
            
            // Créer l'historique des changements
            $changes = $this->calculateChanges($originalData, $switch->toArray());
            
            $this->historyRepository->create([
                'device_type' => \App\Models\SwitchModel::class,
                'device_id' => $switchId,
                'change_type' => 'update',
                'configuration' => $switch->configuration ?? null,
                'pre_change_config' => $originalData,
                'post_change_config' => $switch->toArray(),
                'change_summary' => json_encode($changes),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Mise à jour du switch',
            ]);
            
            // Déclencher l'événement
            event(new SwitchUpdated($switch, $changes, [
                'userId' => auth()->id(),
                'ipAddress' => request()->ip(),
            ]));
            
            DB::commit();
            
            Log::info('[SwitchService] Switch mis à jour avec succès', ['switch_id' => $switchId]);
            
            return $switch;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[SwitchService] Erreur lors de la mise à jour du switch: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getSwitch(int $switchId)
    {
        try {
            $switch = $this->switchRepository->findWithRelations($switchId, [
                'site',
                'configurationHistories' => function ($query) {
                    $query->latest()->limit(10);
                },
                'accessLogs' => function ($query) {
                    $query->latest()->limit(20);
                },
            ]);
            
            if (!$switch) {
                throw new Exception("Switch non trouvé: {$switchId}");
            }
            
            // Ajouter des données calculées
            $switch->backup_status = $this->calculateBackupStatus($switch);
            $switch->port_utilization = $this->calculatePortUtilization($switch);
            $switch->vlan_info = $this->extractVlanInfo($switch);
            
            return $switch;
            
        } catch (Exception $e) {
            Log::error('[SwitchService] Erreur lors de la récupération du switch: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteSwitch(int $switchId): bool
    {
        DB::beginTransaction();
        
        try {
            $switch = $this->switchRepository->find($switchId);
            
            if (!$switch) {
                throw new Exception("Switch non trouvé: {$switchId}");
            }
            
            Log::info('[SwitchService] Suppression du switch', ['switch_id' => $switchId]);
            
            // Créer un backup avant suppression
            $this->createPreDeleteBackup($switch);
            
            // Supprimer le switch
            $deleted = $this->switchRepository->delete($switchId);
            
            if (!$deleted) {
                throw new Exception("Échec de la suppression du switch");
            }
            
            // Journaliser l'action
            $this->logDeletion($switch);
            
            DB::commit();
            
            Log::info('[SwitchService] Switch supprimé avec succès', ['switch_id' => $switchId]);
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[SwitchService] Erreur lors de la suppression du switch: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getSwitchStatistics(): array
    {
        try {
            return [
                'total' => $this->switchRepository->count(),
                'by_brand' => $this->switchRepository->countByBrand(),
                'by_status' => $this->switchRepository->countByStatus(),
                'by_site' => $this->switchRepository->countBySite(),
                'by_type' => $this->switchRepository->countByType(),
                'needing_backup' => $this->switchRepository->countNeedingBackup(),
                'total_ports' => $this->switchRepository->sumPortsTotal(),
                'used_ports' => $this->switchRepository->sumPortsUsed(),
                'average_utilization' => $this->switchRepository->averageUtilization(),
                'recently_updated' => $this->switchRepository->getRecentlyUpdated(10),
            ];
        } catch (Exception $e) {
            Log::error('[SwitchService] Erreur lors du calcul des statistiques: ' . $e->getMessage());
            return [];
        }
    }

    public function updatePortConfiguration(int $switchId, array $portConfig)
    {
        DB::beginTransaction();
        
        try {
            $switch = $this->switchRepository->find($switchId);
            
            if (!$switch) {
                throw new Exception("Switch non trouvé: {$switchId}");
            }
            
            Log::info('[SwitchService] Mise à jour de la configuration des ports', [
                'switch_id' => $switchId,
                'config_size' => count($portConfig),
            ]);
            
            // Valider la configuration des ports
            $this->validatePortConfig($portConfig);
            
            // Calculer les ports utilisés
            $portsUsed = $this->calculatePortsUsedFromConfig($portConfig);
            
            // Mettre à jour le switch
            $updated = $this->switchRepository->update($switchId, [
                'configuration' => json_encode($portConfig),
                'ports_used' => $portsUsed,
            ]);
            
            if (!$updated) {
                throw new Exception("Échec de la mise à jour de la configuration");
            }
            
            // Créer l'historique spécifique
            $this->historyRepository->create([
                'device_type' => \App\Models\SwitchModel::class,
                'device_id' => $switchId,
                'change_type' => 'port_config_update',
                'configuration' => json_encode($portConfig),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Mise à jour de la configuration des ports',
            ]);
            
            DB::commit();
            
            Log::info('[SwitchService] Configuration des ports mise à jour avec succès', [
                'switch_id' => $switchId,
                'ports_used' => $portsUsed,
            ]);
            
            return $this->switchRepository->find($switchId);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[SwitchService] Erreur lors de la mise à jour de la configuration: ' . $e->getMessage());
            throw $e;
        }
    }

    public function testConnectivity(int $switchId): array
    {
        try {
            $switch = $this->switchRepository->find($switchId);
            
            if (!$switch) {
                throw new Exception("Switch non trouvé: {$switchId}");
            }
            
            Log::info('[SwitchService] Test de connectivité du switch', ['switch_id' => $switchId]);
            
            $results = [
                'switch_id' => $switchId,
                'switch_name' => $switch->name,
                'tests' => [],
                'overall_status' => 'unknown',
                'timestamp' => now()->toISOString(),
            ];
            
            // Test de ping sur l'IP NMS
            if ($switch->ip_nms) {
                $pingResult = $this->testPing($switch->ip_nms);
                $results['tests']['ping_nms'] = $pingResult;
            }
            
            // Test de ping sur l'IP Service
            if ($switch->ip_service) {
                $pingResult = $this->testPing($switch->ip_service);
                $results['tests']['ping_service'] = $pingResult;
            }
            
            // Test de disponibilité des ports
            $portResult = $this->testPortAvailability($switch);
            $results['tests']['port_availability'] = $portResult;
            
            // Test des credentials (simulé)
            $authResult = $this->testAuthentication($switch);
            $results['tests']['authentication'] = $authResult;
            
            // Déterminer le statut global
            $successfulTests = array_filter($results['tests'], fn($test) => $test['status'] === 'success');
            $results['overall_status'] = count($successfulTests) === count($results['tests']) ? 'success' : 'partial';
            
            if (empty($successfulTests)) {
                $results['overall_status'] = 'failed';
            }
            
            // Journaliser les résultats
            $this->logConnectivityTest($switch, $results);
            
            return $results;
            
        } catch (Exception $e) {
            Log::error('[SwitchService] Erreur lors du test de connectivité: ' . $e->getMessage());
            throw $e;
        }
    }

    private function validateSwitchData(array $data): void
    {
        $requiredFields = ['name', 'brand', 'model', 'site_id', 'ports_total'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Le champ '{$field}' est requis");
            }
        }
        
        // Valider l'adresse IP NMS
        if (!empty($data['ip_nms']) && !filter_var($data['ip_nms'], FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("Adresse IP NMS invalide");
        }
        
        // Valider les ports
        if (!empty($data['ports_total']) && (!is_int($data['ports_total']) || $data['ports_total'] <= 0)) {
            throw new \InvalidArgumentException("Nombre total de ports invalide");
        }
        
        if (!empty($data['ports_used']) && (!is_int($data['ports_used']) || $data['ports_used'] < 0)) {
            throw new \InvalidArgumentException("Nombre de ports utilisés invalide");
        }
        
        if (!empty($data['ports_total']) && !empty($data['ports_used']) && $data['ports_used'] > $data['ports_total']) {
            throw new \InvalidArgumentException("Les ports utilisés ne peuvent pas dépasser le total des ports");
        }
    }

    private function validatePortConfig(array $portConfig): void
    {
        foreach ($portConfig as $port => $config) {
            if (!is_numeric($port) || $port < 1 || $port > 48) {
                throw new \InvalidArgumentException("Numéro de port invalide: {$port}");
            }
            
            if (empty($config['status']) || !in_array($config['status'], ['enabled', 'disabled'])) {
                throw new \InvalidArgumentException("Statut invalide pour le port {$port}");
            }
            
            if (!empty($config['vlan']) && (!is_int($config['vlan']) || $config['vlan'] < 1 || $config['vlan'] > 4094)) {
                throw new \InvalidArgumentException("VLAN invalide pour le port {$port}");
            }
        }
    }

    private function estimatePortsUsed(array $data): int
    {
        $portsTotal = $data['ports_total'] ?? 0;
        
        // Estimation basée sur le type de switch
        $brand = strtolower($data['brand'] ?? '');
        $model = strtolower($data['model'] ?? '');
        
        if (str_contains($brand . $model, 'core') || $portsTotal >= 48) {
            // Switch core: utilisation élevée
            return (int) round($portsTotal * 0.7);
        } elseif (str_contains($brand . $model, 'access') || $portsTotal <= 24) {
            // Switch d'accès: utilisation moyenne
            return (int) round($portsTotal * 0.5);
        } else {
            // Par défaut: 60% d'utilisation
            return (int) round($portsTotal * 0.6);
        }
    }

    private function sanitizeData(array $data): array
    {
        $sensitiveFields = ['password'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***MASKED***';
            }
        }
        
        return $data;
    }

    private function getChangedFields($switch, array $newData): array
    {
        $changes = [];
        $original = $switch->toArray();
        
        foreach ($newData as $key => $value) {
            if (array_key_exists($key, $original) && $original[$key] != $value) {
                $changes[$key] = [
                    'before' => $original[$key],
                    'after' => $value,
                ];
            }
        }
        
        return $changes;
    }

    private function calculateChanges(array $oldData, array $newData): array
    {
        $changes = [];
        
        foreach ($newData as $key => $value) {
            if (!array_key_exists($key, $oldData) || $oldData[$key] != $value) {
                $changes[$key] = [
                    'old' => $oldData[$key] ?? null,
                    'new' => $value,
                ];
            }
        }
        
        return $changes;
    }

    private function calculateBackupStatus($switch): array
    {
        if (!$switch->last_backup) {
            return ['status' => 'danger', 'message' => 'Jamais sauvegardé'];
        }
        
        $daysSinceBackup = $switch->last_backup->diffInDays(now());
        
        if ($daysSinceBackup <= 1) {
            return ['status' => 'success', 'message' => 'Récent (<24h)'];
        } elseif ($daysSinceBackup <= 7) {
            return ['status' => 'warning', 'message' => 'Modéré (<7 jours)'];
        } else {
            return ['status' => 'danger', 'message' => 'Ancien (>7 jours)'];
        }
    }

    private function calculatePortUtilization($switch): array
    {
        $total = $switch->ports_total ?? 0;
        $used = $switch->ports_used ?? 0;
        
        if ($total === 0) {
            return [
                'percentage' => 0,
                'status' => 'unknown',
                'available' => 0,
            ];
        }
        
        $percentage = round(($used / $total) * 100, 2);
        
        if ($percentage >= 90) {
            $status = 'danger';
        } elseif ($percentage >= 70) {
            $status = 'warning';
        } elseif ($percentage >= 50) {
            $status = 'info';
        } else {
            $status = 'success';
        }
        
        return [
            'percentage' => $percentage,
            'status' => $status,
            'total' => $total,
            'used' => $used,
            'available' => $total - $used,
        ];
    }

    private function extractVlanInfo($switch): array
    {
        return [
            'vlan_nms' => $switch->vlan_nms,
            'vlan_service' => $switch->vlan_service,
            'has_vlan_configuration' => !empty($switch->vlan_nms) || !empty($switch->vlan_service),
        ];
    }

    private function calculatePortsUsedFromConfig(array $portConfig): int
    {
        $used = 0;
        
        foreach ($portConfig as $config) {
            if (($config['status'] ?? 'disabled') === 'enabled') {
                $used++;
            }
        }
        
        return $used;
    }

    private function createPreDeleteBackup($switch): void
    {
        try {
            $this->historyRepository->create([
                'device_type' => \App\Models\SwitchModel::class,
                'device_id' => $switch->id,
                'change_type' => 'pre_delete_backup',
                'configuration' => $switch->configuration ?? null,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Backup avant suppression du switch',
            ]);
            
            Log::info('[SwitchService] Backup avant suppression créé', ['switch_id' => $switch->id]);
        } catch (Exception $e) {
            Log::error('[SwitchService] Erreur lors de la création du backup avant suppression: ' . $e->getMessage());
        }
    }

    private function logDeletion($switch): void
    {
        try {
            \App\Models\AccessLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'device_type' => \App\Models\SwitchModel::class,
                'device_id' => $switch->id,
                'parameters' => [
                    'switch_name' => $switch->name,
                    'site' => $switch->site?->name,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'result' => 'success',
            ]);
        } catch (Exception $e) {
            Log::error('[SwitchService] Erreur lors de la journalisation de la suppression: ' . $e->getMessage());
        }
    }

    private function testPing(string $ip): array
    {
        $command = sprintf('ping -c 1 -W 1 %s', escapeshellarg($ip));
        exec($command, $output, $result);
        
        return [
            'status' => $result === 0 ? 'success' : 'failed',
            'ip' => $ip,
            'result' => $result,
            'output' => $output[0] ?? 'No output',
        ];
    }

    private function testPortAvailability($switch): array
    {
        $total = $switch->ports_total ?? 0;
        $used = $switch->ports_used ?? 0;
        
        if ($total === 0) {
            return [
                'status' => 'unknown',
                'message' => 'Aucune information sur les ports',
            ];
        }
        
        $available = $total - $used;
        
        return [
            'status' => $available > 0 ? 'success' : ($available === 0 ? 'warning' : 'error'),
            'total_ports' => $total,
            'used_ports' => $used,
            'available_ports' => $available,
            'utilization_percentage' => round(($used / $total) * 100, 2),
        ];
    }

    private function testAuthentication($switch): array
    {
        return [
            'status' => (!empty($switch->username) && !empty($switch->password)) ? 'success' : 'warning',
            'message' => (!empty($switch->username) && !empty($switch->password)) ? 
                'Credentials disponibles' : 'Credentials incomplets',
            'has_username' => !empty($switch->username),
            'has_password' => !empty($switch->password),
        ];
    }

    private function logConnectivityTest($switch, array $results): void
    {
        try {
            \App\Models\AccessLog::create([
                'user_id' => auth()->id(),
                'action' => 'connectivity_test',
                'device_type' => \App\Models\SwitchModel::class,
                'device_id' => $switch->id,
                'parameters' => [
                    'overall_status' => $results['overall_status'],
                    'tests_performed' => count($results['tests']),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'result' => $results['overall_status'] === 'success' ? 'success' : 'warning',
            ]);
        } catch (Exception $e) {
            Log::error('[SwitchService] Erreur lors de la journalisation du test de connectivité: ' . $e->getMessage());
        }
    }
}