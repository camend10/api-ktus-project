<?php

namespace App\Services;

use App\Models\SedeUsuario;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UsuarioService
{
    public function getUsersByFilter($buscar)
    {
        $user = auth('api')->user();
        if ($user && !in_array($user->role_id, [1, 2])) {
            return User::with(['roles', 'sedes', 'tipodocumento', 'empresa'])
                ->where('name', 'like', '%' . $buscar . '%')
                ->where('role_id', '!=', 1)
                ->where('empresa_id', $user->empresa_id)
                ->where('sede_id', $user->sede_id)
                ->orderBy('id', 'desc')
                ->paginate(20);
        } else {
            return User::with(['roles', 'sedes', 'tipodocumento', 'empresa'])
                ->where('name', 'like', '%' . $buscar . '%')
                ->where('role_id', '!=', 1)
                ->where('empresa_id', $user->empresa_id)
                ->orderBy('id', 'desc')
                ->paginate(20);
        }
    }

    public function existeUsers($campo, $tipo)
    {
        if ($tipo == "email") {
            return User::where('email', $campo)
                ->first();
        }

        if ($tipo == "identificacion") {
            return User::where('identificacion', $campo)
                ->first();
        }

        if ($tipo == "usuario") {
            return User::where('usuario', $campo)
                ->first();
        }
    }

    public function existeUsers2($campo, $tipo, $id)
    {
        if ($tipo == "email") {
            return User::where('email', $campo)
                ->where('id', '!=', $id)
                ->first();
        }

        if ($tipo == "identificacion") {
            return User::where('identificacion', $campo)
                ->where('id', '!=', $id)
                ->first();
        }

        if ($tipo == "usuario") {
            return User::where('usuario', $campo)
                ->where('id', '!=', $id)
                ->first();
        }
    }

    public function getUserById($id)
    {
        return User::findOrFail($id);
    }

    public function storeUser($request, $role_id)
    {
        $role = Role::findOrFail($role_id);
        $user = User::create($request);

        if ($user) {
            $user->assignRole($role);

            // if (!empty($request['sedes'])) {
            //     foreach ($request['sedes'] as $sede_id) {
            //         SedeUsuario::create([
            //             'usuario_id' => $user->id,
            //             'sede_id' => $sede_id,
            //             'estado' => 1, // O el estado que corresponda
            //         ]);
            //     }
            // }
            // Sincronizar sedes en la tabla pivote
            if (!empty($request['sedes'])) {
                $user->sedes()->sync($request['sedes']);
            }

            return $user;
        } else {
            return false;
        }
    }


    public function updateUser($request, $role_id, $id)
    {

        $user = User::findOrFail($id);

        if (!$user) {
            return false;
        }

        if ($request["role_id"] != $user->role_id) {
            // EL VIEJO ROL
            $role_old = Role::findOrFail($user->role_id);
            $user->removeRole($role_old);
            //EL NUEVO ROL
            $role = Role::findOrFail($role_id);
            $user->assignRole($role);
        }

        // Actualizar usuario, preservando valores existentes para campos faltantes
        $data = array_merge(
            $user->toArray(), // Cargar todos los datos actuales del usuario
            $request // Sobrescribir solo los campos enviados en el request
        );

        $user->update($data);

        // Sincronizar las sedes seleccionadas
        $user->sedes()->sync($request['sedes']);

        return $user;
    }

    public function cambiarEstadoUser($request, $id)
    {
        $user = User::findOrFail($id);
        if (!$user) {
            return false;
        }

        // Actualizar el estado del usuario
        $user->estado = $request["estado"];
        $user->save();

        // validacion por usuarios
        return $user;
    }
}
