<?php

namespace App\Events\Contracts;

interface ConfigurationEventInterface
{
    public function getConfigurationType(): string;
    public function shouldTriggerBackup(): bool;
    public function getBackupMetadata(): array;
}