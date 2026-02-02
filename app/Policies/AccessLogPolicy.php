<?php
// app/Policies/AccessLogPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\AccessLog;

class AccessLogPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir n'importe quel log d'accès
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut voir un log d'accès
     */
    public function view(User $user, AccessLog $log): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut créer un log d'accès
     */
    public function create(User $user): bool
    {
        return false; // Les logs sont créés automatiquement
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un log d'accès
     */
    public function update(User $user, AccessLog $log): bool
    {
        return false; // Les logs ne doivent pas être modifiés
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un log d'accès
     */
    public function delete(User $user, AccessLog $log): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut voir les rapports de sécurité
     */
    public function viewSecurityReports(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut voir les activités suspectes
     */
    public function viewSuspiciousActivities(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut nettoyer les anciens logs
     */
    public function cleanupLogs(User $user): bool
    {
        return $user->hasRole('admin');
    }
}