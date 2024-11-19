<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Empresas\EmpresaRequest;
use App\Services\Configuracion\EmpresaService;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    protected $empresaService;

    public function __construct(EmpresaService $empresaService)
    {
        $this->empresaService = $empresaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $empresas = $this->empresaService->getEmpresasByFilter($buscar);

        return response()->json([
            'total' => $empresas->total(),
            'empresas' => $empresas->map(function ($empresa) {
                return [
                    'id' => $empresa->id,
                    'nit_empresa' => $empresa->nit_empresa,
                    'dv' => $empresa->dv,
                    'nombre' => $empresa->nombre,
                    'email' => $empresa->email,
                    'direccion' => $empresa->direccion,
                    'telefono' => $empresa->telefono,
                    'web' => $empresa->web,
                    'celular' => $empresa->celular,
                    'estado' => $empresa->estado,
                    'departamento_id' => $empresa->departamento_id,
                    'municipio_id' => $empresa->municipio_id,
                    'departamento' => $empresa->departamento->nombre,
                    'municipio' => $empresa->municipio->nombre,
                    "created_format_at" => $empresa->created_at ? $empresa->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmpresaRequest $request)
    {

        $validated = $request->validated();

        $empresa = $this->empresaService->storeEmpresa($validated);

        return response()->json([
            'message' => 200,
            'message_text' => 'La empresa se registró de manera exitosa',
            'empresa' => [
                'id' => $empresa->id,
                'nit_empresa' => $empresa->nit_empresa,
                'dv' => $empresa->dv,
                'nombre' => $empresa->nombre,
                'email' => $empresa->email,
                'direccion' => $empresa->direccion,
                'telefono' => $empresa->telefono,
                'web' => $empresa->web,
                'celular' => $empresa->celular,
                'estado' => $empresa->estado,
                'departamento_id' => $empresa->departamento_id,
                'municipio_id' => $empresa->municipio_id,
                'departamento' => $empresa->departamento->nombre,
                'municipio' => $empresa->municipio->nombre,
                "created_format_at" => $empresa->created_at ? $empresa->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmpresaRequest $request, string $id)
    {
        $validated = $request->validated();

        $empresa = $this->empresaService->updateEmpresa($validated, $id);

        return response()->json([
            'message' => 200,
            'message_text' => 'La empresa se editó de manera exitosa',
            'empresa' => [
                'id' => $empresa->id,
                'nit_empresa' => $empresa->nit_empresa,
                'dv' => $empresa->dv,
                'nombre' => $empresa->nombre,
                'email' => $empresa->email,
                'direccion' => $empresa->direccion,
                'telefono' => $empresa->telefono,
                'web' => $empresa->web,
                'celular' => $empresa->celular,
                'estado' => $empresa->estado,
                'departamento_id' => $empresa->departamento_id,
                'municipio_id' => $empresa->municipio_id,
                'departamento' => $empresa->departamento->nombre,
                'municipio' => $empresa->municipio->nombre,
                "created_format_at" => $empresa->created_at ? $empresa->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $empresa = $this->empresaService->cambiarEstado($request, $id);

        // if (!$empresa) {
        //     return response()->json([
        //         'message' => 403,
        //         'message_text' => 'Ocurrio un error',
        //         'empresa' => []
        //     ], 403);
        // }

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Empresa activada de manera exitosa';
        } else {
            $texto = 'Empresa eliminada de manera exitosa';
        }

        if ($empresa == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Empresa no encontrada',
                'empresa' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'empresa' => [
                'id' => $empresa->id,
                'nit_empresa' => $empresa->nit_empresa,
                'dv' => $empresa->dv,
                'nombre' => $empresa->nombre,
                'email' => $empresa->email,
                'direccion' => $empresa->direccion,
                'telefono' => $empresa->telefono,
                'web' => $empresa->web,
                'celular' => $empresa->celular,
                'estado' => $empresa->estado,
                'departamento_id' => $empresa->departamento_id,
                'municipio_id' => $empresa->municipio_id,
                'departamento' => $empresa->departamento->nombre,
                'municipio' => $empresa->municipio->nombre,
                "created_format_at" => $empresa->created_at ? $empresa->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }
}
