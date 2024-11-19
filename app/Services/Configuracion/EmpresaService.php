<?php

namespace App\Services\Configuracion;

use App\Models\Empresa;

class EmpresaService
{

    public function getEmpresasByFilter($buscar)
    {
        return Empresa::where('nombre', 'like', '%' . $buscar . '%')
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

    public function existeEmpresa($campo, $tipo)
    {
        if ($tipo == "nit_empresa") {
            return Empresa::where('nit_empresa', $campo)
                ->first();
        }

        if ($tipo == "nombre") {
            return Empresa::where('nombre', $campo)
                ->first();
        }
    }

    public function storeEmpresa($request)
    {
        $empresa = Empresa::create($request);
        return $empresa;
    }

    public function existeEmpresa2($campo, $tipo, $id)
    {
        if ($tipo == "nit_empresa") {
            return Empresa::where('nit_empresa', $campo)
                ->where('id', '!=', $id)
                ->first();
        }

        if ($tipo == "nombre") {
            return Empresa::where('nombre', $campo)
                ->where('id', '!=', $id)
                ->first();
        }
    }

    public function updateEmpresa($request, $id)
    {

        $empresa = Empresa::findOrFail($id);

        $empresa->update($request);

        return $empresa;
    }

    public function cambiarEstado($request, $id)
    {
        $empresa = Empresa::findOrFail($id);
        if (!$empresa) {
            return false;
        }

        // Actualizar el estado del usuario
        $empresa->estado = $request["estado"];
        $empresa->save();

        // validacion por usuarios
        return $empresa;
    }
}
