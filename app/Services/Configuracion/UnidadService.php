<?php

namespace App\Services\Configuracion;

use App\Models\Configuracion\Unidad;
use App\Models\Configuracion\UnidadTransformacion;

class UnidadService
{

    public function getByFilter($buscar)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        return Unidad::where('nombre', 'like', '%' . $buscar . '%')
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

    public function store($request)
    {
        $resp = Unidad::create($request);
        return $resp;
    }

    public function update($request, $id)
    {

        $resp = Unidad::findOrFail($id);

        $resp->update($request);

        return $resp;
    }

    public function cambiarEstado($request, $id)
    {
        $resp = Unidad::findOrFail($id);
        if (!$resp) {
            return false;
        }

        // validacion por productos
        // validacion por inventarios
        // validacion por compras

        $resp->estado = $request["estado"];
        $resp->save();


        return $resp;
    }

    public function storeTranformacion($request)
    {
        $resp = UnidadTransformacion::create([
            'unidad_id' => $request['unidad_id'],
            'unidad_to_id' => $request['unidad_to_id'],
            'empresa_id' => $request['empresa_id'],
            'estado' => 1
        ]);
        return $resp;
    }

    public function delete_transformacion($id)
    {
        $resp = UnidadTransformacion::findOrFail($id);

        if (!$resp) {
            return false;
        }

        return $resp->delete();
    }
}
