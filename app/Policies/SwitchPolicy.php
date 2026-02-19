<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SwitchModel;

class SwitchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    public function view(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function update(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    /**
     * ✅ $switch est nullable : @can('delete', SwitchModel::class) ne passe
     *    pas d'instance → Laravel appelle delete($user) sans 2ème argument.
     *    Sans "= null", PHP lève ArgumentCountError.
     */
    public function delete(User $user, ?SwitchModel $switch = null): bool
    {
        return $user->hasRole('admin');
    }

    public function backup(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function restore(User $user, SwitchModel $switch): bool
    {
        return $user->hasRole('admin');
    }

    public function export(User $user, ?SwitchModel $switch = null): bool
    {
        return $user->hasAnyRole(['admin', 'engineer', 'viewer']);
    }

    public function modifyConfiguration(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function testConnectivity(User $user, SwitchModel $switch): bool
    {
        return $user->hasAnyRole(['admin', 'engineer']);
    }

    public function reboot(User $user, SwitchModel $switch): bool
    {
        return $user->hasRole('admin');
    }
}