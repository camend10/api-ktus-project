<?php

namespace App\Services\Configuracion;

use App\Models\Configuracion\Sede;

class SedeService
{

    public function getSedesByFilter($buscar)
    {
        $user = auth('api')->user();
        
        return Sede::where('nombre', 'like', '%' . $buscar . '%')
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

    public function existeSede($campo, $tipo)
    {
        if ($tipo == "codigo") {
            return Sede::where('codigo', $campo)
                ->first();
        }

        if ($tipo == "nombre") {
            return Sede::where('nombre', $campo)
                ->first();
        }
    }

    public function storeSede($request)
    {
        $sede = Sede::create($request);
        return $sede;
    }

    public function existeSede2($campo, $tipo, $id)
    {
        if ($tipo == "codigo") {
            return Sede::where('codigo', $campo)
                ->where('id', '!=', $id)
                ->first();
        }

        if ($tipo == "nombre") {
            return Sede::where('nombre', $campo)
                ->where('id', '!=', $id)
                ->first();
        }
    }

    public function updateSede($request, $id)
    {

        $sede = Sede::findOrFail($id);

        $sede->update($request);

        return $sede;
    }

    public function cambiarEstado($request, $id)
    {
        $sede = Sede::findOrFail($id);
        if (!$sede) {
            return false;
        }

        // Actualizar el estado del usuario
        $sede->estado = $request["estado"];
        $sede->save();

        // validacion por usuarios
        return $sede;
    }
}
