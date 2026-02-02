<?php
// app/Actions/ValidateConfiguration.php

namespace App\Actions;

use Illuminate\Support\Facades\Log;

class ValidateConfiguration
{
    /**
     * Valider une configuration
     */
    public function execute(string $configuration, string $deviceType = null): array
    {
        $results = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'checks' => []
        ];

        // Vérification de base
        $results['checks'][] = $this->checkNotEmpty($configuration);
        $results['checks'][] = $this->checkSize($configuration);
        $results['checks'][] = $this->checkEncoding($configuration);
        
        // Vérifications spécifiques par type d'appareil
        if ($deviceType) {
            $results['checks'][] = $this->checkDeviceSpecific($configuration, $deviceType);
        }

        // Vérifications de sécurité
        $results['checks'][] = $this->checkSecurity($configuration);
        
        // Analyser les résultats
        foreach ($results['checks'] as $check) {
            if (!$check['passed']) {
                $results['errors'][] = $check['message'];
                $results['valid'] = false;
            } elseif (!empty($check['warning'])) {
                $results['warnings'][] = $check['warning'];
            }
        }

        // Journaliser les erreurs
        if (!empty($results['errors'])) {
            Log::warning('Configuration validation failed', [
                'errors' => $results['errors'],
                'configuration_size' => strlen($configuration)
            ]);
        }

        return $results;
    }

    /**
     * Vérifier que la configuration n'est pas vide
     */
    private function checkNotEmpty(string $configuration): array
    {
        $passed = !empty(trim($configuration));
        
        return [
            'check' => 'not_empty',
            'passed' => $passed,
            'message' => $passed ? null : 'La configuration est vide'
        ];
    }

    /**
     * Vérifier la taille de la configuration
     */
    private function checkSize(string $configuration): array
    {
        $size = strlen($configuration);
        $passed = $size > 0 && $size < 1048576; // 1MB max
        
        $warning = null;
        if ($size > 524288) { // 512KB
            $warning = 'Configuration volumineuse (>512KB)';
        }
        
        return [
            'check' => 'size',
            'passed' => $passed,
            'message' => $passed ? null : 'Configuration trop volumineuse (>1MB)',
            'warning' => $warning,
            'size' => $size
        ];
    }

    /**
     * Vérifier l'encodage
     */
    private function checkEncoding(string $configuration): array
    {
        $isUtf8 = mb_check_encoding($configuration, 'UTF-8');
        
        return [
            'check' => 'encoding',
            'passed' => $isUtf8,
            'message' => $isUtf8 ? null : 'Encodage non UTF-8 détecté'
        ];
    }

    /**
     * Vérifications spécifiques par type d'appareil
     */
    private function checkDeviceSpecific(string $configuration, string $deviceType): array
    {
        $checks = [
            'passed' => true,
            'check' => 'device_specific',
            'message' => null,
            'warnings' => []
        ];

        switch ($deviceType) {
            case 'router':
                $checks = array_merge($checks, $this->checkRouterConfiguration($configuration));
                break;
            case 'firewall':
                $checks = array_merge($checks, $this->checkFirewallConfiguration($configuration));
                break;
            case 'switch':
                $checks = array_merge($checks, $this->checkSwitchConfiguration($configuration));
                break;
        }

        return $checks;
    }

    /**
     * Vérifications pour les routeurs
     */
    private function checkRouterConfiguration(string $configuration): array
    {
        $warnings = [];
        
        // Vérifier la présence de configurations critiques
        if (!str_contains($configuration, 'hostname')) {
            $warnings[] = 'Hostname non configuré';
        }
        
        if (!str_contains($configuration, 'enable secret')) {
            $warnings[] = 'Mot de passe enable non configuré';
        }
        
        return [
            'passed' => true,
            'warnings' => $warnings
        ];
    }

    /**
     * Vérifications pour les firewalls
     */
    private function checkFirewallConfiguration(string $configuration): array
    {
        $warnings = [];
        
        if (!str_contains($configuration, 'security-level')) {
            $warnings[] = 'Niveau de sécurité non configuré';
        }
        
        return [
            'passed' => true,
            'warnings' => $warnings
        ];
    }

    /**
     * Vérifications pour les switches
     */
    private function checkSwitchConfiguration(string $configuration): array
    {
        $warnings = [];
        
        if (!str_contains($configuration, 'spanning-tree')) {
            $warnings[] = 'Spanning-tree non configuré';
        }
        
        return [
            'passed' => true,
            'warnings' => $warnings
        ];
    }

    /**
     * Vérifications de sécurité
     */
    private function checkSecurity(string $configuration): array
    {
        $warnings = [];
        $passed = true;
        
        // Vérifier les mots de passe en clair
        if (preg_match('/password\s+(\S+)/i', $configuration, $matches)) {
            if (!str_starts_with($matches[1] ?? '', '7') && 
                !str_starts_with($matches[1] ?? '', '5') &&
                strlen($matches[1] ?? '') < 20) {
                $warnings[] = 'Mot de passe potentiellement en clair détecté';
            }
        }
        
        // Vérifier les clés faibles
        if (str_contains($configuration, 'crypto key generate rsa modulus 512')) {
            $warnings[] = 'Clé RSA faible (512 bits) détectée';
        }
        
        return [
            'check' => 'security',
            'passed' => $passed,
            'message' => null,
            'warnings' => $warnings
        ];
    }

    /**
     * Analyser les changements de configuration
     */
    public function analyzeChanges(string $oldConfig, string $newConfig): array
    {
        $analysis = [
            'changes' => 0,
            'added_lines' => 0,
            'removed_lines' => 0,
            'modified_lines' => 0,
            'diff' => ''
        ];

        $oldLines = explode("\n", $oldConfig);
        $newLines = explode("\n", $newConfig);
        
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? '';
            $newLine = $newLines[$i] ?? '';
            
            if ($oldLine !== $newLine) {
                $analysis['changes']++;
                $analysis['diff'] .= sprintf("Ligne %d:\n  - %s\n  + %s\n\n", $i + 1, $oldLine, $newLine);
                
                if (empty($oldLine)) {
                    $analysis['added_lines']++;
                } elseif (empty($newLine)) {
                    $analysis['removed_lines']++;
                } else {
                    $analysis['modified_lines']++;
                }
            }
        }

        return $analysis;
    }
}