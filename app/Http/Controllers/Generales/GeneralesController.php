<?php

namespace App\Http\Controllers\Generales;

use App\Http\Controllers\Controller;
use App\Services\GeneralService;
use Illuminate\Http\Request;

class GeneralesController extends Controller
{
    protected $generalService;
    protected $userService;
    protected $simulacroService;
    protected $materiaService;

    public function __construct(GeneralService $generalService)
    {
        $this->generalService = $generalService;
    }

    public function configuraciones(Request $request)
    {
        $departamentos = $this->generalService->getDepartamentos();
        $empresas = $this->generalService->empresas($request->empresa_id);
        $sedes = $this->generalService->sedes($request->empresa_id);
        $tipodocumentos = $this->generalService->getTipoDocs();
        $generos = $this->generalService->generos();
        $roles = $this->generalService->roles();
        $segmentos_clientes = $this->generalService->segmentos_clientes($request->empresa_id);
        $sede_deliveries = $this->generalService->sedeDeliveries($request->empresa_id);

        $muni = $this->generalService->getMunicipios();
        $municipios = [];
        foreach ($muni as $item) {
            $municipios[$item->departamento_id][] = [
                'id' => $item->id,
                'nombre' => strtoupper($item->nombre),
            ];
        }

        $arraySedes = [];
        foreach ($sedes as $item) {
            $arraySedes[$item->empresa_id][] = [
                'id' => $item->id,
                'nombre' => strtoupper($item->nombre),
            ];
        }

        if ($departamentos) {
            return response()->json([
                'departamentos' => $departamentos,
                'empresas' => $empresas,
                'sedes' => $sedes,
                'tipodocumentos' => $tipodocumentos,
                'generos' => $generos,
                'roles' => $roles,
                'municipios' => $municipios,
                'arraySedes' => $arraySedes,
                'segmentos_clientes' => $segmentos_clientes,
                'sede_deliveries' => $sede_deliveries,
            ], 200);
        } else {
            return response()->json([
                'message' => 403,
                'error' => "Lo sentimos, ocurrió un error en el servidor: ",
            ], 500);
        }
    }

    public function departamentos()
    {
        $departamentos = $this->generalService->getDepartamentos();
        if ($departamentos) {
            return response()->json([
                'departamentos' => $departamentos,
            ], 200);
        } else {
            return response()->json([
                'message' => 403,
                'error' => "Lo sentimos, ocurrió un error en el servidor: ",
            ], 500);
        }
    }

    public function municipios()
    {
        $muni = $this->generalService->getMunicipios();

        $municipios = [];
        foreach ($muni as $item) {
            $municipios[$item->departamento_id][] = [
                'id' => $item->id,
                'nombre' => strtoupper($item->nombre),
            ];
        }
        if ($muni) {
            return response()->json([
                'municipios' => $municipios,
            ], 200);
        } else {
            return response()->json([
                'message' => 403,
                'error' => "Lo sentimos, ocurrió un error en el servidor: ",
            ], 500);
        }
    }

    public function tipodocs()
    {
        $tipodocumentos = $this->generalService->getTipoDocs();
        if ($tipodocumentos) {
            return response()->json([
                'tipodocumentos' => $tipodocumentos,
            ], 200);
        } else {
            return response()->json([
                'message' => 403,
                'error' => "Lo sentimos, ocurrió un error en el servidor: ",
            ], 500);
        }
    }

    public function generos()
    {
        $generos = $this->generalService->generos();
        if ($generos) {
            return response()->json([
                'generos' => $generos,
            ], 200);
        } else {
            return response()->json([
                'message' => 403,
                'error' => "Lo sentimos, ocurrió un error en el servidor: ",
            ], 500);
        }
    }

    public function roles()
    {
        $roles = $this->generalService->roles();
        if ($roles) {
            return response()->json([
                'roles' => $roles,
            ], 200);
        } else {
            return response()->json([
                'message' => 403,
                'error' => "Lo sentimos, ocurrió un error en el servidor: ",
            ], 500);
        }
    }

    public function empresas(Request $request)
    {
        $empresas = $this->generalService->empresas($request->empresa_id);
        if ($empresas) {
            return response()->json([
                'empresas' => $empresas,
            ], 200);
        } else {
            return response()->json([
                'message' => 403,
                'error' => "Lo sentimos, ocurrió un error en el servidor: ",
            ], 500);
        }
    }

    public function articulos(Request $request)
    {
        $bodegas = $this->generalService->bodegas($request->empresa_id);
        $sedes = $this->generalService->sedes($request->empresa_id);
        $empresas = $this->generalService->empresas($request->empresa_id);
        $unidades = $this->generalService->unidades($request->empresa_id);
        $ivas = $this->generalService->ivas($request->empresa_id);
        $segmentos_clientes = $this->generalService->segmentos_clientes($request->empresa_id);
        $categorias = $this->generalService->categorias($request->empresa_id);
        $proveedores = $this->generalService->proveedores($request->empresa_id);

        if ($empresas) {
            return response()->json([
                'unidades' => $unidades,
                'empresas' => $empresas,
                'sedes' => $sedes,
                'bodegas' => $bodegas,
                'segmentos_clientes' => $segmentos_clientes,
                'ivas' => $ivas,
                'categorias' => $categorias,
                'proveedores' => $proveedores,
            ], 200);
        } else {
            return response()->json([
                'message' => 403,
                'error' => "Lo sentimos, ocurrió un error en el servidor: ",
            ], 500);
        }
    }
}
