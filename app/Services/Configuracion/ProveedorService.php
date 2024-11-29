<?php

namespace App\Services\Configuracion;

use App\Models\Configuracion\Proveedor;

class ProveedorService
{

    public function getByFilter($buscar)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        return Proveedor::where('nombres', 'like', '%' . $buscar . '%')
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

    public function store($request)
    {
        $resp = Proveedor::create($request);
        return $resp;
    }

    public function update($request, $id)
    {

        $resp = Proveedor::findOrFail($id);

        $resp->update($request);

        return $resp;
    }

    public function cambiarEstado($request, $id)
    {
        $resp = Proveedor::findOrFail($id);
        if (!$resp) {
            return false;
        }

        // Actualizar el estado del usuario
        $resp->estado = $request["estado"];
        $resp->save();

        // validacion por usuarios
        return $resp;
    }

    
    public function getById($id)
    {
        return Proveedor::findOrFail($id);
    }
}
