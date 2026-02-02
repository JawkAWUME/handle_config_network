<?php

namespace App\Events;

use App\Models\Firewall;
use App\Events\Traits\EventBaseTrait;
use App\Events\Contracts\AuditEventInterface;
use App\Events\Contracts\SecurityEventInterface;
use App\Events\Contracts\ConfigurationEventInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FirewallUpdated implements AuditEventInterface, SecurityEventInterface, ConfigurationEventInterface
{
    use Dispatchable, SerializesModels, EventBaseTrait;

    public Firewall $firewall;
    public array $changes;
    public array $originalData;
    public array $configurationChanges;
    public string $updateCategory;
    public bool $requiresVerification;

    public function __construct(Firewall $firewall, array $changes, array $originalData = [], array $options = [])
    {
        $this->firewall = $firewall;
        $this->changes = $changes;
        $this->originalData = $originalData;
        $this->initializeEvent('FIREWALL_UPDATED', $options);
        
        $this->configurationChanges = $this->extractConfigurationChanges();
        $this->updateCategory = $this->determineUpdateCategory();
        $this->requiresVerification = $this->determineIfVerificationRequired();
    }

    public function getAuditPayload(): array
    {
        return [
            'event' => $this->action,
            'event_uuid' => $this->eventUuid,
            'entity_type' => 'firewall',
            'entity_id' => $this->firewall->id,
            'entity_name' => $this->firewall->name,
            'update_category' => $this->updateCategory,
            'changes' => $this->formatChanges(),
            'configuration_changes' => $this->configurationChanges,
            'security_impact' => $this->calculateSecurityImpact(),
            'context' => [
                'user_id' => $this->userId,
                'ip_address' => $this->ipAddress,
                'user_agent' => $this->userAgent,
                'requires_verification' => $this->requiresVerification,
                'verification_reasons' => $this->getVerificationReasons(),
            ],
            'metadata' => $this->metadata,
            'timestamp' => $this->timestamp->toIso8601String(),
        ];
    }

    public function getAffectedEntity(): array
    {
        return [
            'type' => 'firewall',
            'id' => $this->firewall->id,
            'name' => $this->firewall->name,
            'changes' => array_keys($this->changes),
        ];
    }

    public function shouldLogToAudit(): bool
    {
        return !empty($this->changes) && $this->hasSignificantChanges();
    }

    public function getSecurityLevel(): string
    {
        return $this->hasSecurityChanges() ? 'HIGH' : 'MEDIUM';
    }

    public function isSuspicious(): bool
    {
        return $this->hasSecurityPolicyChanges() && $this->isOffHoursUpdate();
    }

    public function shouldNotifySecurityTeam(): bool
    {
        return $this->hasSecurityChanges() || $this->hasHaConfigurationChanges();
    }

    public function getSecurityContext(): array
    {
        return [
            'has_security_changes' => $this->hasSecurityChanges(),
            'has_policy_changes' => $this->hasSecurityPolicyChanges(),
            'risk_level' => $this->calculateRiskLevel(),
            'requires_immediate_review' => $this->requiresImmediateReview(),
        ];
    }

    public function getConfigurationType(): string
    {
        return 'firewall';
    }

    public function shouldTriggerBackup(): bool
    {
        return $this->hasSecurityChanges() || $this->hasConfigurationChanges();
    }

    public function getBackupMetadata(): array
    {
        return [
            'device_type' => 'firewall',
            'device_id' => $this->firewall->id,
            'change_type' => $this->updateCategory,
            'changes_summary' => array_keys($this->changes),
            'notes' => sprintf('Update: %s', $this->updateCategory),
        ];
    }

    private function extractConfigurationChanges(): array
    {
        $changes = [];
        
        if (isset($this->changes['security_policies'])) {
            $old = json_decode($this->originalData['security_policies'] ?? '[]', true) ?? [];
            $new = json_decode($this->changes['security_policies'], true) ?? [];
            
            $changes['security_policies'] = [
                'count_before' => count($old),
                'count_after' => count($new),
                'added' => array_diff_key($new, $old),
                'removed' => array_diff_key($old, $new),
            ];
        }
        
        if (isset($this->changes['nat_rules'])) {
            $old = json_decode($this->originalData['nat_rules'] ?? '[]', true) ?? [];
            $new = json_decode($this->changes['nat_rules'], true) ?? [];
            
            $changes['nat_rules'] = [
                'count_before' => count($old),
                'count_after' => count($new),
                'added' => array_diff_key($new, $old),
                'removed' => array_diff_key($old, $new),
            ];
        }
        
        if (isset($this->changes['vpn_configuration'])) {
            $changes['vpn_configuration'] = 'modified';
        }
        
        return $changes;
    }

    private function determineUpdateCategory(): string
    {
        if ($this->hasSecurityPolicyChanges()) return 'SECURITY_POLICY_CHANGE';
        if (isset($this->changes['nat_rules'])) return 'NAT_RULE_CHANGE';
        if (isset($this->changes['vpn_configuration'])) return 'VPN_CONFIG_CHANGE';
        if (isset($this->changes['high_availability'])) return 'HA_CONFIG_CHANGE';
        if (isset($this->changes['status'])) return 'STATUS_CHANGE';
        return 'GENERAL_UPDATE';
    }

    private function determineIfVerificationRequired(): bool
    {
        return $this->hasSecurityChanges() || 
               $this->hasHaConfigurationChanges() ||
               isset($this->changes['status']);
    }

    private function formatChanges(): array
    {
        $formatted = [];
        
        foreach ($this->changes as $field => $value) {
            $oldValue = $this->originalData[$field] ?? null;
            
            // Masquer les champs sensibles
            if (in_array($field, ['password', 'enable_password', 'configuration'])) {
                $formatted[$field] = [
                    'before' => $oldValue ? '***' : null,
                    'after' => '***',
                    'type' => 'sensitive',
                ];
            } else {
                $formatted[$field] = [
                    'before' => $oldValue,
                    'after' => $value,
                    'type' => gettype($value),
                ];
            }
        }
        
        return $formatted;
    }

    private function calculateSecurityImpact(): array
    {
        $impact = 'LOW';
        $score = 0;
        
        if ($this->hasSecurityPolicyChanges()) {
            $score += 40;
            $impact = 'HIGH';
        }
        
        if (isset($this->changes['vpn_configuration'])) {
            $score += 30;
            if ($impact === 'LOW') $impact = 'MEDIUM';
        }
        
        if (isset($this->changes['high_availability'])) {
            $score += 20;
        }
        
        if (isset($this->changes['status']) && !$this->changes['status']) {
            $score += 50;
            $impact = 'CRITICAL';
        }
        
        return [
            'score' => min(100, $score),
            'level' => $impact,
            'description' => $this->getImpactDescription($impact),
        ];
    }

    private function getImpactDescription(string $impact): string
    {
        return match($impact) {
            'CRITICAL' => 'Impact critique sur la sécurité du réseau',
            'HIGH' => 'Impact élevé sur les politiques de sécurité',
            'MEDIUM' => 'Impact modéré sur la configuration',
            'LOW' => 'Impact minimal, changement opérationnel',
            default => 'Pas d\'impact détecté',
        };
    }

    private function getVerificationReasons(): array
    {
        $reasons = [];
        
        if ($this->hasSecurityPolicyChanges()) {
            $reasons[] = 'Modification des politiques de sécurité';
        }
        
        if ($this->hasHaConfigurationChanges()) {
            $reasons[] = 'Modification de la configuration HA';
        }
        
        if (isset($this->changes['status'])) {
            $reasons[] = 'Changement d\'état du firewall';
        }
        
        return $reasons;
    }

    private function hasSignificantChanges(): bool
    {
        $insignificant = ['updated_at', 'last_backup', 'notes'];
        $changedFields = array_keys($this->changes);
        
        return !empty(array_diff($changedFields, $insignificant));
    }

    private function hasSecurityChanges(): bool
    {
        $securityFields = ['security_policies', 'nat_rules', 'vpn_configuration', 'status'];
        return !empty(array_intersect($securityFields, array_keys($this->changes)));
    }

    private function hasSecurityPolicyChanges(): bool
    {
        return isset($this->changes['security_policies']);
    }

    private function hasHaConfigurationChanges(): bool
    {
        return isset($this->changes['high_availability']) || isset($this->changes['ha_peer_id']);
    }

    private function hasConfigurationChanges(): bool
    {
        return isset($this->changes['configuration']) || isset($this->changes['configuration_file']);
    }

    private function calculateRiskLevel(): string
    {
        if ($this->hasSecurityPolicyChanges()) return 'HIGH';
        if (isset($this->changes['vpn_configuration'])) return 'MEDIUM';
        if (isset($this->changes['high_availability'])) return 'MEDIUM';
        return 'LOW';
    }

    private function requiresImmediateReview(): bool
    {
        return $this->hasSecurityPolicyChanges() || 
               (isset($this->changes['status']) && !$this->changes['status']);
    }

    private function isOffHoursUpdate(): bool
    {
        $hour = $this->timestamp->hour;
        return $hour < 6 || $hour > 20;
    }
}