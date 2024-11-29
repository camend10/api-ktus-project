<?php

namespace App\Policies;

use App\Models\Configuracion\SedeDeliverie;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SedeDeliveriePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('ver_sedeDeliverie')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SedeDeliverie $sedeDeliverie): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('registrar_sedeDeliverie')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        if ($user->can('editar_sedeDeliverie')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        if ($user->can('eliminar_sedeDeliverie')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SedeDeliverie $sedeDeliverie): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SedeDeliverie $sedeDeliverie): bool
    {
        return false;
    }
}
