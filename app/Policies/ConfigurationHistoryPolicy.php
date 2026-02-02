<?php
// app/Policies/ConfigurationHistoryPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\ConfigurationHistory;

class ConfigurationHistoryPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir n'importe quel historique
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut voir un historique
     */
    public function view(User $user, ConfigurationHistory $history): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut créer un historique
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un historique
     */
    public function update(User $user, ConfigurationHistory $history): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un historique
     */
    public function delete(User $user, ConfigurationHistory $history): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut restaurer depuis un historique
     */
    public function restoreFrom(User $user, ConfigurationHistory $history): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut comparer des historiques
     */
    public function compare(User $user, ConfigurationHistory $history): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut télécharger la configuration
     */
    public function download(User $user, ConfigurationHistory $history): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut valider l'intégrité
     */
    public function validate(User $user, ConfigurationHistory $history): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }
}