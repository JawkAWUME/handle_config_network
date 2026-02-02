<?php
// app/Repositories/Contracts/AccessLogRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\AccessLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AccessLogRepositoryInterface
{
    /**
     * Récupérer tous les logs d'accès
     */
    public function all(): Collection;

    /**
     * Récupérer les logs d'accès paginés
     */
    public function paginate(int $perPage = 50): LengthAwarePaginator;

    /**
     * Trouver un log d'accès par son ID
     */
    public function find(int $id): ?AccessLog;

    /**
     * Trouver un log d'accès par son ID ou lever une exception
     */
    public function findOrFail(int $id): AccessLog;

    /**
     * Créer un nouveau log d'accès
     */
    public function create(array $data): AccessLog;

    /**
     * Rechercher dans les logs d'accès
     */
    public function search(string $query, array $filters = []): LengthAwarePaginator;

    /**
     * Récupérer les logs d'accès pour un utilisateur spécifique
     */
    public function getForUser(int $userId): Collection;

    /**
     * Récupérer les logs d'accès pour une IP spécifique
     */
    public function getForIp(string $ipAddress): Collection;

    /**
     * Récupérer les logs d'accès pour une période spécifique
     */
    public function getBetweenDates(string $startDate, string $endDate): Collection;

    /**
     * Récupérer les activités suspectes
     */
    public function getSuspiciousActivities(): Collection;

    /**
     * Récupérer les statistiques des logs d'accès
     */
    public function getStatistics(): array;

    /**
     * Récupérer les logs d'accès par action
     */
    public function getByAction(string $action): Collection;

    /**
     * Récupérer les logs d'accès par résultat
     */
    public function getByResult(string $result): Collection;

    /**
     * Récupérer les tentatives de connexion échouées
     */
    public function getFailedLoginAttempts(int $hours = 24): Collection;

    /**
     * Vérifier si une IP est suspecte
     */
    public function isIpSuspicious(string $ipAddress): bool;

    /**
     * Générer un rapport d'activité pour un utilisateur
     */
    public function generateUserActivityReport(int $userId): array;

    /**
     * Générer un rapport de sécurité
     */
    public function generateSecurityReport(): array;

    /**
     * Nettoyer les anciens logs
     */
    public function cleanupOldLogs(int $daysToKeep = 90): int;
}