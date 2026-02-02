<?php

namespace App\Services;

use App\Repositories\RouterRepository;
use App\Repositories\ConfigurationHistoryRepository;
use App\Events\RouterCreated;
use App\Events\RouterUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class RouterService
{
    public function __construct(
        protected RouterRepository $routerRepository,
        protected ConfigurationHistoryRepository $historyRepository
    ) {}

    public function createRouter(array $data)
    {
        DB::beginTransaction();
        
        try {
            Log::info('[RouterService] Création d\'un nouveau routeur', ['data' => $this->sanitizeData($data)]);
            
            // Valider les données
            $this->validateRouterData($data);
            
            // Créer le routeur
            $router = $this->routerRepository->create($data);
            
            // Créer l'historique initial
            $this->historyRepository->create([
                'device_type' => \App\Models\Router::class,
                'device_id' => $router->id,
                'change_type' => 'create',
                'configuration' => $router->configuration ?? null,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Création initiale du routeur',
            ]);
            
            // Déclencher l'événement
            event(new RouterCreated($router, [
                'userId' => auth()->id(),
                'ipAddress' => request()->ip(),
                'metadata' => [
                    'interfaces_count' => count($router->interfaces ?? []),
                    'site' => $router->site?->name,
                ]
            ]));
            
            DB::commit();
            
            Log::info('[RouterService] Routeur créé avec succès', ['router_id' => $router->id]);
            
            return $router;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[RouterService] Erreur lors de la création du routeur: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateRouter(int $routerId, array $data)
    {
        DB::beginTransaction();
        
        try {
            $router = $this->routerRepository->find($routerId);
            
            if (!$router) {
                throw new Exception("Routeur non trouvé: {$routerId}");
            }
            
            Log::info('[RouterService] Mise à jour du routeur', [
                'router_id' => $routerId,
                'changes' => $this->getChangedFields($router, $data),
            ]);
            
            // Sauvegarder l'ancien état
            $originalData = $router->toArray();
            
            // Mettre à jour le routeur
            $updated = $this->routerRepository->update($routerId, $data);
            
            if (!$updated) {
                throw new Exception("Échec de la mise à jour du routeur");
            }
            
            // Récupérer le routeur mis à jour
            $router = $router->fresh();
            
            // Créer l'historique des changements
            $changes = $this->calculateChanges($originalData, $router->toArray());
            
            $this->historyRepository->create([
                'device_type' => \App\Models\Router::class,
                'device_id' => $routerId,
                'change_type' => 'update',
                'configuration' => $router->configuration ?? null,
                'pre_change_config' => $originalData,
                'post_change_config' => $router->toArray(),
                'change_summary' => json_encode($changes),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Mise à jour du routeur',
            ]);
            
            // Déclencher l'événement
            event(new RouterUpdated($router, $changes, $originalData, [
                'userId' => auth()->id(),
                'ipAddress' => request()->ip(),
            ]));
            
            DB::commit();
            
            Log::info('[RouterService] Routeur mis à jour avec succès', ['router_id' => $routerId]);
            
            return $router;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[RouterService] Erreur lors de la mise à jour du routeur: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getRouter(int $routerId)
    {
        try {
            $router = $this->routerRepository->findWithRelations($routerId, [
                'site',
                'configurationHistories' => function ($query) {
                    $query->latest()->limit(10);
                },
                'accessLogs' => function ($query) {
                    $query->latest()->limit(20);
                },
            ]);
            
            if (!$router) {
                throw new Exception("Routeur non trouvé: {$routerId}");
            }
            
            // Ajouter des données calculées
            $router->backup_status = $this->calculateBackupStatus($router);
            $router->interface_stats = $this->calculateInterfaceStats($router);
            $router->routing_stats = $this->calculateRoutingStats($router);
            
            return $router;
            
        } catch (Exception $e) {
            Log::error('[RouterService] Erreur lors de la récupération du routeur: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteRouter(int $routerId): bool
    {
        DB::beginTransaction();
        
        try {
            $router = $this->routerRepository->find($routerId);
            
            if (!$router) {
                throw new Exception("Routeur non trouvé: {$routerId}");
            }
            
            Log::info('[RouterService] Suppression du routeur', ['router_id' => $routerId]);
            
            // Créer un backup avant suppression
            $this->createPreDeleteBackup($router);
            
            // Supprimer le routeur
            $deleted = $this->routerRepository->delete($routerId);
            
            if (!$deleted) {
                throw new Exception("Échec de la suppression du routeur");
            }
            
            // Journaliser l'action
            $this->logDeletion($router);
            
            DB::commit();
            
            Log::info('[RouterService] Routeur supprimé avec succès', ['router_id' => $routerId]);
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[RouterService] Erreur lors de la suppression du routeur: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getRouterStatistics(): array
    {
        try {
            return [
                'total' => $this->routerRepository->count(),
                'by_brand' => $this->routerRepository->countByBrand(),
                'by_status' => $this->routerRepository->countByStatus(),
                'by_site' => $this->routerRepository->countBySite(),
                'needing_backup' => $this->routerRepository->countNeedingBackup(),
                'average_interfaces_per_router' => $this->routerRepository->averageInterfacesCount(),
                'recently_updated' => $this->routerRepository->getRecentlyUpdated(10),
            ];
        } catch (Exception $e) {
            Log::error('[RouterService] Erreur lors du calcul des statistiques: ' . $e->getMessage());
            return [];
        }
    }

    public function updateInterfaces(int $routerId, array $interfaces)
    {
        DB::beginTransaction();
        
        try {
            $router = $this->routerRepository->find($routerId);
            
            if (!$router) {
                throw new Exception("Routeur non trouvé: {$routerId}");
            }
            
            Log::info('[RouterService] Mise à jour des interfaces', [
                'router_id' => $routerId,
                'interfaces_count' => count($interfaces),
            ]);
            
            // Valider les interfaces
            $this->validateInterfaces($interfaces);
            
            // Sauvegarder l'ancien état
            $originalInterfaces = $router->interfaces ?? [];
            
            // Mettre à jour les interfaces
            $updated = $this->routerRepository->update($routerId, [
                'interfaces' => $interfaces,
            ]);
            
            if (!$updated) {
                throw new Exception("Échec de la mise à jour des interfaces");
            }
            
            // Créer l'historique spécifique
            $this->historyRepository->create([
                'device_type' => \App\Models\Router::class,
                'device_id' => $routerId,
                'change_type' => 'interface_update',
                'configuration' => json_encode($interfaces),
                'pre_change_config' => json_encode($originalInterfaces),
                'post_change_config' => json_encode($interfaces),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Mise à jour des interfaces',
            ]);
            
            DB::commit();
            
            Log::info('[RouterService] Interfaces mises à jour avec succès', [
                'router_id' => $routerId,
                'interfaces_count' => count($interfaces),
            ]);
            
            return $this->routerRepository->find($routerId);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[RouterService] Erreur lors de la mise à jour des interfaces: ' . $e->getMessage());
            throw $e;
        }
    }

    public function testConnectivity(int $routerId): array
    {
        try {
            $router = $this->routerRepository->find($routerId);
            
            if (!$router) {
                throw new Exception("Routeur non trouvé: {$routerId}");
            }
            
            Log::info('[RouterService] Test de connectivité du routeur', ['router_id' => $routerId]);
            
            $results = [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'tests' => [],
                'overall_status' => 'unknown',
                'timestamp' => now()->toISOString(),
            ];
            
            // Test de ping sur l'IP de management
            if ($router->management_ip) {
                $pingResult = $this->testPing($router->management_ip);
                $results['tests']['ping_management'] = $pingResult;
            }
            
            // Test des interfaces actives
            $interfaceResults = $this->testInterfaces($router);
            $results['tests']['interfaces'] = $interfaceResults;
            
            // Test des credentials (simulé)
            $authResult = $this->testAuthentication($router);
            $results['tests']['authentication'] = $authResult;
            
            // Déterminer le statut global
            $successfulTests = array_filter($results['tests'], fn($test) => $test['status'] === 'success');
            $results['overall_status'] = count($successfulTests) === count($results['tests']) ? 'success' : 'partial';
            
            if (empty($successfulTests)) {
                $results['overall_status'] = 'failed';
            }
            
            // Journaliser les résultats
            $this->logConnectivityTest($router, $results);
            
            return $results;
            
        } catch (Exception $e) {
            Log::error('[RouterService] Erreur lors du test de connectivité: ' . $e->getMessage());
            throw $e;
        }
    }

    private function validateRouterData(array $data): void
    {
        $requiredFields = ['name', 'brand', 'model', 'site_id'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Le champ '{$field}' est requis");
            }
        }
        
        // Valider l'adresse IP de management
        if (!empty($data['management_ip']) && !filter_var($data['management_ip'], FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("Adresse IP de management invalide");
        }
        
        // Valider les interfaces si fournies
        if (!empty($data['interfaces'])) {
            $interfaces = is_string($data['interfaces']) ? 
                json_decode($data['interfaces'], true) : $data['interfaces'];
            
            if ($interfaces && !is_array($interfaces)) {
                throw new \InvalidArgumentException("Format d'interfaces invalide");
            }
        }
    }

    private function validateInterfaces(array $interfaces): void
    {
        foreach ($interfaces as $index => $interface) {
            if (empty($interface['name'])) {
                throw new \InvalidArgumentException("L'interface #{$index} doit avoir un nom");
            }
            
            if (!empty($interface['ip_address']) && !filter_var($interface['ip_address'], FILTER_VALIDATE_IP)) {
                throw new \InvalidArgumentException("Adresse IP invalide pour l'interface '{$interface['name']}'");
            }
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

    private function getChangedFields($router, array $newData): array
    {
        $changes = [];
        $original = $router->toArray();
        
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

    private function calculateBackupStatus($router): array
    {
        if (!$router->last_backup) {
            return ['status' => 'danger', 'message' => 'Jamais sauvegardé'];
        }
        
        $daysSinceBackup = $router->last_backup->diffInDays(now());
        
        if ($daysSinceBackup <= 1) {
            return ['status' => 'success', 'message' => 'Récent (<24h)'];
        } elseif ($daysSinceBackup <= 7) {
            return ['status' => 'warning', 'message' => 'Modéré (<7 jours)'];
        } else {
            return ['status' => 'danger', 'message' => 'Ancien (>7 jours)'];
        }
    }

    private function calculateInterfaceStats($router): array
    {
        $interfaces = $router->interfaces ?? [];
        
        if (empty($interfaces)) {
            return [
                'total' => 0,
                'up' => 0,
                'down' => 0,
                'up_percentage' => 0,
            ];
        }
        
        $up = 0;
        $down = 0;
        
        foreach ($interfaces as $interface) {
            if (($interface['status'] ?? 'down') === 'up') {
                $up++;
            } else {
                $down++;
            }
        }
        
        $total = $up + $down;
        
        return [
            'total' => $total,
            'up' => $up,
            'down' => $down,
            'up_percentage' => $total > 0 ? round(($up / $total) * 100, 2) : 0,
        ];
    }

    private function calculateRoutingStats($router): array
    {
        $protocols = $router->routing_protocols ?? [];
        
        return [
            'bgp_enabled' => $protocols['bgp']['enabled'] ?? false,
            'ospf_enabled' => $protocols['ospf']['enabled'] ?? false,
            'static_routes' => $protocols['static_routes'] ?? 0,
        ];
    }

    private function createPreDeleteBackup($router): void
    {
        try {
            $this->historyRepository->create([
                'device_type' => \App\Models\Router::class,
                'device_id' => $router->id,
                'change_type' => 'pre_delete_backup',
                'configuration' => $router->configuration ?? null,
                'interfaces' => $router->interfaces ?? null,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Backup avant suppression du routeur',
            ]);
            
            Log::info('[RouterService] Backup avant suppression créé', ['router_id' => $router->id]);
        } catch (Exception $e) {
            Log::error('[RouterService] Erreur lors de la création du backup avant suppression: ' . $e->getMessage());
        }
    }

    private function logDeletion($router): void
    {
        try {
            \App\Models\AccessLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'device_type' => \App\Models\Router::class,
                'device_id' => $router->id,
                'parameters' => [
                    'router_name' => $router->name,
                    'site' => $router->site?->name,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'result' => 'success',
            ]);
        } catch (Exception $e) {
            Log::error('[RouterService] Erreur lors de la journalisation de la suppression: ' . $e->getMessage());
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

    private function testInterfaces($router): array
    {
        $interfaces = $router->interfaces ?? [];
        $results = [
            'total' => count($interfaces),
            'up' => 0,
            'down' => 0,
            'interfaces' => [],
        ];
        
        foreach ($interfaces as $index => $interface) {
            $status = $interface['status'] ?? 'down';
            $results['interfaces'][] = [
                'name' => $interface['name'] ?? "Interface {$index}",
                'status' => $status,
                'ip' => $interface['ip_address'] ?? null,
            ];
            
            if ($status === 'up') {
                $results['up']++;
            } else {
                $results['down']++;
            }
        }
        
        return [
            'status' => $results['up'] > 0 ? ($results['up'] === $results['total'] ? 'success' : 'partial') : 'failed',
            'results' => $results,
        ];
    }

    private function testAuthentication($router): array
    {
        return [
            'status' => (!empty($router->username) && !empty($router->password)) ? 'success' : 'warning',
            'message' => (!empty($router->username) && !empty($router->password)) ? 
                'Credentials disponibles' : 'Credentials incomplets',
            'has_username' => !empty($router->username),
            'has_password' => !empty($router->password),
        ];
    }

    private function logConnectivityTest($router, array $results): void
    {
        try {
            \App\Models\AccessLog::create([
                'user_id' => auth()->id(),
                'action' => 'connectivity_test',
                'device_type' => \App\Models\Router::class,
                'device_id' => $router->id,
                'parameters' => [
                    'overall_status' => $results['overall_status'],
                    'tests_performed' => count($results['tests']),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'result' => $results['overall_status'] === 'success' ? 'success' : 'warning',
            ]);
        } catch (Exception $e) {
            Log::error('[RouterService] Erreur lors de la journalisation du test de connectivité: ' . $e->getMessage());
        }
    }
}