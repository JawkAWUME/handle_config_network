<?php

namespace App\Events;

use App\Models\AccessLog;
use App\Events\Traits\EventBaseTrait;
use App\Events\Contracts\AuditEventInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SecurityAccessLogged implements AuditEventInterface
{
    use Dispatchable, SerializesModels, EventBaseTrait;

    public AccessLog $accessLog;
    public bool $isSuspicious;
    public array $securityDetails;

    public function __construct(AccessLog $accessLog, array $options = [])
    {
        $this->accessLog = $accessLog;
        $this->initializeEvent('SECURITY_ACCESS_LOGGED', $options);
        
        $this->isSuspicious = $this->determineIfSuspicious();
        $this->securityDetails = $this->extractSecurityDetails();
    }

    public function getAuditPayload(): array
    {
        return [
            'event' => $this->action,
            'event_uuid' => $this->eventUuid,
            'entity_type' => 'access_log',
            'entity_id' => $this->accessLog->id,
            'details' => [
                'user' => $this->accessLog->user?->name ?? 'Anonymous',
                'action' => $this->accessLog->action,
                'result' => $this->accessLog->result,
                'device_type' => $this->accessLog->device_type,
                'device_id' => $this->accessLog->device_id,
                'device_name' => $this->accessLog->device?->name ?? 'Unknown',
                'ip_address' => $this->accessLog->ip_address,
                'url' => $this->accessLog->url,
                'method' => $this->accessLog->method,
                'response_code' => $this->accessLog->response_code,
                'response_time' => $this->accessLog->response_time,
            ],
            'security_assessment' => [
                'is_suspicious' => $this->isSuspicious,
                'risk_level' => $this->getRiskLevel(),
                'requires_investigation' => $this->requiresInvestigation(),
            ],
            'contextual_data' => [
                'location' => $this->accessLog->city ? "{$this->accessLog->city}, {$this->accessLog->country}" : 'Unknown',
                'browser' => $this->accessLog->browser,
                'platform' => $this->accessLog->platform,
                'referrer' => $this->accessLog->referrer,
                'session_id' => $this->accessLog->session_id,
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
            'type' => 'access_log',
            'id' => $this->accessLog->id,
            'name' => "Access log for {$this->accessLog->action}",
            'changes' => ['created' => true],
        ];
    }

    public function shouldLogToAudit(): bool
    {
        return $this->isSuspicious || $this->accessLog->result === AccessLog::RESULT_DENIED;
    }

    private function determineIfSuspicious(): bool
    {
        return $this->accessLog->isSuspicious();
    }

    private function extractSecurityDetails(): array
    {
        return [
            'failed_login' => $this->accessLog->action === AccessLog::TYPE_LOGIN && 
                             $this->accessLog->result === AccessLog::RESULT_FAILED,
            'denied_access' => $this->accessLog->result === AccessLog::RESULT_DENIED,
            'sensitive_action' => in_array($this->accessLog->action, [
                AccessLog::TYPE_DELETE,
                AccessLog::TYPE_RESTORE,
                AccessLog::TYPE_BACKUP,
            ]),
            'unusual_location' => $this->isUnusualLocation(),
            'multiple_failures' => $this->hasMultipleRecentFailures(),
        ];
    }

    private function getRiskLevel(): string
    {
        if ($this->accessLog->result === AccessLog::RESULT_DENIED) return 'CRITICAL';
        if ($this->isSuspicious) return 'HIGH';
        if ($this->accessLog->result === AccessLog::RESULT_FAILED) return 'MEDIUM';
        return 'LOW';
    }

    private function requiresInvestigation(): bool
    {
        return $this->getRiskLevel() === 'CRITICAL' || 
               ($this->isSuspicious && $this->accessLog->action === AccessLog::TYPE_LOGIN);
    }

    private function isUnusualLocation(): bool
    {
        // Vérifier si l'accès vient d'un pays inhabituel pour l'utilisateur
        // Cette logique nécessiterait un historique des accès de l'utilisateur
        return false;
    }

    private function hasMultipleRecentFailures(): bool
    {
        // Vérifier les échecs récents pour la même IP/Utilisateur
        // Cette logique nécessiterait une requête à la base de données
        return false;
    }
}