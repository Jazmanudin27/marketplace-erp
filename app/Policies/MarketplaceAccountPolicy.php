<?php

namespace App\Policies;

use App\Models\MarketplaceAccount;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MarketplaceAccountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MarketplaceAccount $marketplaceAccount): bool
    {
        return $user->company_id === $marketplaceAccount->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketplaceAccount $marketplaceAccount): bool
    {
        return $user->company_id === $marketplaceAccount->company_id
            && ($user->hasRole('admin') || $user->hasRole('manager'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketplaceAccount $marketplaceAccount): bool
    {
        return $user->company_id === $marketplaceAccount->company_id
            && $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MarketplaceAccount $marketplaceAccount): bool
    {
        return $user->company_id === $marketplaceAccount->company_id
            && $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MarketplaceAccount $marketplaceAccount): bool
    {
        return $user->company_id === $marketplaceAccount->company_id
            && $user->hasRole('admin');
    }
}
