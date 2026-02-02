<?php
// app/Policies/UserPolicy.php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir n'importe quel utilisateur
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut voir un utilisateur
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasRole('admin') || $user->id === $model->id;
    }

    /**
     * Déterminer si l'utilisateur peut créer un utilisateur
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un utilisateur
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasRole('admin') || $user->id === $model->id;
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un utilisateur
     */
    public function delete(User $user, User $model): bool
    {
        // Ne pas permettre de se supprimer soi-même
        if ($user->id === $model->id) {
            return false;
        }
        
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut gérer les rôles
     */
    public function manageRoles(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut gérer les permissions
     */
    public function managePermissions(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut voir les logs d'activité
     */
    public function viewActivityLogs(User $user, User $model): bool
    {
        return $user->hasRole('admin') || $user->id === $model->id;
    }

    /**
     * Déterminer si l'utilisateur peut réinitialiser le mot de passe
     */
    public function resetPassword(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }
}