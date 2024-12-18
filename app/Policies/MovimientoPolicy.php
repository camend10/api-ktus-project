<?php

namespace App\Policies;

use App\Models\Movimientos\Movimiento;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class MovimientoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('ver_movimiento')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Movimiento $movimiento): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('registrar_movimiento')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        if ($user->can('editar_movimiento')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        if ($user->can('eliminar_movimiento')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Movimiento $movimiento): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Movimiento $movimiento): bool
    {
        return false;
    }

    public function entrada(User $user): bool
    {
        if ($user->can('entrada_solicitud')) {
            return true;
        }

        return false;
    }

    public function salida(User $user): bool
    {
        if ($user->can('salida_solicitud')) {
            return true;
        }

        return false;
    }
}
