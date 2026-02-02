<?php

namespace App\Events\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

trait EventBaseTrait
{
    public string $eventUuid;
    public Carbon $timestamp;
    public string $action;
    public ?int $userId;
    public string $ipAddress;
    public string $userAgent;
    public array $metadata = [];
    
    protected function initializeEvent(string $action, array $options = []): void
    {
        $this->eventUuid = (string) Str::uuid();
        $this->timestamp = now();
        $this->action = $action;
        $this->userId = $options['userId'] ?? auth()->id();
        $this->ipAddress = $options['ipAddress'] ?? request()->ip() ?? '0.0.0.0';
        $this->userAgent = $options['userAgent'] ?? request()->userAgent() ?? 'CLI';
        $this->metadata = $options['metadata'] ?? [];
    }
    
    public function getEventSignature(): string
    {
        return sprintf('%s:%s', $this->action, $this->eventUuid);
    }
}