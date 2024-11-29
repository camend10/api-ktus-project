<?php

namespace App\Services\Configuracion;

use App\Models\Configuracion\Iva;
use Spatie\Permission\Models\Role;

class IvaService
{

    public function getByFilter($buscar)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        return Iva::where('porcentaje', 'like', '%' . $buscar . '%')
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

    public function store($request)
    {
        $resp = Iva::create($request);
        return $resp;
    }

    public function update($request, $id)
    {

        $resp = Iva::findOrFail($id);

        $resp->update($request);

        return $resp;
    }

    public function cambiarEstado($request, $id)
    {
        $resp = Iva::findOrFail($id);
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
