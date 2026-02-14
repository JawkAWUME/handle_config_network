<?php
// app/Policies/RouterPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Router;

class RouterPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir n'importe quel routeur
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut voir un routeur
     */
    public function view(User $user, Router $router): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut créer un routeur
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un routeur
     */
    public function update(User $user, Router $router): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un routeur
     */
    public function delete(User $user, Router $router = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut créer un backup
     */
    public function backup(User $user, Router $router): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut restaurer un backup
     */
    public function restore(User $user, Router $router): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut exporter la configuration
     */
    public function export(User $user, Router $router): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut gérer le routage
     */
    public function manageRouting(User $user, Router $router): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut gérer la haute disponibilité
     */
    public function manageHa(User $user, Router $router): bool
    {
        return $user->hasRole('admin');
    }
}