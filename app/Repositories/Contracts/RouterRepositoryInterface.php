<?php
// app/Repositories/Contracts/RouterRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\Router;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RouterRepositoryInterface
{
    /**
     * Récupérer tous les routeurs
     */
    public function all(): Collection;

    /**
     * Récupérer les routeurs paginés
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    /**
     * Trouver un routeur par son ID
     */
    public function find(int $id): ?Router;

    /**
     * Trouver un routeur par son ID ou lever une exception
     */
    public function findOrFail(int $id): Router;

    /**
     * Créer un nouveau routeur
     */
    public function create(array $data): Router;

    /**
     * Mettre à jour un routeur
     */
    public function update(int $id, array $data): bool;

    /**
     * Supprimer un routeur
     */
    public function delete(int $id): bool;

    /**
     * Rechercher des routeurs
     */
    public function search(string $query, array $filters = []): LengthAwarePaginator;

    /**
     * Récupérer les routeurs par site
     */
    public function getBySite(int $siteId): Collection;

    /**
     * Récupérer les routeurs par marque
     */
    public function getByBrand(string $brand): Collection;

    /**
     * Récupérer les routeurs nécessitant un backup
     */
    public function getNeedingBackup(int $days = 7): Collection;

    /**
     * Récupérer les statistiques des routeurs
     */
    public function getStatistics(): array;

    /**
     * Créer un backup pour un routeur
     */
    public function createBackup(int $routerId, int $userId, ?string $notes = null): bool;

    /**
     * Récupérer l'historique des backups d'un routeur
     */
    public function getBackupHistory(int $routerId): Collection;

    /**
     * Récupérer les routeurs avec des configurations volumineuses
     */
    public function getLargeConfigurations(int $sizeThreshold = 100000): Collection;

    /**
     * Récupérer les routeurs par protocole de routage
     */
    public function getByRoutingProtocol(string $protocol): Collection;
}