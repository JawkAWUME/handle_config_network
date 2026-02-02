<?php

namespace App\Events\Contracts;

interface SecurityEventInterface
{
    public function getSecurityLevel(): string;
    public function isSuspicious(): bool;
    public function shouldNotifySecurityTeam(): bool;
    public function getSecurityContext(): array;
}