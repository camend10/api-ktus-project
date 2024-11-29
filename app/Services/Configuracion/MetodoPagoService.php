<?php

namespace App\Services\Configuracion;

use App\Models\Configuracion\MetodoPago;

class MetodoPagoService
{

    public function getByFilter($buscar)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        return MetodoPago::where('nombre', 'like', '%' . $buscar . '%')
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

    public function store($request)
    {
        // Si metodo_pago_id es 9999999, conviértelo en NULL
        if ($request['metodo_pago_id'] == 9999999) {
            $request['metodo_pago_id'] = null;
        }

        $resp = MetodoPago::create($request);
        return $resp;
    }

    public function update($request, $id)
    {
        // Si metodo_pago_id es 9999999, conviértelo en NULL
        if ($request['metodo_pago_id'] == 9999999) {
            $request['metodo_pago_id'] = null;
        }

        $resp = MetodoPago::findOrFail($id);

        $resp->update($request);

        return $resp;
    }

    public function cambiarEstado($request, $id)
    {
        $resp = MetodoPago::findOrFail($id);
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
