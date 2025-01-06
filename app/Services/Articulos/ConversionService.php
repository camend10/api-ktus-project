<?php

namespace App\Services\Articulos;

use App\Models\Articulos\BodegaArticulo;
use App\Models\Articulos\Conversion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConversionService
{
    public function getByFilter($data)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }
        if ($user && !in_array($user->role_id, [1, 2])) {
            return Conversion::with([
                'empresa',
                'sede',
                'usuario',
                'articulo',
                'bodega',
                'unidad_inicio',
                'unidad_final',
            ])
                ->FilterAdvance($data)
                ->where('empresa_id', $user->empresa_id)
                ->where("sede_id", $user->sede_id)
                ->orderBy('id', 'desc')
                ->paginate(20);
        } else {
            return Conversion::with([
                'empresa',
                'sede',
                'usuario',
                'articulo',
                'bodega',
                'unidad_inicio',
                'unidad_final',
            ])
                ->FilterAdvance($data)
                ->where('empresa_id', $user->empresa_id)
                ->orderBy('id', 'desc')
                ->paginate(20);
        }
    }

    public function store($request)
    {
        try {
            // Inicia la transacción
            DB::beginTransaction();

            // DISMINUIR EL STOCK
            $stock_inicial = BodegaArticulo::where('articulo_id', $request["articulo_id"])
                ->where('unidad_id', $request["unidad_inicio_id"])
                ->where('bodega_id', $request["bodega_id"])
                ->where('empresa_id', $request["empresa_id"])
                ->first();

            if ($stock_inicial) {
                if (($stock_inicial->cantidad - $request["cantidad_final"]) < 0) {
                    return [
                        'error' => true,
                        'code' => 400,
                        'message' => 'No se puede crear esta conversión por que no se cuenta con el stock.',
                    ];
                }
                $stock_inicial->update([
                    "cantidad" => $stock_inicial->cantidad - $request["cantidad_final"],
                ]);
            }

            // AUMENTAR EL STOCK
            $stock_final = BodegaArticulo::where('articulo_id', $request["articulo_id"])
                ->where('unidad_id', $request["unidad_final_id"])
                ->where('bodega_id', $request["bodega_id"])
                ->where('empresa_id', $request["empresa_id"])
                ->first();

            if ($stock_final) {
                $stock_final->update([
                    "cantidad" => $stock_final->cantidad + $request["cantidad_convertida"],
                ]);
            } else {
                BodegaArticulo::create([
                    "articulo_id" => $request["articulo_id"],
                    "bodega_id" => $request["bodega_id"],
                    "cantidad" => $request["cantidad_convertida"],
                    "empresa_id" => $request["empresa_id"],
                    "estado" => 1,
                    "unidad_id" => $request["unidad_final_id"],
                ]);
            }

            $resp = Conversion::create($request);

            if (!$resp) {
                return [
                    'error' => true,
                    'code' => 400,
                    'message' => 'No se puede crear esta conversión.',
                ];
            }

            DB::commit();

            return $resp;
        } catch (\Throwable $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            Log::error('Error al crear la conversión: ' . $e->getMessage());
            throw new HttpException(500, 'Error al eliminar la factura.');
        }
    }

    public function update($request, $id)
    {

        $resp = Conversion::findOrFail($id);

        $resp->update($request);

        return $resp;
    }

    public function cambiarEstado($request, $id)
    {
        try {
            // Inicia la transacción
            DB::beginTransaction();

            $conversion = Conversion::findOrFail($id);
            if (!$conversion) {
                return [
                    'error' => true,
                    'code' => 400,
                    'message' => 'La conversión no existe.',
                ];
            }

            if ($conversion) {
                // AUMENTAR EL STOCK
                $stock_inicial = BodegaArticulo::where('articulo_id', $conversion->articulo_id)
                    ->where('unidad_id', $conversion->unidad_inicio_id)
                    ->where('bodega_id', $conversion->bodega_id)
                    ->where('empresa_id', $conversion->empresa_id)
                    ->first();

                if ($stock_inicial) {
                    $stock_inicial->update([
                        "cantidad" => $stock_inicial->cantidad + $conversion->cantidad_final,
                    ]);
                }

                // DISMINUIR EL STOCK
                $stock_final = BodegaArticulo::where('articulo_id', $conversion->articulo_id)
                    ->where('unidad_id', $conversion->unidad_final_id)
                    ->where('bodega_id', $conversion->bodega_id)
                    ->where('empresa_id', $conversion->empresa_id)
                    ->first();

                if ($stock_final) {
                    if (($stock_final->cantidad - $conversion->cantidad_convertida) < 0) {
                        return [
                            'error' => true,
                            'code' => 400,
                            'message' => 'No se puede eliminar esta conversión por que no se cuenta con el stock.',
                        ];
                    }
                    $stock_final->update([
                        "cantidad" => $stock_final->cantidad - $conversion->cantidad_convertida,
                    ]);
                }
            }

            $conversion->estado = 0;
            $conversion->save();

            // Confirmar la transacción
            DB::commit();

            return $conversion;

            // return [
            //     'error' => false,
            // ];
        } catch (\Throwable $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            Log::error('Error al eliminar la conversión: ' . $e->getMessage());
            throw new HttpException(500, 'Error al eliminar la factura.');
        }
    }
}
