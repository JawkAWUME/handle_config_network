<?php
// app/Services/Contracts/BackupServiceInterface.php

namespace App\Services\Contracts;

interface BackupServiceInterface
{
    /**
     * Exécuter un backup planifié
     */
    public function executeScheduledBackups(): array;

    /**
     * Vérifier les appareils nécessitant un backup
     */
    public function checkDevicesNeedingBackup(int $daysThreshold = 7): array;

    /**
     * Configurer la planification des backups
     */
    public function configureBackupSchedule(array $scheduleConfig): bool;

    /**
     * Tester la connectivité à un appareil
     */
    public function testDeviceConnectivity($device): array;

    /**
     * Synchroniser les configurations avec le système de fichiers
     */
    public function syncWithFilesystem(): array;

    /**
     * Compresser les anciens backups
     */
    public function compressOldBackups(int $daysThreshold = 30): array;

    /**
     * Restaurer une configuration depuis le système de fichiers
     */
    public function restoreFromFilesystem(string $filePath, string $deviceType, int $deviceId): bool;
}