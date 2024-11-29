<?php

namespace App\Services\Configuracion;

use App\Models\Configuracion\Categoria;

class CategoriaService
{

    public function getByFilter($buscar)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        return Categoria::where('nombre', 'like', '%' . $buscar . '%')
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

    public function store($request)
    {
        $resp = Categoria::create($request);
        return $resp;
    }

    public function update($request, $id)
    {

        $resp = Categoria::findOrFail($id);

        $resp->update($request);

        return $resp;
    }

    public function cambiarEstado($request, $id)
    {
        $resp = Categoria::findOrFail($id);
        if (!$resp) {
            return false;
        }

        // Actualizar el estado del usuario
        $resp->estado = $request["estado"];
        $resp->save();

        // validacion por usuarios
        return $resp;
    }

    
    public function getCategoriaById($id)
    {
        return Categoria::findOrFail($id);
    }
}
