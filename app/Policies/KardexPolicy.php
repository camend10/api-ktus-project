<?php

namespace App\Policies;

use App\Models\Kardex\ArticuloStockInicial;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KardexPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('ver_kardex')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ArticuloStockInicial $articuloStockInicial): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ArticuloStockInicial $articuloStockInicial): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ArticuloStockInicial $articuloStockInicial): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ArticuloStockInicial $articuloStockInicial): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ArticuloStockInicial $articuloStockInicial): bool
    {
        return false;
    }
}
