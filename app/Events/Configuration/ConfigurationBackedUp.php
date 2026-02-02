<?php

namespace App\Events;

use App\Models\ConfigurationHistory;
use App\Events\Traits\EventBaseTrait;
use App\Events\Contracts\AuditEventInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConfigurationBackedUp implements AuditEventInterface
{
    use Dispatchable, SerializesModels, EventBaseTrait;

    public ConfigurationHistory $backup;
    public string $deviceType;
    public int $deviceId;
    public string $changeType;
    public array $backupDetails;

    public function __construct(ConfigurationHistory $backup, array $options = [])
    {
        $this->backup = $backup;
        $this->initializeEvent('CONFIGURATION_BACKUP_CREATED', $options);
        
        $this->deviceType = class_basename($backup->device_type);
        $this->deviceId = $backup->device_id;
        $this->changeType = $backup->change_type;
        $this->backupDetails = $this->extractBackupDetails();
    }

    public function getAuditPayload(): array
    {
        return [
            'event' => $this->action,
            'event_uuid' => $this->eventUuid,
            'entity_type' => 'configuration_backup',
            'entity_id' => $this->backup->id,
            'details' => [
                'device_type' => $this->deviceType,
                'device_id' => $this->deviceId,
                'device_name' => $this->backup->device?->name ?? 'Unknown',
                'change_type' => $this->changeType,
                'config_size' => $this->backup->config_size,
                'config_checksum' => $this->backup->config_checksum,
                'user' => $this->backup->user?->name,
                'notes' => $this->backup->notes,
            ],
            'backup_details' => $this->backupDetails,
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
            'type' => 'configuration_backup',
            'id' => $this->backup->id,
            'name' => "Backup for {$this->deviceType} #{$this->deviceId}",
            'changes' => ['created' => true],
        ];
    }

    public function shouldLogToAudit(): bool
    {
        return true; // Toujours loguer les backups
    }

    private function extractBackupDetails(): array
    {
        return [
            'is_corrupted' => $this->backup->isCorrupted(),
            'size_formatted' => $this->backup->config_size_formatted,
            'change_type_formatted' => $this->backup->change_type_formatted,
            'restored_from' => $this->backup->restored_from,
            'has_notes' => !empty($this->backup->notes),
            'ip_address' => $this->backup->ip_address,
        ];
    }
}