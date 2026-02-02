<?php

namespace App\Services;

use App\Repositories\FirewallRepository;
use App\Repositories\ConfigurationHistoryRepository;
use App\Events\FirewallCreated;
use App\Events\FirewallUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class FirewallService
{
    public function __construct(
        protected FirewallRepository $firewallRepository,
        protected ConfigurationHistoryRepository $historyRepository
    ) {}

    public function createFirewall(array $data)
    {
        DB::beginTransaction();
        
        try {
            Log::info('[FirewallService] Création d\'un nouveau firewall', ['data' => $this->sanitizeData($data)]);
            
            // Valider les données
            $this->validateFirewallData($data);
            
            // Créer le firewall
            $firewall = $this->firewallRepository->create($data);
            
            // Créer l'historique initial
            $this->historyRepository->create([
                'device_type' => \App\Models\Firewall::class,
                'device_id' => $firewall->id,
                'change_type' => 'create',
                'configuration' => $firewall->configuration ?? null,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Création initiale du firewall',
            ]);
            
            // Déclencher l'événement
            event(new FirewallCreated($firewall, [
                'userId' => auth()->id(),
                'ipAddress' => request()->ip(),
                'metadata' => [
                    'rules_count' => count($firewall->security_policies ?? []),
                    'site' => $firewall->site?->name,
                ]
            ]));
            
            DB::commit();
            
            Log::info('[FirewallService] Firewall créé avec succès', ['firewall_id' => $firewall->id]);
            
            return $firewall;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FirewallService] Erreur lors de la création du firewall: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateFirewall(int $firewallId, array $data)
    {
        DB::beginTransaction();
        
        try {
            $firewall = $this->firewallRepository->find($firewallId);
            
            if (!$firewall) {
                throw new Exception("Firewall non trouvé: {$firewallId}");
            }
            
            Log::info('[FirewallService] Mise à jour du firewall', [
                'firewall_id' => $firewallId,
                'changes' => $this->getChangedFields($firewall, $data),
            ]);
            
            // Sauvegarder l'ancien état
            $originalData = $firewall->toArray();
            
            // Mettre à jour le firewall
            $updated = $this->firewallRepository->update($firewallId, $data);
            
            if (!$updated) {
                throw new Exception("Échec de la mise à jour du firewall");
            }
            
            // Récupérer le firewall mis à jour
            $firewall = $firewall->fresh();
            
            // Créer l'historique des changements
            $changes = $this->calculateChanges($originalData, $firewall->toArray());
            
            $this->historyRepository->create([
                'device_type' => \App\Models\Firewall::class,
                'device_id' => $firewallId,
                'change_type' => 'update',
                'configuration' => $firewall->configuration ?? null,
                'pre_change_config' => $originalData,
                'post_change_config' => $firewall->toArray(),
                'change_summary' => json_encode($changes),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Mise à jour du firewall',
            ]);
            
            // Déclencher l'événement
            event(new FirewallUpdated($firewall, $changes, $originalData, [
                'userId' => auth()->id(),
                'ipAddress' => request()->ip(),
            ]));
            
            DB::commit();
            
            Log::info('[FirewallService] Firewall mis à jour avec succès', ['firewall_id' => $firewallId]);
            
            return $firewall;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FirewallService] Erreur lors de la mise à jour du firewall: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getFirewall(int $firewallId)
    {
        try {
            $firewall = $this->firewallRepository->findWithRelations($firewallId, [
                'site',
                'configurationHistories' => function ($query) {
                    $query->latest()->limit(10);
                },
                'accessLogs' => function ($query) {
                    $query->latest()->limit(20);
                },
            ]);
            
            if (!$firewall) {
                throw new Exception("Firewall non trouvé: {$firewallId}");
            }
            
            // Ajouter des données calculées
            $firewall->backup_status = $this->calculateBackupStatus($firewall);
            $firewall->license_status = $this->calculateLicenseStatus($firewall);
            $firewall->ha_status = $this->calculateHaStatus($firewall);
            $firewall->security_score = $this->calculateSecurityScore($firewall);
            
            return $firewall;
            
        } catch (Exception $e) {
            Log::error('[FirewallService] Erreur lors de la récupération du firewall: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteFirewall(int $firewallId): bool
    {
        DB::beginTransaction();
        
        try {
            $firewall = $this->firewallRepository->find($firewallId);
            
            if (!$firewall) {
                throw new Exception("Firewall non trouvé: {$firewallId}");
            }
            
            Log::info('[FirewallService] Suppression du firewall', ['firewall_id' => $firewallId]);
            
            // Créer un backup avant suppression
            $this->createPreDeleteBackup($firewall);
            
            // Supprimer le firewall
            $deleted = $this->firewallRepository->delete($firewallId);
            
            if (!$deleted) {
                throw new Exception("Échec de la suppression du firewall");
            }
            
            // Journaliser l'action
            $this->logDeletion($firewall);
            
            DB::commit();
            
            Log::info('[FirewallService] Firewall supprimé avec succès', ['firewall_id' => $firewallId]);
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FirewallService] Erreur lors de la suppression du firewall: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getFirewallStatistics(): array
    {
        try {
            return [
                'total' => $this->firewallRepository->count(),
                'by_brand' => $this->firewallRepository->countByBrand(),
                'by_type' => $this->firewallRepository->countByType(),
                'by_status' => $this->firewallRepository->countByStatus(),
                'by_site' => $this->firewallRepository->countBySite(),
                'ha_enabled' => $this->firewallRepository->countHaEnabled(),
                'needing_backup' => $this->firewallRepository->countNeedingBackup(),
                'average_rules_per_firewall' => $this->firewallRepository->averageRulesCount(),
                'recently_updated' => $this->firewallRepository->getRecentlyUpdated(10),
            ];
        } catch (Exception $e) {
            Log::error('[FirewallService] Erreur lors du calcul des statistiques: ' . $e->getMessage());
            return [];
        }
    }

    public function updateSecurityPolicies(int $firewallId, array $policies)
    {
        DB::beginTransaction();
        
        try {
            $firewall = $this->firewallRepository->find($firewallId);
            
            if (!$firewall) {
                throw new Exception("Firewall non trouvé: {$firewallId}");
            }
            
            Log::info('[FirewallService] Mise à jour des politiques de sécurité', [
                'firewall_id' => $firewallId,
                'policies_count' => count($policies),
            ]);
            
            // Valider les politiques
            $this->validateSecurityPolicies($policies);
            
            // Sauvegarder l'ancien état
            $originalPolicies = $firewall->security_policies ?? [];
            
            // Mettre à jour les politiques
            $updated = $this->firewallRepository->update($firewallId, [
                'security_policies' => $policies,
            ]);
            
            if (!$updated) {
                throw new Exception("Échec de la mise à jour des politiques");
            }
            
            // Créer l'historique spécifique
            $this->historyRepository->create([
                'device_type' => \App\Models\Firewall::class,
                'device_id' => $firewallId,
                'change_type' => 'security_policy_update',
                'configuration' => json_encode($policies),
                'pre_change_config' => json_encode($originalPolicies),
                'post_change_config' => json_encode($policies),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Mise à jour des politiques de sécurité',
            ]);
            
            DB::commit();
            
            Log::info('[FirewallService] Politiques de sécurité mises à jour avec succès', [
                'firewall_id' => $firewallId,
                'policies_count' => count($policies),
            ]);
            
            return $this->firewallRepository->find($firewallId);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FirewallService] Erreur lors de la mise à jour des politiques: ' . $e->getMessage());
            throw $e;
        }
    }

    public function testConnectivity(int $firewallId): array
    {
        try {
            $firewall = $this->firewallRepository->find($firewallId);
            
            if (!$firewall) {
                throw new Exception("Firewall non trouvé: {$firewallId}");
            }
            
            Log::info('[FirewallService] Test de connectivité du firewall', ['firewall_id' => $firewallId]);
            
            $results = [
                'firewall_id' => $firewallId,
                'firewall_name' => $firewall->name,
                'tests' => [],
                'overall_status' => 'unknown',
                'timestamp' => now()->toISOString(),
            ];
            
            // Test de ping sur l'IP NMS
            if ($firewall->ip_nms) {
                $pingResult = $this->testPing($firewall->ip_nms);
                $results['tests']['ping_nms'] = $pingResult;
            }
            
            // Test de ping sur l'IP Service
            if ($firewall->ip_service) {
                $pingResult = $this->testPing($firewall->ip_service);
                $results['tests']['ping_service'] = $pingResult;
            }
            
            // Test des credentials (simulé)
            $authResult = $this->testAuthentication($firewall);
            $results['tests']['authentication'] = $authResult;
            
            // Déterminer le statut global
            $successfulTests = array_filter($results['tests'], fn($test) => $test['status'] === 'success');
            $results['overall_status'] = count($successfulTests) === count($results['tests']) ? 'success' : 'partial';
            
            if (empty($successfulTests)) {
                $results['overall_status'] = 'failed';
            }
            
            // Journaliser les résultats
            $this->logConnectivityTest($firewall, $results);
            
            return $results;
            
        } catch (Exception $e) {
            Log::error('[FirewallService] Erreur lors du test de connectivité: ' . $e->getMessage());
            throw $e;
        }
    }

    private function validateFirewallData(array $data): void
    {
        $requiredFields = ['name', 'brand', 'model', 'site_id'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Le champ '{$field}' est requis");
            }
        }
        
        // Valider l'adresse IP NMS
        if (!empty($data['ip_nms']) && !filter_var($data['ip_nms'], FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("Adresse IP NMS invalide");
        }
        
        // Valider le type de firewall
        $validTypes = ['palo_alto', 'fortinet', 'cisco_asa', 'checkpoint', 'other'];
        if (!empty($data['firewall_type']) && !in_array($data['firewall_type'], $validTypes)) {
            throw new \InvalidArgumentException("Type de firewall invalide");
        }
    }

    private function validateSecurityPolicies(array $policies): void
    {
        foreach ($policies as $index => $policy) {
            if (empty($policy['name'])) {
                throw new \InvalidArgumentException("La politique #{$index} doit avoir un nom");
            }
            
            if (empty($policy['action']) || !in_array($policy['action'], ['allow', 'deny', 'drop'])) {
                throw new \InvalidArgumentException("Action invalide pour la politique '{$policy['name']}'");
            }
        }
    }

    private function sanitizeData(array $data): array
    {
        $sensitiveFields = ['password', 'enable_password', 'psk', 'secret'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***MASKED***';
            }
        }
        
        return $data;
    }

    private function getChangedFields($firewall, array $newData): array
    {
        $changes = [];
        $original = $firewall->toArray();
        
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

    private function calculateBackupStatus($firewall): array
    {
        if (!$firewall->last_backup) {
            return ['status' => 'danger', 'message' => 'Jamais sauvegardé'];
        }
        
        $daysSinceBackup = $firewall->last_backup->diffInDays(now());
        
        if ($daysSinceBackup <= 1) {
            return ['status' => 'success', 'message' => 'Récent (<24h)'];
        } elseif ($daysSinceBackup <= 7) {
            return ['status' => 'warning', 'message' => 'Modéré (<7 jours)'];
        } else {
            return ['status' => 'danger', 'message' => 'Ancien (>7 jours)'];
        }
    }

    private function calculateLicenseStatus($firewall): array
    {
        $licenses = $firewall->licenses ?? [];
        
        if (empty($licenses)) {
            return ['status' => 'warning', 'message' => 'Aucune licence'];
        }
        
        $expired = 0;
        $expiring = 0;
        $valid = 0;
        
        foreach ($licenses as $license) {
            if (isset($license['expiration_date'])) {
                $expiration = \Carbon\Carbon::parse($license['expiration_date']);
                $daysUntilExpiration = now()->diffInDays($expiration, false);
                
                if ($daysUntilExpiration < 0) {
                    $expired++;
                } elseif ($daysUntilExpiration <= 30) {
                    $expiring++;
                } else {
                    $valid++;
                }
            }
        }
        
        if ($expired > 0) {
            return ['status' => 'danger', 'message' => "{$expired} licence(s) expirée(s)"];
        } elseif ($expiring > 0) {
            return ['status' => 'warning', 'message' => "{$expiring} licence(s) expirent bientôt"];
        } else {
            return ['status' => 'success', 'message' => 'Toutes les licences sont valides'];
        }
    }

    private function calculateHaStatus($firewall): array
    {
        if (!$firewall->high_availability) {
            return ['status' => 'secondary', 'message' => 'Non configuré'];
        }

        if ($firewall->haPeer && $firewall->haPeer->status) {
            return ['status' => 'success', 'message' => 'Actif avec pair'];
        } elseif ($firewall->haPeer) {
            return ['status' => 'warning', 'message' => 'Pair inactif'];
        } else {
            return ['status' => 'danger', 'message' => 'Pair non configuré'];
        }
    }

    private function calculateSecurityScore($firewall): int
    {
        $score = 100;
        
        // Décote pour absence de backup
        if (!$firewall->last_backup) {
            $score -= 20;
        } elseif ($firewall->last_backup->diffInDays(now()) > 7) {
            $score -= 10;
        }
        
        // Décote pour licences expirées
        $licenses = $firewall->licenses ?? [];
        foreach ($licenses as $license) {
            if (isset($license['expiration_date']) && 
                \Carbon\Carbon::parse($license['expiration_date'])->isPast()) {
                $score -= 15;
                break;
            }
        }
        
        // Décote pour absence de règles par défaut deny
        $hasDefaultDeny = false;
        $policies = $firewall->security_policies ?? [];
        foreach ($policies as $policy) {
            if (($policy['action'] ?? '') === 'deny' && 
                ($policy['source_address'] ?? '') === 'any' &&
                ($policy['destination_address'] ?? '') === 'any') {
                $hasDefaultDeny = true;
                break;
            }
        }
        
        if (!$hasDefaultDeny) {
            $score -= 25;
        }
        
        // Bonus pour HA actif
        if ($firewall->high_availability && $firewall->haPeer && $firewall->haPeer->status) {
            $score += 10;
        }
        
        return max(0, min(100, $score));
    }

    private function createPreDeleteBackup($firewall): void
    {
        try {
            $this->historyRepository->create([
                'device_type' => \App\Models\Firewall::class,
                'device_id' => $firewall->id,
                'change_type' => 'pre_delete_backup',
                'configuration' => $firewall->configuration ?? null,
                'security_policies' => $firewall->security_policies ?? null,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'notes' => 'Backup avant suppression du firewall',
            ]);
            
            Log::info('[FirewallService] Backup avant suppression créé', ['firewall_id' => $firewall->id]);
        } catch (Exception $e) {
            Log::error('[FirewallService] Erreur lors de la création du backup avant suppression: ' . $e->getMessage());
        }
    }

    private function logDeletion($firewall): void
    {
        try {
            \App\Models\AccessLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'device_type' => \App\Models\Firewall::class,
                'device_id' => $firewall->id,
                'parameters' => [
                    'firewall_name' => $firewall->name,
                    'site' => $firewall->site?->name,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'result' => 'success',
            ]);
        } catch (Exception $e) {
            Log::error('[FirewallService] Erreur lors de la journalisation de la suppression: ' . $e->getMessage());
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

    private function testAuthentication($firewall): array
    {
        return [
            'status' => (!empty($firewall->username) && !empty($firewall->password)) ? 'success' : 'warning',
            'message' => (!empty($firewall->username) && !empty($firewall->password)) ? 
                'Credentials disponibles' : 'Credentials incomplets',
            'has_username' => !empty($firewall->username),
            'has_password' => !empty($firewall->password),
        ];
    }

    private function logConnectivityTest($firewall, array $results): void
    {
        try {
            \App\Models\AccessLog::create([
                'user_id' => auth()->id(),
                'action' => 'connectivity_test',
                'device_type' => \App\Models\Firewall::class,
                'device_id' => $firewall->id,
                'parameters' => [
                    'overall_status' => $results['overall_status'],
                    'tests_performed' => count($results['tests']),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'result' => $results['overall_status'] === 'success' ? 'success' : 'warning',
            ]);
        } catch (Exception $e) {
            Log::error('[FirewallService] Erreur lors de la journalisation du test de connectivité: ' . $e->getMessage());
        }
    }
}