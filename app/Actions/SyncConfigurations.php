<?php
// app/Actions/SyncConfigurations.php

namespace App\Actions;

use App\Models\SwitchModel;
use App\Models\Router;
use App\Models\Firewall;
use App\Events\ConfigurationUpdated;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncConfigurations
{
    /**
     * Synchroniser les configurations
     */
    public function execute(array $options = []): array
    {
        $results = [
            'total' => 0,
            'synced' => 0,
            'failed' => 0,
            'details' => []
        ];

        // Synchroniser les switches
        if ($options['sync_switches'] ?? true) {
            $switchResults = $this->syncSwitches();
            $this->mergeResults($results, $switchResults, 'switch');
        }

        // Synchroniser les routeurs
        if ($options['sync_routers'] ?? true) {
            $routerResults = $this->syncRouters();
            $this->mergeResults($results, $routerResults, 'router');
        }

        // Synchroniser les firewalls
        if ($options['sync_firewalls'] ?? true) {
            $firewallResults = $this->syncFirewalls();
            $this->mergeResults($results, $firewallResults, 'firewall');
        }

        // Journaliser le résultat
        Log::info('Synchronisation des configurations terminée', $results);

        return $results;
    }

    /**
     * Synchroniser les switches
     */
    private function syncSwitches(): array
    {
        $results = ['synced' => 0, 'failed' => 0, 'details' => []];
        $switches = SwitchModel::where('status', 'online')->get();
        
        foreach ($switches as $switch) {
            try {
                // Simuler la récupération de la configuration
                $newConfig = $this->fetchConfigurationFromDevice($switch);
                
                if ($newConfig && $newConfig !== $switch->configuration) {
                    // Sauvegarder l'ancienne configuration
                    $oldConfig = $switch->configuration;
                    
                    // Mettre à jour la configuration
                    $switch->update([
                        'configuration' => $newConfig,
                        'last_sync' => Carbon::now()
                    ]);
                    
                    // Déclencher l'événement
                    event(new ConfigurationUpdated($switch, auth()->user(), 'sync'));
                    
                    $results['details'][] = [
                        'device' => $switch->name,
                        'type' => 'switch',
                        'status' => 'synced',
                        'changes' => $oldConfig ? 'updated' : 'initial'
                    ];
                    $results['synced']++;
                } else {
                    $results['details'][] = [
                        'device' => $switch->name,
                        'type' => 'switch',
                        'status' => 'unchanged',
                        'changes' => 'none'
                    ];
                }
            } catch (\Exception $e) {
                $results['details'][] = [
                    'device' => $switch->name,
                    'type' => 'switch',
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
                $results['failed']++;
            }
        }
        
        return $results;
    }

    /**
     * Synchroniser les routeurs
     */
    private function syncRouters(): array
    {
        $results = ['synced' => 0, 'failed' => 0, 'details' => []];
        $routers = Router::where('status', true)->get();
        
        foreach ($routers as $router) {
            try {
                $newConfig = $this->fetchConfigurationFromDevice($router);
                
                if ($newConfig && $newConfig !== $router->configuration) {
                    $oldConfig = $router->configuration;
                    
                    $router->update([
                        'configuration' => $newConfig,
                        'last_sync' => Carbon::now()
                    ]);
                    
                    event(new ConfigurationUpdated($router, auth()->user(), 'sync'));
                    
                    $results['details'][] = [
                        'device' => $router->name,
                        'type' => 'router',
                        'status' => 'synced',
                        'changes' => $oldConfig ? 'updated' : 'initial'
                    ];
                    $results['synced']++;
                } else {
                    $results['details'][] = [
                        'device' => $router->name,
                        'type' => 'router',
                        'status' => 'unchanged'
                    ];
                }
            } catch (\Exception $e) {
                $results['details'][] = [
                    'device' => $router->name,
                    'type' => 'router',
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
                $results['failed']++;
            }
        }
        
        return $results;
    }

    /**
     * Synchroniser les firewalls
     */
    private function syncFirewalls(): array
    {
        $results = ['synced' => 0, 'failed' => 0, 'details' => []];
        $firewalls = Firewall::where('status', true)->get();
        
        foreach ($firewalls as $firewall) {
            try {
                $newConfig = $this->fetchConfigurationFromDevice($firewall);
                
                if ($newConfig && $newConfig !== $firewall->configuration) {
                    $oldConfig = $firewall->configuration;
                    
                    $firewall->update([
                        'configuration' => $newConfig,
                        'last_sync' => Carbon::now()
                    ]);
                    
                    event(new ConfigurationUpdated($firewall, auth()->user(), 'sync'));
                    
                    $results['details'][] = [
                        'device' => $firewall->name,
                        'type' => 'firewall',
                        'status' => 'synced',
                        'changes' => $oldConfig ? 'updated' : 'initial'
                    ];
                    $results['synced']++;
                } else {
                    $results['details'][] = [
                        'device' => $firewall->name,
                        'type' => 'firewall',
                        'status' => 'unchanged'
                    ];
                }
            } catch (\Exception $e) {
                $results['details'][] = [
                    'device' => $firewall->name,
                    'type' => 'firewall',
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
                $results['failed']++;
            }
        }
        
        return $results;
    }

    /**
     * Récupérer la configuration depuis l'appareil
     * Note: À implémenter avec les protocoles réels (SSH, Telnet, API)
     */
    private function fetchConfigurationFromDevice($device): ?string
    {
        // Simulation - À remplacer par la vraie logique
        // Exemple: SSH vers l'appareil pour récupérer "show running-config"
        
        if ($device->status === 'offline' || $device->status === false) {
            throw new \Exception('Appareil hors ligne');
        }
        
        // Simuler un délai réseau
        sleep(1);
        
        // Retourner une configuration simulée
        $config = "! Configuration for {$device->name}\n";
        $config .= "! Last sync: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
        $config .= "hostname {$device->name}\n";
        
        if ($device instanceof SwitchModel) {
            $config .= "spanning-tree mode rapid-pvst\n";
            $config .= "vlan {$device->vlan_nms}\n";
        } elseif ($device instanceof Router) {
            $config .= "interface GigabitEthernet0/0\n";
            $config .= " ip address {$device->management_ip} 255.255.255.0\n";
        } elseif ($device instanceof Firewall) {
            $config .= "security-level 100\n";
            $config .= "access-list OUTSIDE extended permit ip any any\n";
        }
        
        return $config;
    }

    /**
     * Fusionner les résultats
     */
    private function mergeResults(array &$total, array $partial, string $type): void
    {
        $total['total'] += $partial['synced'] + $partial['failed'];
        $total['synced'] += $partial['synced'];
        $total['failed'] += $partial['failed'];
        
        foreach ($partial['details'] as $detail) {
            $total['details'][] = $detail;
        }
    }

    /**
     * Vérifier la connectivité d'un appareil
     */
    public function checkConnectivity($device): array
    {
        try {
            // Tenter de se connecter à l'appareil
            $start = microtime(true);
            $this->fetchConfigurationFromDevice($device);
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'success' => true,
                'device' => $device->name,
                'response_time_ms' => $responseTime,
                'message' => 'Connectivité OK'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'device' => $device->name,
                'error' => $e->getMessage(),
                'message' => 'Échec de connexion'
            ];
        }
    }

    /**
     * Vérifier la connectivité de tous les appareils
     */
    public function checkAllConnectivity(): array
    {
        $results = [
            'total' => 0,
            'online' => 0,
            'offline' => 0,
            'details' => []
        ];
        
        // Vérifier les switches
        $switches = SwitchModel::all();
        foreach ($switches as $switch) {
            $result = $this->checkConnectivity($switch);
            $this->addConnectivityResult($results, $result, 'switch');
        }
        
        // Vérifier les routeurs
        $routers = Router::all();
        foreach ($routers as $router) {
            $result = $this->checkConnectivity($router);
            $this->addConnectivityResult($results, $result, 'router');
        }
        
        // Vérifier les firewalls
        $firewalls = Firewall::all();
        foreach ($firewalls as $firewall) {
            $result = $this->checkConnectivity($firewall);
            $this->addConnectivityResult($results, $result, 'firewall');
        }
        
        return $results;
    }

    /**
     * Ajouter un résultat de connectivité
     */
    private function addConnectivityResult(array &$results, array $result, string $type): void
    {
        $results['total']++;
        
        if ($result['success']) {
            $results['online']++;
        } else {
            $results['offline']++;
        }
        
        $results['details'][] = array_merge($result, ['type' => $type]);
    }
}