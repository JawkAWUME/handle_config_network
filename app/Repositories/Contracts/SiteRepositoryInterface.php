<?php
// app/Repositories/Contracts/SiteRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SiteRepositoryInterface
{
    /**
     * Récupérer tous les sites
     */
    public function all(): Collection;

    /**
     * Récupérer les sites paginés
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    /**
     * Trouver un site par son ID
     */
    public function find(int $id): ?Site;

    /**
     * Trouver un site par son ID ou lever une exception
     */
    public function findOrFail(int $id): Site;

    /**
     * Créer un nouveau site
     */
    public function create(array $data): Site;

    /**
     * Mettre à jour un site
     */
    public function update(int $id, array $data): bool;

    /**
     * Supprimer un site
     */
    public function delete(int $id): bool;

    /**
     * Rechercher des sites
     */
    public function search(string $query, array $filters = []): LengthAwarePaginator;

    /**
     * Récupérer les sites avec les équipements associés
     */
    public function withDevices(int $siteId): ?Site;

    /**
     * Récupérer les statistiques des sites
     */
    public function getStatistics(): array;

    /**
     * Récupérer les sites actifs
     */
    public function getActiveSites(): Collection;

    /**
     * Récupérer le nombre total d'équipements par site
     */
    public function getDeviceCountsBySite(): Collection;

    public function getSitesNeedingAttention(): Collection;
}