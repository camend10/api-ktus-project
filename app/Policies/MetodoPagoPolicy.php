<?php

namespace App\Policies;

use App\Models\Configuracion\MetodoPago;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MetodoPagoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {        
        if ($user->can('ver_metodoPago')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MetodoPago $metodoPago): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {        

        if ($user->can('registrar_metodoPago')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        if ($user->can('editar_metodoPago')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        if ($user->can('eliminar_metodoPago')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MetodoPago $metodoPago): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MetodoPago $metodoPago): bool
    {
        return false;
    }
}
