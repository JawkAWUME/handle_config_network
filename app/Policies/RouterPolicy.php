<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Router;

class RouterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    public function view(User $user, Router $router): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function update(User $user, Router $router): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    // ✅ nullable + valeur par défaut
    public function delete(User $user, ?Router $router = null): bool
    {
        return $user->hasRole('admin');
    }

    public function backup(User $user, Router $router): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function restore(User $user, Router $router): bool
    {
        return $user->hasRole('admin');
    }

    public function export(User $user, ?Router $router = null): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    public function manageRouting(User $user, Router $router): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function manageHa(User $user, Router $router): bool
    {
        return $user->hasRole('admin');
    }
}