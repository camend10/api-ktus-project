<?php

namespace App\Services\Movimientos;

use App\Models\Articulos\BodegaArticulo;
use App\Models\Movimientos\DetallePlantilla;
use App\Models\Movimientos\Plantilla;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PlantillaService
{
    public function getByFilter($buscar)
    {
        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        if ($user && !in_array($user->role_id, [1, 2])) {
            return Plantilla::with([
                'empresa',
                'sede',
                'usuario',
                'detalles_plantillas.articulo',
                'detalles_plantillas.unidad'
            ])
                ->where('nombre', 'like', '%' . $buscar . '%')
                ->where("empresa_id", $user->empresa_id)
                ->where("sede_id", $user->sede_id)
                ->orderBy("id", "desc")
                ->paginate(20);
        } else {
            return Plantilla::with([
                'empresa',
                'sede',
                'usuario',
                'detalles_plantillas.articulo',
                'detalles_plantillas.unidad'
            ])
                ->where('nombre', 'like', '%' . $buscar . '%')
                ->where("empresa_id", $user->empresa_id)
                ->orderBy("id", "desc")
                ->paginate(20);
        }
    }

    public function store($request)
    {
        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        try {
            // Inicia la transacción
            DB::beginTransaction();

            $respuesta = Plantilla::create([
                'codigo' => $request["codigo"],
                'nombre' => $request["nombre"],
                'observacion' => $request["observacion"],
                'user_id' => $request["user_id"],
                'empresa_id' => $request["empresa_id"],
                'sede_id' => $request["sede_id"],
                'estado' => $request["estado"],
            ]);


            $detalles = $request["detalles_plantillas"] ?? [];

            foreach ($detalles as $detalle) {
                DetallePlantilla::create([
                    'costo' => $detalle["costo"],
                    'total_costo' => $detalle["total_costo"],
                    'cantidad' => $detalle["cantidad"],
                    'cantidad_recibida' => $detalle["cantidad_recibida"],
                    'plantilla_id' => $respuesta->id,
                    'articulo_id' => $detalle["articulo"]["id"],
                    'empresa_id' => $detalle["empresa_id"],
                    'sede_id' => $detalle["sede_id"],
                    'unidad_id' => $detalle["unidad"]["id"],
                    'estado' => $detalle["estado"]
                ]);
            }

            // Confirma la transacción
            DB::commit();

            return $respuesta;
        } catch (\Throwable $e) {
            // Revierte la transacción si ocurre un error
            DB::rollBack();
            Log::error('Error al crear la plantilla: ' . $e->getMessage());
            throw new HttpException(500, $e->getMessage());
            return false;
        }
    }

    public function update($request, $id)
    {
        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        try {
            // Inicia la transacción
            DB::beginTransaction();

            // Determina si es una actualización o creación
            $plantilla = Plantilla::findOrFail($id);

            // Asignar datos comunes a la factura
            $plantilla->fill([
                'codigo' => $request["codigo"],
                'nombre' => $request["nombre"],
                'observacion' => $request["observacion"],
                'user_id' => $request["user_id"],
                'empresa_id' => $request["empresa_id"],
                'sede_id' => $request["sede_id"],
                'estado' => $request["estado"],
            ]);

            $plantilla->save();

            // Sincronizar detalles de factura
            $detalles = $request["detalles_plantillas"] ?? [];
            // Log::error('Error al crear la factura: ' . json_encode($detalle_factura));

            $detalle_ids = [];

            foreach ($detalles as $detalle) {
                $detalle_model = DetallePlantilla::updateOrCreate(
                    [
                        "plantilla_id" => $plantilla->id,
                        "articulo_id" => $detalle["articulo"]["id"],
                        'unidad_id' => $detalle["unidad"]["id"],
                        'empresa_id' => $detalle["empresa_id"],
                        'sede_id' => $detalle["sede_id"]
                    ],
                    [
                        'costo' => $detalle["costo"],
                        'total_costo' => $detalle["total_costo"],
                        'cantidad' => $detalle["cantidad"],
                        'cantidad_recibida' => $detalle["cantidad_recibida"],
                        'estado' => $detalle["estado"]
                    ]
                );
                $detalle_ids[] = $detalle_model->id;
            }

            // Eliminar registros que no estén en los nuevos detalles
            DetallePlantilla::where("plantilla_id", $plantilla->id)
                ->whereNotIn("id", $detalle_ids)
                ->delete();

            // Confirmar transacción
            DB::commit();

            return $plantilla;
        } catch (\Throwable $e) {
            // Revierte la transacción en caso de error
            DB::rollBack();
            Log::error('Error al crear o actualizar la plantilla: ' . $e->getMessage());
            throw new HttpException(500, $e->getMessage());
        }
    }

    public function cambiarEstado($request, $id)
    {
        $resp = $this->getById($id);
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
        return Plantilla::with([
            'empresa',
            'sede',
            'usuario',
            'detalles_plantillas.articulo',
            'detalles_plantillas.unidad'
        ])
            ->findOrFail($id);
    }

}
