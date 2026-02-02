<?php
// app/Repositories/Contracts/FirewallRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\Firewall;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface FirewallRepositoryInterface
{
    /**
     * Récupérer tous les firewalls
     */
    public function all(): Collection;

    /**
     * Récupérer les firewalls paginés
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    /**
     * Trouver un firewall par son ID
     */
    public function find(int $id): ?Firewall;

    /**
     * Trouver un firewall par son ID ou lever une exception
     */
    public function findOrFail(int $id): Firewall;

    /**
     * Créer un nouveau firewall
     */
    public function create(array $data): Firewall;

    /**
     * Mettre à jour un firewall
     */
    public function update(int $id, array $data): bool;

    /**
     * Supprimer un firewall
     */
    public function delete(int $id): bool;

    /**
     * Rechercher des firewalls
     */
    public function search(string $query, array $filters = []): LengthAwarePaginator;

    /**
     * Récupérer les firewalls par site
     */
    public function getBySite(int $siteId): Collection;

    /**
     * Récupérer les firewalls par type
     */
    public function getByType(string $type): Collection;

    /**
     * Récupérer les firewalls en paires de haute disponibilité
     */
    public function getInHaPairs(): Collection;

    /**
     * Récupérer les statistiques des firewalls
     */
    public function getStatistics(): array;

    /**
     * Récupérer un rapport sur l'état des licences
     */
    public function getLicenseStatusReport(): array;

    /**
     * Récupérer les politiques de sécurité par site
     */
    public function getSecurityPoliciesBySite(): array;

    /**
     * Analyser les règles NAT
     */
    public function analyzeNatRules(): array;
}