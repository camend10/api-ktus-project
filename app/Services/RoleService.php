<?php

namespace App\Services;

use Spatie\Permission\Models\Role;

class RoleService
{
    public function getRolesByFilter($buscar)
    {
        return Role::with(['permissions'])
            ->where('name', 'like', '%' . $buscar . '%')
            ->where('name','!=','Super-Admin')
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function existeRol($name)
    {
        return Role::where('name', $name)
            ->first();
    }

    public function existeRol2($name, $id)
    {
        return Role::where('name', $name)
            ->where('id', '<>', $id)
            ->first();
    }

    public function storeRoles($name, $permissions)
    {
        $role = Role::create([
            'guard_name' => 'api',
            'name' => $name
        ]);

        foreach ($permissions as $key => $permission) {
            $role->givePermissionTo($permission);
        }

        return $role;
    }

    public function updateRoles($request, $id, $permissions)
    {
        
        $role = Role::findOrFail($id);        
        $role->update($request);

        $role->syncPermissions($permissions);

        return $role;
    }

    public function eliminarRoles($id)
    {
        $role = Role::findOrFail($id);
        // validacion por usuarios
        $role->delete();
        return true;
    }
}
