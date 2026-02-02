<?php

namespace App\Events;

use App\Models\Firewall;
use App\Events\Traits\EventBaseTrait;
use App\Events\Contracts\AuditEventInterface;
use App\Events\Contracts\SecurityEventInterface;
use App\Events\Contracts\ConfigurationEventInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FirewallCreated implements AuditEventInterface, SecurityEventInterface, ConfigurationEventInterface
{
    use Dispatchable, SerializesModels, EventBaseTrait;

    public Firewall $firewall;
    public array $initialConfig;
    public bool $isCritical;
    public string $securityLevel;
    public array $complianceData;

    public function __construct(Firewall $firewall, array $options = [])
    {
        $this->firewall = $firewall;
        $this->initializeEvent('FIREWALL_CREATED', $options);
        
        $this->isCritical = $options['isCritical'] ?? true;
        $this->securityLevel = $options['securityLevel'] ?? 'MEDIUM';
        $this->initialConfig = $this->extractInitialConfiguration();
        $this->complianceData = $this->calculateCompliance();
    }

    public function getAuditPayload(): array
    {
        return [
            'event' => $this->action,
            'event_uuid' => $this->eventUuid,
            'entity_type' => 'firewall',
            'entity_id' => $this->firewall->id,
            'entity_name' => $this->firewall->name,
            'details' => [
                'brand' => $this->firewall->brand,
                'model' => $this->firewall->model,
                'type' => $this->firewall->firewall_type,
                'ip_nms' => $this->firewall->ip_nms,
                'ip_service' => $this->firewall->ip_service,
                'version' => $this->firewall->version,
                'serial_number' => $this->firewall->serial_number,
                'ha_enabled' => $this->firewall->high_availability,
                'ha_peer' => $this->firewall->ha_peer_id,
                'site' => $this->firewall->site?->name,
            ],
            'initial_configuration' => [
                'security_policies_count' => count($this->firewall->security_policies ?? []),
                'nat_rules_count' => count($this->firewall->nat_rules ?? []),
                'vpn_configs' => !empty($this->firewall->vpn_configuration),
                'licenses' => $this->firewall->licenses ?? [],
            ],
            'context' => [
                'user_id' => $this->userId,
                'ip_address' => $this->ipAddress,
                'user_agent' => $this->userAgent,
            ],
            'security' => $this->getSecurityContext(),
            'compliance' => $this->complianceData,
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
            'changes' => ['created' => true],
        ];
    }

    public function shouldLogToAudit(): bool
    {
        return true;
    }

    public function getSecurityLevel(): string
    {
        return $this->securityLevel;
    }

    public function isSuspicious(): bool
    {
        // Création hors heures ouvrables ou sans politiques de sécurité
        $hour = $this->timestamp->hour;
        $isOffHours = $hour < 6 || $hour > 20;
        
        return $isOffHours || empty($this->firewall->security_policies);
    }

    public function shouldNotifySecurityTeam(): bool
    {
        return $this->isCritical || $this->isSuspicious();
    }

    public function getSecurityContext(): array
    {
        return [
            'critical' => $this->isCritical,
            'suspicious' => $this->isSuspicious(),
            'has_default_deny' => $this->hasDefaultDenyRule(),
            'risk_score' => $this->calculateRiskScore(),
            'requires_review' => $this->requiresSecurityReview(),
        ];
    }

    public function getConfigurationType(): string
    {
        return 'firewall';
    }

    public function shouldTriggerBackup(): bool
    {
        return true; // Toujours sauvegarder à la création
    }

    public function getBackupMetadata(): array
    {
        return [
            'device_type' => 'firewall',
            'device_id' => $this->firewall->id,
            'change_type' => 'initial',
            'notes' => 'Configuration initiale',
        ];
    }

    private function extractInitialConfiguration(): array
    {
        return [
            'security_policies' => $this->firewall->security_policies ?? [],
            'nat_rules' => $this->firewall->nat_rules ?? [],
            'vpn_configuration' => $this->firewall->vpn_configuration ?? [],
            'licenses' => $this->firewall->licenses ?? [],
        ];
    }

    private function calculateCompliance(): array
    {
        return [
            'has_security_policies' => !empty($this->firewall->security_policies),
            'has_backup_plan' => $this->firewall->last_backup !== null,
            'ha_configured' => $this->firewall->high_availability && $this->firewall->ha_peer_id,
            'monitoring_enabled' => $this->firewall->monitoring_enabled,
            'status' => $this->getComplianceStatus(),
        ];
    }

    private function getComplianceStatus(): string
    {
        $checks = [
            !empty($this->firewall->security_policies),
            $this->firewall->monitoring_enabled,
            $this->firewall->status,
        ];

        $passed = count(array_filter($checks));
        
        return $passed === count($checks) ? 'COMPLIANT' : ($passed >= ceil(count($checks) / 2) ? 'PARTIAL' : 'NON_COMPLIANT');
    }

    private function hasDefaultDenyRule(): bool
    {
        $policies = $this->firewall->security_policies ?? [];
        
        foreach ($policies as $policy) {
            if (($policy['action'] ?? '') === 'deny' && 
                ($policy['source_address'] ?? '') === 'any' &&
                ($policy['destination_address'] ?? '') === 'any') {
                return true;
            }
        }
        
        return false;
    }

    private function calculateRiskScore(): int
    {
        $score = 0;
        
        if (!$this->hasDefaultDenyRule()) $score += 30;
        if (!$this->firewall->monitoring_enabled) $score += 20;
        if (!$this->firewall->high_availability) $score += 15;
        if (empty($this->firewall->security_policies)) $score += 25;
        
        return min(100, $score);
    }

    private function requiresSecurityReview(): bool
    {
        return $this->calculateRiskScore() > 50 || $this->isSuspicious();
    }
}