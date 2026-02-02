<?php
// app/Repositories/Contracts/ConfigurationHistoryRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\ConfigurationHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ConfigurationHistoryRepositoryInterface
{
    /**
     * Récupérer tout l'historique
     */
    public function all(): Collection;

    /**
     * Récupérer l'historique paginé
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    /**
     * Trouver une entrée d'historique par son ID
     */
    public function find(int $id): ?ConfigurationHistory;

    /**
     * Trouver une entrée d'historique par son ID ou lever une exception
     */
    public function findOrFail(int $id): ConfigurationHistory;

    /**
     * Créer une nouvelle entrée d'historique
     */
    public function create(array $data): ConfigurationHistory;

    /**
     * Mettre à jour une entrée d'historique
     */
    public function update(int $id, array $data): bool;

    /**
     * Supprimer une entrée d'historique
     */
    public function delete(int $id): bool;

    /**
     * Rechercher dans l'historique
     */
    public function search(string $query, array $filters = []): LengthAwarePaginator;

    /**
     * Récupérer l'historique pour un équipement spécifique
     */
    public function getForDevice(string $deviceType, int $deviceId): Collection;

    /**
     * Récupérer l'historique pour un utilisateur spécifique
     */
    public function getForUser(int $userId): Collection;

    /**
     * Récupérer les backups seulement
     */
    public function getBackups(): Collection;

    /**
     * Récupérer les changements récents
     */
    public function getRecentChanges(int $days = 7): Collection;

    /**
     * Récupérer les statistiques de l'historique
     */
    public function getStatistics(): array;

    /**
     * Récupérer l'historique par type de changement
     */
    public function getByChangeType(string $changeType): Collection;

    /**
     * Récupérer l'historique entre deux dates
     */
    public function getBetweenDates(string $startDate, string $endDate): Collection;

    /**
     * Comparer deux configurations
     */
    public function compareConfigurations(int $historyId1, int $historyId2): array;

    /**
     * Valider l'intégrité d'une configuration sauvegardée
     */
    public function validateConfiguration(int $historyId): array;

    /**
     * Nettoyer l'historique ancien
     */
    public function cleanupOldEntries(int $daysToKeep = 90): int;
}