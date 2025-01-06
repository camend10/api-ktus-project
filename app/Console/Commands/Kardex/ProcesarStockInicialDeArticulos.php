<?php

namespace App\Console\Commands\Kardex;

use App\Models\Articulos\BodegaArticulo;
use App\Models\Empresa;
use App\Models\Kardex\ArticuloStockInicial;
use App\Models\Movimientos\DetalleSolicitud;
use Illuminate\Console\Command;

class ProcesarStockInicialDeArticulos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:procesar-stock-inicial-de-articulos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cada 1 del mes, se va a guardar el stock disponible en ese momento';

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
    }

    /**
     * Procesar los artículos de una empresa.
     *
     * @param int $empresa_id
     */
    private function procesarArticulosPorEmpresa($empresa_id)
    {

        $bodega_articulo = BodegaArticulo::where('empresa_id', $empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        if ($bodega_articulo->isEmpty()) {
            $this->info("No hay bodegas registradas en la empresa ID: {$empresa_id}");
            return;
        }

        foreach ($bodega_articulo as $bodega) {
            $precio_avg = 0;
            $fecha_mes_anterior = now()->subMonth(1);
            $precio_avg =  DetalleSolicitud::where('fecha_entrega', "<>", NULL)
                ->where('articulo_id', $bodega->articulo_id)
                ->where('unidad_id', $bodega->unidad_id)
                ->where('empresa_id', $bodega->empresa_id)

                ->wherehas('solicitud', function ($subq) use ($bodega) {
                    $subq->where('bodega_id', $bodega->bodega_id);
                })
                ->whereYear("fecha_entrega", $fecha_mes_anterior->format('Y'))
                ->whereMonth("fecha_entrega", $fecha_mes_anterior->format('m'))
                ->avg('costo');

            if (!$precio_avg) {
                $articulo_inicial_last = ArticuloStockInicial::where('articulo_id', $bodega->articulo_id)
                    ->where('unidad_id', $bodega->unidad_id)
                    ->where('empresa_id', $bodega->empresa_id)
                    ->where('bodega_id', $bodega->bodega_id)
                    ->where('precio_avg', ">", 0)
                    ->orderBy("id", "desc")
                    ->first();

                if ($articulo_inicial_last) {
                    $precio_avg = $articulo_inicial_last->precio_avg;
                }
            }

            ArticuloStockInicial::create([
                'articulo_id' => $bodega->articulo_id,
                'bodega_id' => $bodega->bodega_id,
                'cantidad' => $bodega->cantidad,
                'empresa_id' => $bodega->empresa_id,
                'estado' => 1,
                'unidad_id' => $bodega->unidad_id,
                'precio_avg' => $precio_avg ?? 0,
            ]);
        }
    }
}
