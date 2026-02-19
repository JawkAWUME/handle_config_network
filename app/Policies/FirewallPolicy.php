<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Firewall;

class FirewallPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    public function view(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function update(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    // ✅ nullable + valeur par défaut
    public function delete(User $user, ?Firewall $firewall = null): bool
    {
        return $user->hasRole('admin');
    }

    public function backup(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function restore(User $user, Firewall $firewall): bool
    {
        return $user->hasRole('admin');
    }

    public function export(User $user, ?Firewall $firewall = null): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    public function manageSecurityPolicies(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function manageNatRules(User $user, Firewall $firewall): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function manageVpn(User $user, Firewall $firewall): bool
    {
        return $user->hasRole('admin');
    }

    public function manageLicenses(User $user, Firewall $firewall): bool
    {
        return $user->hasRole('admin');
    }
}