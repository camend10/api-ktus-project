<?php

namespace App\Services\Facturas;

use App\Models\Facturas\Factura;

class FacturaService
{

    public function getByFilter($data)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        if ($user && !in_array($user->role_id, [1, 2])) {
            return Factura::FilterAdvance($data)
                ->where('empresa_id', $user->empresa_id)
                ->where('sede_id', $user->sede_id)
                ->orderBy('id', 'desc')
                ->paginate(20);
        } else {
            return Factura::FilterAdvance($data)
                ->where('empresa_id', $user->empresa_id)
                ->orderBy('id', 'desc')
                ->paginate(20);
        }
    }

    public function getAllFacturas($data)
    {

        return Factura::FilterAdvance($data)
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

        $resp = Factura::create($request);
        return $resp;
    }

    public function update($request, $id)
    {

        $resp = Factura::findOrFail($id);

        $resp->update($request);

        return $resp;
    }

    public function cambiarEstado($request, $id)
    {
        $resp = Factura::findOrFail($id);
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
        return Factura::findOrFail($id);
    }
}
