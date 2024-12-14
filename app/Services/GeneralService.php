<?php

namespace App\Services;

use App\Models\Configuracion\Bodega;
use App\Models\Configuracion\Categoria;
use App\Models\Configuracion\Iva;
use App\Models\Configuracion\MetodoPago;
use App\Models\Configuracion\Proveedor;
use App\Models\Configuracion\Sede;
use App\Models\Configuracion\SedeDeliverie;
use App\Models\Configuracion\SegmentoCliente;
use App\Models\Configuracion\Unidad;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Genero;
use App\Models\Municipio;
use App\Models\TipoDocumento;
use App\Models\User;
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

    public function bodegas($empresa_id)
    {
        return Bodega::where('empresa_id', $empresa_id)
            ->where('estado', 1)
            ->get();
    }

    public function unidades($empresa_id)
    {
        return Unidad::where('empresa_id', $empresa_id)
            ->where('estado', 1)
            ->get();
    }

    public function ivas($empresa_id)
    {
        return Iva::where('empresa_id', $empresa_id)
            ->where('estado', 1)
            ->get();
    }

    public function segmentos_clientes($empresa_id)
    {
        return SegmentoCliente::where('empresa_id', $empresa_id)
            ->where('estado', 1)
            ->get();
    }

    public function categorias($empresa_id)
    {
        return Categoria::where('empresa_id', $empresa_id)
            ->where('estado', 1)
            ->get();
    }

    public function proveedores($empresa_id)
    {
        return Proveedor::with(['tipodocumento'])
            ->where('empresa_id', $empresa_id)
            ->where('estado', 1)
            ->get();
    }

    public function sedeDeliveries($empresa_id)
    {
        return SedeDeliverie::where('empresa_id', $empresa_id)
            ->where('estado', 1)
            ->get();
    }

    public function metodoPagos($empresa_id)
    {
        return MetodoPago::where('empresa_id', $empresa_id)
            ->where('metodo_pago_id', null)
            ->where('estado', 1)
            ->get();
    }

    public function vendedores($empresa_id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return false;
        }
        if ($user && !in_array($user->role_id, [1, 2])) {

            return User::where('empresa_id', $empresa_id)
                ->where('sede_id', $user->sede_id)
                ->where('role_id', '!=', 1)
                ->where('estado', 1)
                ->get();
        } else {

            return User::where('empresa_id', $empresa_id)
                ->where('role_id', '!=', 1)
                ->where('estado', 1)
                ->get();
        }
    }

    public function getEmpresa($empresa_id)
    {
        return Empresa::where('id', $empresa_id)
            ->first();
    }
}
