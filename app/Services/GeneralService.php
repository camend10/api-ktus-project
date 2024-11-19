<?php

namespace App\Services;

use App\Models\Configuracion\Sede;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Genero;
use App\Models\Municipio;
use App\Models\TipoDocumento;
use Spatie\Permission\Models\Role;

class GeneralService
{
    public function getDepartamentos()
    {
        return Departamento::all();
    }

    public function getMunicipios()
    {
        return Municipio::all();
    }

    public function getTipoDocs()
    {
        return TipoDocumento::where('estado', 1)->get();
    }

    public function generos()
    {
        return Genero::where('estado', 1)->get();
    }

    public function roles()
    {
        return Role::all();
    }

    public function empresas($empresa_id)
    {
        return Empresa::where('id', $empresa_id)
            ->where('estado', 1)
            ->get();
    }

    public function sedes($empresa_id)
    {
        return Sede::where('empresa_id', $empresa_id)
            ->where('estado', 1)
            ->get();
    }
}
