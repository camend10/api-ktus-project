<?php

namespace App\Policies;

use App\Models\Articulos\Articulo;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class ArticuloPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // if ($user->can('ver_articulos')) {
        //     return true;
        // }

        // return false;

        // Log::info('Checking permission', [
        //     'user_id' => $user->id,
        //     'permissions' => $user->getAllPermissions()->pluck('name'),
        // ]);
        return $user->can('ver_articulos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Articulo $articulo): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('registrar_articulo')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        if ($user->can('editar_articulo')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        if ($user->can('eliminar_articulo')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Articulo $articulo): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Articulo $articulo): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function baja_existencia(User $user): bool
    {
        if ($user->can('baja_existencia')) {
            return true;
        }

        return false;
    }
    /**
     * Determine whether the user can delete the model.
     */
    public function ventas(User $user): bool
    {
        if ($user->can('ventas')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function vendidos(User $user): bool
    {
        if ($user->can('vendidos')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function movimientos(User $user): bool
    {
        if ($user->can('movimientos')) {
            return true;
        }

        return false;
    }
}
