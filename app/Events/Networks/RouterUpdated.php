<?php

namespace App\Events;

use App\Models\Router;
use App\Events\Traits\EventBaseTrait;
use App\Events\Contracts\AuditEventInterface;
use App\Events\Contracts\ConfigurationEventInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RouterUpdated implements AuditEventInterface, ConfigurationEventInterface
{
    use Dispatchable, SerializesModels, EventBaseTrait;

    public Router $router;
    public array $changes;
    public array $originalData;
    public array $networkChanges;
    public bool $affectsRouting;

    public function __construct(Router $router, array $changes, array $originalData = [], array $options = [])
    {
        $this->router = $router;
        $this->changes = $changes;
        $this->originalData = $originalData;
        $this->initializeEvent('ROUTER_UPDATED', $options);
        
        $this->networkChanges = $this->extractNetworkChanges();
        $this->affectsRouting = $this->determineIfAffectsRouting();
    }

    public function getAuditPayload(): array
    {
        return [
            'event' => $this->action,
            'event_uuid' => $this->eventUuid,
            'entity_type' => 'router',
            'entity_id' => $this->router->id,
            'entity_name' => $this->router->name,
            'changes' => $this->formatChanges(),
            'network_changes' => $this->networkChanges,
            'impact_assessment' => [
                'affects_routing' => $this->affectsRouting,
                'requires_reload' => $this->requiresReload(),
                'downtime_risk' => $this->getDowntimeRisk(),
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
            'type' => 'router',
            'id' => $this->router->id,
            'name' => $this->router->name,
            'changes' => array_keys($this->changes),
        ];
    }

    public function shouldLogToAudit(): bool
    {
        return !empty($this->changes) && $this->hasSignificantChanges();
    }

    public function getConfigurationType(): string
    {
        return 'router';
    }

    public function shouldTriggerBackup(): bool
    {
        return $this->affectsRouting || isset($this->changes['configuration']);
    }

    public function getBackupMetadata(): array
    {
        return [
            'device_type' => 'router',
            'device_id' => $this->router->id,
            'change_type' => $this->affectsRouting ? 'routing_change' : 'general_update',
            'changes_summary' => array_keys($this->changes),
            'notes' => sprintf('Update: %s', implode(', ', array_keys($this->changes))),
        ];
    }

    private function extractNetworkChanges(): array
    {
        $changes = [];
        
        if (isset($this->changes['interfaces'])) {
            $old = $this->originalData['interfaces'] ?? [];
            $new = $this->changes['interfaces'];
            
            if (is_string($old)) $old = json_decode($old, true) ?? [];
            if (is_string($new)) $new = json_decode($new, true) ?? [];
            
            $changes['interfaces'] = [
                'count_before' => count($old),
                'count_after' => count($new),
                'modified_interfaces' => $this->getModifiedInterfaces($old, $new),
            ];
        }
        
        if (isset($this->changes['routing_protocols'])) {
            $changes['routing_protocols'] = 'modified';
        }
        
        if (isset($this->changes['management_ip'])) {
            $changes['management_ip'] = [
                'before' => $this->originalData['management_ip'] ?? null,
                'after' => $this->changes['management_ip'],
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

    private function determineIfAffectsRouting(): bool
    {
        $routingFields = ['interfaces', 'routing_protocols', 'management_ip'];
        return !empty(array_intersect($routingFields, array_keys($this->changes)));
    }

    private function requiresReload(): bool
    {
        return isset($this->changes['operating_system']) || 
               isset($this->changes['configuration']);
    }

    private function getDowntimeRisk(): string
    {
        if ($this->requiresReload()) return 'HIGH';
        if ($this->affectsRouting) return 'MEDIUM';
        return 'LOW';
    }

    private function getModifiedInterfaces(array $old, array $new): array
    {
        $modified = [];
        
        foreach ($old as $index => $oldInterface) {
            $newInterface = $new[$index] ?? null;
            
            if ($newInterface && $oldInterface != $newInterface) {
                $modified[] = [
                    'interface' => $oldInterface['name'] ?? "Interface $index",
                    'changes' => array_diff_assoc($newInterface, $oldInterface),
                ];
            }
        }
        
        return $modified;
    }

    private function hasSignificantChanges(): bool
    {
        $insignificant = ['updated_at', 'notes'];
        $changedFields = array_keys($this->changes);
        
        return !empty(array_diff($changedFields, $insignificant));
    }
}