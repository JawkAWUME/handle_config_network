<?php
// app/Policies/SitePolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Site;

class SitePolicy
{
    /**
     * Déterminer si l'utilisateur peut voir n'importe quel site
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut voir un site
     */
    public function view(User $user, Site $site): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut créer un site
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un site
     */
    public function update(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un site
     */
    public function delete(User $user, Site $site): bool
    {
        // Vérifier si le site a des équipements
        if ($site->switches()->exists() || $site->routers()->exists() || $site->firewalls()->exists()) {
            return false;
        }
        
        return $user->hasRole('admin');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
    /**
     * Déterminer si l'utilisateur peut exporter les données du site
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut gérer les équipements du site
     */
    public function manageDevices(User $user, Site $site): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }
}