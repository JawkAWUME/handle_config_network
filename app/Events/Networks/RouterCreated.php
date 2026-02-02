<?php

namespace App\Events;

use App\Models\Router;
use App\Events\Traits\EventBaseTrait;
use App\Events\Contracts\AuditEventInterface;
use App\Events\Contracts\ConfigurationEventInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RouterCreated implements AuditEventInterface, ConfigurationEventInterface
{
    use Dispatchable, SerializesModels, EventBaseTrait;

    public Router $router;
    public array $initialInterfaces;
    public array $routingConfig;

    public function __construct(Router $router, array $options = [])
    {
        $this->router = $router;
        $this->initializeEvent('ROUTER_CREATED', $options);
        
        $this->initialInterfaces = $this->extractInterfaces();
        $this->routingConfig = $this->extractRoutingConfig();
    }

    public function getAuditPayload(): array
    {
        return [
            'event' => $this->action,
            'event_uuid' => $this->eventUuid,
            'entity_type' => 'router',
            'entity_id' => $this->router->id,
            'entity_name' => $this->router->name,
            'details' => [
                'brand' => $this->router->brand,
                'model' => $this->router->model,
                'management_ip' => $this->router->management_ip,
                'operating_system' => $this->router->operating_system,
                'serial_number' => $this->router->serial_number,
                'site' => $this->router->site?->name,
            ],
            'network_configuration' => [
                'interfaces_count' => count($this->initialInterfaces),
                'interfaces' => $this->initialInterfaces,
                'routing_protocols' => $this->routingConfig,
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
            'changes' => ['created' => true],
        ];
    }

    public function shouldLogToAudit(): bool
    {
        return true;
    }

    public function getConfigurationType(): string
    {
        return 'router';
    }

    public function shouldTriggerBackup(): bool
    {
        return true;
    }

    public function getBackupMetadata(): array
    {
        return [
            'device_type' => 'router',
            'device_id' => $this->router->id,
            'change_type' => 'initial',
            'notes' => 'Configuration initiale du routeur',
        ];
    }

    private function extractInterfaces(): array
    {
        $interfaces = $this->router->interfaces ?? [];
        
        if (is_string($interfaces)) {
            $interfaces = json_decode($interfaces, true) ?? [];
        }
        
        return array_map(function($interface) {
            return [
                'name' => $interface['name'] ?? 'unknown',
                'ip_address' => $interface['ip_address'] ?? null,
                'subnet_mask' => $interface['subnet_mask'] ?? null,
                'status' => $interface['status'] ?? 'down',
                'description' => $interface['description'] ?? '',
            ];
        }, $interfaces);
    }

    private function extractRoutingConfig(): array
    {
        $protocols = $this->router->routing_protocols ?? [];
        
        if (is_string($protocols)) {
            $protocols = json_decode($protocols, true) ?? [];
        }
        
        return $protocols;
    }
}