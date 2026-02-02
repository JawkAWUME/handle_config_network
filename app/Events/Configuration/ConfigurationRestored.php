<?php

namespace App\Events;

use App\Models\ConfigurationHistory;
use App\Events\Traits\EventBaseTrait;
use App\Events\Contracts\AuditEventInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConfigurationRestored implements AuditEventInterface
{
    use Dispatchable, SerializesModels, EventBaseTrait;

    public ConfigurationHistory $restoredBackup;
    public ConfigurationHistory $newBackup;
    public string $deviceType;
    public int $deviceId;
    public array $restoreDetails;

    public function __construct(ConfigurationHistory $restoredBackup, ConfigurationHistory $newBackup, array $options = [])
    {
        $this->restoredBackup = $restoredBackup;
        $this->newBackup = $newBackup;
        $this->initializeEvent('CONFIGURATION_RESTORED', $options);
        
        $this->deviceType = class_basename($restoredBackup->device_type);
        $this->deviceId = $restoredBackup->device_id;
        $this->restoreDetails = $this->extractRestoreDetails();
    }

    public function getAuditPayload(): array
    {
        return [
            'event' => $this->action,
            'event_uuid' => $this->eventUuid,
            'entity_type' => 'configuration_restore',
            'restored_backup_id' => $this->restoredBackup->id,
            'new_backup_id' => $this->newBackup->id,
            'details' => [
                'device_type' => $this->deviceType,
                'device_id' => $this->deviceId,
                'device_name' => $this->restoredBackup->device?->name ?? 'Unknown',
                'restored_from_backup' => $this->restoredBackup->created_at->format('Y-m-d H:i:s'),
                'backup_before_restore_created' => $this->newBackup->created_at->format('Y-m-d H:i:s'),
                'user' => $this->restoredBackup->user?->name,
                'notes' => $this->newBackup->notes,
            ],
            'restore_details' => $this->restoreDetails,
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
            'type' => 'configuration_restore',
            'id' => $this->newBackup->id,
            'name' => "Restore for {$this->deviceType} #{$this->deviceId}",
            'changes' => ['restored' => true],
        ];
    }

    public function shouldLogToAudit(): bool
    {
        return true; // Toujours loguer les restaurations
    }

    private function extractRestoreDetails(): array
    {
        return [
            'time_difference' => $this->restoredBackup->created_at->diffForHumans(),
            'config_size_comparison' => [
                'restored_size' => $this->restoredBackup->config_size,
                'current_size' => $this->newBackup->config_size,
                'difference' => $this->newBackup->config_size - $this->restoredBackup->config_size,
            ],
            'integrity_check' => [
                'restored_backup_valid' => !$this->restoredBackup->isCorrupted(),
                'new_backup_valid' => !$this->newBackup->isCorrupted(),
            ],
            'is_rollback' => $this->isRollback(),
        ];
    }

    private function isRollback(): bool
    {
        return $this->restoredBackup->created_at->greaterThan(
            $this->restoredBackup->device?->updated_at ?? now()
        );
    }
}