<?php
// app/Policies/FirewallPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Firewall;

class FirewallPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir n'importe quel firewall
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut voir un firewall
     */
    public function view(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut créer un firewall
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un firewall
     */
    public function update(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un firewall
     */
    public function delete(User $user, Firewall $firewall): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut créer un backup
     */
    public function backup(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut restaurer un backup
     */
    public function restore(User $user, Firewall $firewall): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut exporter la configuration
     */
    public function export(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    /**
     * Déterminer si l'utilisateur peut gérer les politiques de sécurité
     */
    public function manageSecurityPolicies(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut gérer les règles NAT
     */
    public function manageNatRules(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * Déterminer si l'utilisateur peut gérer les VPN
     */
    public function manageVpn(User $user, Firewall $firewall): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut gérer les licences
     */
    public function manageLicenses(User $user, Firewall $firewall): bool
    {
        return $user->hasRole('admin');
    }
}