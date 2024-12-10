<?php

namespace App\Services\Clientes;

use App\Models\Clientes\Cliente;

class ClienteService
{

    public function getByFilter($data)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        return Cliente::FilterAdvance($data)
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

    public function getAllClientes($data)
    {

        return Cliente::FilterAdvance($data)
            ->where('estado', 1)
            ->where('empresa_id', $data["empresa_id"])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function store($request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }
        $request["user_id"] = $user->id;
        
        $resp = Cliente::create($request);
        return $resp;
    }

    public function update($request, $id)
    {

        $resp = Cliente::findOrFail($id);

        $resp->update($request);

        return $resp;
    }

    public function cambiarEstado($request, $id)
    {
        $resp = Cliente::findOrFail($id);
        if (!$resp) {
            return false;
        }

        $resp->estado = $request["estado"];
        $resp->save();

        // validacion por usuarios
        return $resp;
    }


    public function getById($id)
    {
        return Cliente::findOrFail($id);
    }

}
