<?php

namespace App\Events;

use App\Models\SwitchModel;
use App\Events\Traits\EventBaseTrait;
use App\Events\Contracts\AuditEventInterface;
use App\Events\Contracts\ConfigurationEventInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SwitchUpdated implements AuditEventInterface, ConfigurationEventInterface
{
    use Dispatchable, SerializesModels, EventBaseTrait;

    public SwitchModel $switch;
    public array $changes;
    public array $originalData;
    public array $networkChanges;
    public bool $affectsConnectivity;

    public function __construct(SwitchModel $switch, array $changes, array $originalData = [], array $options = [])
    {
        $this->switch = $switch;
        $this->changes = $changes;
        $this->originalData = $originalData;
        $this->initializeEvent('SWITCH_UPDATED', $options);
        
        $this->networkChanges = $this->extractNetworkChanges();
        $this->affectsConnectivity = $this->determineIfAffectsConnectivity();
    }

    public function getAuditPayload(): array
    {
        return [
            'event' => $this->action,
            'event_uuid' => $this->eventUuid,
            'entity_type' => 'switch',
            'entity_id' => $this->switch->id,
            'entity_name' => $this->switch->name,
            'changes' => $this->formatChanges(),
            'network_changes' => $this->networkChanges,
            'impact_assessment' => [
                'affects_connectivity' => $this->affectsConnectivity,
                'requires_reboot' => $this->requiresReboot(),
                'affected_vlans' => $this->getAffectedVlans(),
            ],
            'context' => [
                'user_id' => $this->userId,
                'ip_address' => $this->ipAddress,
                'user_agent' => $this->userAgent,
            ],
            'metadata' => $this->metadata,
            'timestamp' => $this->timestamp->toIso8601String(),
        ];
    }

    public function getAffectedEntity(): array
    {
        return [
            'type' => 'switch',
            'id' => $this->switch->id,
            'name' => $this->switch->name,
            'changes' => array_keys($this->changes),
        ];
    }

    public function shouldLogToAudit(): bool
    {
        return !empty($this->changes) && $this->hasSignificantChanges();
    }

    public function getConfigurationType(): string
    {
        return 'switch';
    }

    public function shouldTriggerBackup(): bool
    {
        return $this->affectsConnectivity || isset($this->changes['configuration']);
    }

    public function getBackupMetadata(): array
    {
        return [
            'device_type' => 'switch',
            'device_id' => $this->switch->id,
            'change_type' => $this->affectsConnectivity ? 'network_change' : 'general_update',
            'changes_summary' => array_keys($this->changes),
            'notes' => sprintf('Update: %s', implode(', ', array_keys($this->changes))),
        ];
    }

    private function extractNetworkChanges(): array
    {
        $changes = [];
        
        $networkFields = ['ip_nms', 'ip_service', 'vlan_nms', 'vlan_service'];
        
        foreach ($networkFields as $field) {
            if (isset($this->changes[$field])) {
                $changes[$field] = [
                    'before' => $this->originalData[$field] ?? null,
                    'after' => $this->changes[$field],
                ];
            }
        }
        
        if (isset($this->changes['configuration'])) {
            $changes['configuration'] = [
                'size_before' => strlen($this->originalData['configuration'] ?? ''),
                'size_after' => strlen($this->changes['configuration']),
                'modified' => true,
            ];
        }
        
        return $changes;
    }

    private function formatChanges(): array
    {
        $formatted = [];
        
        foreach ($this->changes as $field => $value) {
            $oldValue = $this->originalData[$field] ?? null;
            
            if ($field === 'password' || $field === 'configuration') {
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

    private function determineIfAffectsConnectivity(): bool
    {
        $connectivityFields = ['ip_nms', 'ip_service', 'vlan_nms', 'vlan_service', 'configuration', 'status'];
        return !empty(array_intersect($connectivityFields, array_keys($this->changes)));
    }

    private function requiresReboot(): bool
    {
        return isset($this->changes['configuration']) && $this->hasMajorConfigChanges();
    }

    private function getAffectedVlans(): array
    {
        $affected = [];
        
        if (isset($this->changes['vlan_nms'])) {
            $affected[] = 'NMS VLAN';
        }
        
        if (isset($this->changes['vlan_service'])) {
            $affected[] = 'Service VLAN';
        }
        
        return $affected;
    }

    private function hasMajorConfigChanges(): bool
    {
        if (!isset($this->changes['configuration'])) {
            return false;
        }
        
        $oldConfig = $this->originalData['configuration'] ?? '';
        $newConfig = $this->changes['configuration'];
        
        // Si la taille change de plus de 10%, considérer comme majeur
        $sizeDiff = abs(strlen($newConfig) - strlen($oldConfig));
        $maxSize = max(strlen($newConfig), strlen($oldConfig), 1);
        
        return ($sizeDiff / $maxSize) > 0.1;
    }

    private function hasSignificantChanges(): bool
    {
        $insignificant = ['updated_at', 'notes'];
        $changedFields = array_keys($this->changes);
        
        return !empty(array_diff($changedFields, $insignificant));
    }
}