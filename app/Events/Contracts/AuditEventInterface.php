<?php

namespace App\Events\Contracts;

interface AuditEventInterface
{
    public function getAuditPayload(): array;
    public function getAffectedEntity(): array;
    public function shouldLogToAudit(): bool;
}