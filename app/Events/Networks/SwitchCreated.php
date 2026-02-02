<?php

namespace App\Events;

use App\Models\SwitchModel;
use App\Events\Traits\EventBaseTrait;
use App\Events\Contracts\AuditEventInterface;
use App\Events\Contracts\ConfigurationEventInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SwitchCreated implements AuditEventInterface, ConfigurationEventInterface
{
    use Dispatchable, SerializesModels, EventBaseTrait;

    public SwitchModel $switch;
    public array $networkConfig;

    public function __construct(SwitchModel $switch, array $options = [])
    {
        $this->switch = $switch;
        $this->initializeEvent('SWITCH_CREATED', $options);
        $this->networkConfig = $this->extractNetworkConfig();
    }

    public function getAuditPayload(): array
    {
        return [
            'event' => $this->action,
            'event_uuid' => $this->eventUuid,
            'entity_type' => 'switch',
            'entity_id' => $this->switch->id,
            'entity_name' => $this->switch->name,
            'details' => [
                'site' => $this->switch->site?->name,
                'ip_nms' => $this->switch->ip_nms,
                'ip_service' => $this->switch->ip_service,
                'vlan_nms' => $this->switch->vlan_nms,
                'vlan_service' => $this->switch->vlan_service,
            ],
            'network_configuration' => $this->networkConfig,
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
            'changes' => ['created' => true],
        ];
    }

    public function shouldLogToAudit(): bool
    {
        return true;
    }

    public function getConfigurationType(): string
    {
        return 'switch';
    }

    public function shouldTriggerBackup(): bool
    {
        return true;
    }

    public function getBackupMetadata(): array
    {
        return [
            'device_type' => 'switch',
            'device_id' => $this->switch->id,
            'change_type' => 'initial',
            'notes' => 'Configuration initiale du switch',
        ];
    }

    private function extractNetworkConfig(): array
    {
        // Analyse de la configuration pour extraire les VLANs, ports, etc.
        $config = $this->switch->configuration ?? '';
        
        return [
            'has_configuration' => !empty($config),
            'config_size' => strlen($config),
            'vlan_nms' => $this->switch->vlan_nms,
            'vlan_service' => $this->switch->vlan_service,
            'management_ips' => [
                'nms' => $this->switch->ip_nms,
                'service' => $this->switch->ip_service,
            ],
        ];
    }
}