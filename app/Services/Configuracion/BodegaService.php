<?php

namespace App\Services\Configuracion;

use App\Models\Configuracion\Bodega;
use Spatie\Permission\Models\Role;

class BodegaService
{

    public function getByFilter($buscar)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        if ($user->role_id == 1 || $user->role_id == 2) {
            return Bodega::where('nombre', 'like', '%' . $buscar . '%')
                ->where('empresa_id', $user->empresa_id)
                ->orderBy('id', 'desc')
                ->paginate(20);
        } else {
            return Bodega::where('nombre', 'like', '%' . $buscar . '%')
                ->where('empresa_id', $user->empresa_id)
                ->where('sede_id', $user->sede_id)
                ->orderBy('id', 'desc')
                ->paginate(20);
        }
    }

    public function store($request)
    {
        $resp = Bodega::create($request);
        return $resp;
    }

    public function update($request, $id)
    {

        $resp = Bodega::findOrFail($id);

        $resp->update($request);

        return $resp;
    }

    public function cambiarEstado($request, $id)
    {
        $resp = Bodega::findOrFail($id);
        if (!$resp) {
            return false;
        }

        // Actualizar el estado del usuario
        $resp->estado = $request["estado"];
        $resp->save();

        // validacion por usuarios
        return $resp;
    }
}
