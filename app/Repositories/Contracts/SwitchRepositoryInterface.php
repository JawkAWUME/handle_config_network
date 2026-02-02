<?php
// app/Repositories/Contracts/SwitchRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\SwitchModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SwitchRepositoryInterface
{
    /**
     * Récupérer tous les switches
     */
    public function all(): Collection;

    /**
     * Récupérer les switches paginés
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    /**
     * Trouver un switch par son ID
     */
    public function find(int $id): ?SwitchModel;

    /**
     * Trouver un switch par son ID ou lever une exception
     */
    public function findOrFail(int $id): SwitchModel;

    /**
     * Créer un nouveau switch
     */
    public function create(array $data): SwitchModel;

    /**
     * Mettre à jour un switch
     */
    public function update(int $id, array $data): bool;

    /**
     * Supprimer un switch
     */
    public function delete(int $id): bool;

    /**
     * Rechercher des switches
     */
    public function search(string $query, array $filters = []): LengthAwarePaginator;

    /**
     * Récupérer les switches par site
     */
    public function getBySite(int $siteId): Collection;

    /**
     * Récupérer les switches par statut
     */
    public function getByStatus(string $status): Collection;

    /**
     * Récupérer les switches nécessitant un backup
     */
    public function getNeedingBackup(int $days = 7): Collection;

    /**
     * Récupérer les statistiques des switches
     */
    public function getStatistics(): array;

    /**
     * Créer un backup pour un switch
     */
    public function createBackup(int $switchId, int $userId, ?string $notes = null): bool;

    /**
     * Récupérer l'historique des backups d'un switch
     */
    public function getBackupHistory(int $switchId): Collection;

    /**
     * Récupérer les switches avec des configurations volumineuses
     */
    public function getLargeConfigurations(int $sizeThreshold = 100000): Collection;
}