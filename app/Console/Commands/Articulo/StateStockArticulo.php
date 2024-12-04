<?php

namespace App\Console\Commands\Articulo;

use App\Models\Articulos\Articulo;
use App\Models\Empresa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StateStockArticulo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'articulo:state-stocks';
    protected $signature = 'articulo:state-stocks';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar al articulo 3 status (1 es disponible, 2 por agotar y 3 agotado)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $empresas = Empresa::where('estado', 1)->get(); // Obtener todas las empresas

        if ($empresas->isEmpty()) {
            $this->info("No hay empresas registradas.");
            return 0;
        }

        foreach ($empresas as $empresa) {
            $this->info("Procesando empresa ID: {$empresa->id} - {$empresa->nombre}");
            $this->procesarArticulosPorEmpresa($empresa->id);
        }

        $this->info("Proceso completado para todas las empresas.");


        // foreach ($articulos as $item) {
        //     if ($item->punto_pedido_unidad_id) {

        //         $punto_pedido = $item->punto_pedido;    //cantidad
        //         $punto_pedido_unidad_id = $item->punto_pedido_unidad_id; // unidad

        //         $stock_total = 0;
        //         $is_umbral = false;
        //         //Lista de existencia
        //         foreach ($item->bodegas_articulos as $bodega) {
        //             //Calcular la suma total de stock
        //             $stock_total += $bodega->cantidad;
        //             //Comparar la unidad del punto de pedido
        //             if ($bodega->unidad_id == $punto_pedido_unidad_id) {
        //                 //Saber si el punto de pedido es menor ó igual a la cantidad disponible
        //                 if ($bodega->cantidad <= $punto_pedido) {
        //                     // Asignar status de "Por Agotar"
        //                     $item->update([
        //                         "state_stock" => 2
        //                     ]);
        //                     $is_umbral = true;
        //                 }
        //             }
        //         }

        //         if ($stock_total == 0) {
        //             // Estado "Agotado"
        //             $item->update([
        //                 "state_stock" => 3
        //             ]);
        //         } elseif (!$is_umbral) {
        //             // Estado "Disponible"
        //             $item->update([
        //                 "state_stock" => 1
        //             ]);
        //         }
        //     }
        // }
    }

    /**
     * Procesar los artículos de una empresa.
     *
     * @param int $empresa_id
     */
    private function procesarArticulosPorEmpresa($empresa_id)
    {
        $articulos = Articulo::with('bodegas_articulos')
            ->where('estado', 1)
            ->where('empresa_id', $empresa_id)
            ->get();

        if ($articulos->isEmpty()) {
            $this->info("No hay artículos para procesar en la empresa ID: {$empresa_id}");
            return;
        }

        foreach ($articulos as $articulo) {
            if (!$articulo instanceof Articulo) {
                $this->error("El objeto no es una instancia de Articulo.");
                continue;
            }
            $this->procesarEstadoArticulo($articulo);
        }
    }

    /**
     * Procesar el estado de un artículo en función de su stock.
     *
     * @param Articulo $articulo
     * @return void
     */

    private function procesarEstadoArticulo(Articulo $articulo)
    {
        if (!$articulo->punto_pedido_unidad_id || $articulo->bodegas_articulos->isEmpty()) {
            Log::warning("El artículo ID {$articulo->id} no tiene punto de pedido o bodegas asociadas.");
            return;
        }

        $punto_pedido = $articulo->punto_pedido;
        $punto_pedido_unidad_id = $articulo->punto_pedido_unidad_id;
        $stock_total = 0;
        $is_umbral = false;

        foreach ($articulo->bodegas_articulos as $bodega) {
            // Calcular la suma total de stock
            $stock_total += $bodega->cantidad;

            // Comparar la unidad del punto de pedido
            if ($bodega->unidad_id == $punto_pedido_unidad_id) {
                if ($bodega->cantidad <= $punto_pedido) {
                    // Asignar estado "Por Agotar"
                    $articulo->update(["state_stock" => 2]);
                    // Log::info("Artículo ID {$articulo->id}: Estado actualizado a 'Por Agotar'.");
                    $is_umbral = true;
                }
            }
        }

        // Determinar el estado final del artículo
        if ($stock_total == 0) {
            $articulo->update(["state_stock" => 3]); // Estado "Agotado"
            // Log::info("Artículo ID {$articulo->id}: Estado actualizado a 'Agotado'.");
        } elseif (!$is_umbral) {
            $articulo->update(["state_stock" => 1]); // Estado "Disponible"
            // Log::info("Artículo ID {$articulo->id}: Estado actualizado a 'Disponible'.");
        }
    }
}
