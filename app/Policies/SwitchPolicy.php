<?php
// app/Policies/SwitchPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\SwitchModel;

class SwitchPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir n'importe quel switch
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut voir un switch
     */
    public function view(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut créer un switch
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un switch
     */
    public function update(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un switch
     */
    public function delete(User $user, SwitchModel $switch): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut créer un backup
     */
    public function backup(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut restaurer un backup
     */
    public function restore(User $user, SwitchModel $switch): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut exporter la configuration
     */
    public function export(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut modifier la configuration
     */
    public function modifyConfiguration(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut tester la connectivité
     */
    public function testConnectivity(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut redémarrer le switch
     */
    public function reboot(User $user, SwitchModel $switch): bool
    {
        return $user->hasRole('admin');
    }
}