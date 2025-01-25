<?php

namespace App\Http\Controllers\Reportes;

use App\Exports\Articulo\DownloadArticulo;
use App\Http\Controllers\Controller;
use App\Http\Resources\Articulo\ArticuloCollection;
use App\Models\Articulos\Articulo;
use App\Services\Articulos\ArticuloService;
use App\Services\Configuracion\EmpresaService;
use App\Services\Reportes\ReporteService;
// use Barryvdh\DomPDF\PDF;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    protected $reporteService;
    protected $articuloService;
    protected $empresaService;

    public function __construct(
        ReporteService $reporteService,
        ArticuloService $articuloService,
        EmpresaService $empresaService
    ) {
        $this->reporteService = $reporteService;
        $this->articuloService = $articuloService;
        $this->empresaService = $empresaService;
    }

    public function baja_existencia(Request $request)
    {

        $this->authorize('baja_existencia', Articulo::class);

        $data = $request->all();

        $articulos = $this->reporteService->getBajaExistencia($data);

        if (!$articulos) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $num_art_agotados = $this->articuloService->getByDisponibilidad(3);
        $num_art_por_agotar = $this->articuloService->getByDisponibilidad(2);

        return response()->json([
            'total' => $articulos->total(),
            'articulos' => ArticuloCollection::make($articulos),
            'num_art_agotados' => $num_art_agotados,
            'num_art_por_agotar' => $num_art_por_agotar
        ]);
    }

    public function export_articulo_baja_existencia(Request $request)
    {
        $data = $request->all();

        $articulos = $this->reporteService->getAllArticulosBajaExistencia($data);

        return Excel::download(new DownloadArticulo($articulos), 'Articulos_baja_existencia.xlsx');
    }

    public function pdf_baja_existencia(Request $request)
    {
        set_time_limit(300); // 300 segundos (5 minutos)

        $data = $request->all();

        $data['categoria_id'] = isset($data['categoria_id']) && $data['categoria_id'] == 9999999 ? null : ($data['categoria_id'] ?? null);
        $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? $data["sede_usuario_id"] : ($data['sede_id'] ?? null);
        $data['bodega_id'] = isset($data['bodega_id']) && $data['bodega_id'] == 9999999 ? null : ($data['bodega_id'] ?? null);
        $data['proveedor_id'] = isset($data['proveedor_id']) && $data['proveedor_id'] == 9999999 ? null : ($data['proveedor_id'] ?? null);

        $articulos = $this->reporteService->getAllArticulosBajaExistencia($data);

        $articulos = $articulos->map(function ($item) use ($data) {
            $item->cantidadUnidadSede = $this->getCantidadUnidadYSede($item, (int) $data["sede_usuario_id"]);
            return $item;
        });

        $empresa = $this->empresaService->getEmpresaById($data["empresa_id"]);
        $sede = $this->reporteService->getSedeById($data["sede_id"]);
        if ($data["bodega_id"] === null) {
            $bodega = null; // O cualquier valor predeterminado
        } else {
            $bodega = $this->reporteService->getBodegaById($data["bodega_id"]);
        }

        if ($data["categoria_id"] === null) {
            $categoria = null; // O cualquier valor predeterminado
        } else {
            $categoria = $this->reporteService->getCategoriaById($data["categoria_id"]);
        }

        if ($data["proveedor_id"] === null) {
            $proveedor = null; // O cualquier valor predeterminado
        } else {
            $proveedor = $this->reporteService->getProveedorById($data["proveedor_id"]);
        }

        $software = config('globals.software');
        $titulo = "Reporte de Artículos con Baja Existencia";

        $pdf = PDF::loadView(
            "reportes.baja_existencia",
            compact('articulos', 'empresa', 'software', 'titulo', 'sede', 'bodega', 'categoria', 'proveedor')
        );
        $pdf->set_paper('A4', 'portrait');
        $pdf->set_option('isHtml5ParserEnabled', true);
        $pdf->set_option('isRemoteEnabled', true);
        $pdf->set_option('isPhpEnabled', true);
        $pdf->set_option('isFontSubsettingEnabled', true);

        return $pdf->stream('reporte_articulos_baja_existencia-' . Date('Y-m-d') . '-' . uniqid() . '.pdf');
    }

    public function getCantidadUnidadYSede($item, $sedeId)
    {
        // Verificar si faltan datos
        if (!isset($item->bodegas_articulos) || !$sedeId) {
            return (object) [
                'cantidad' => 0,
                'unidad' => '',
                'sede' => '',
            ];
        }

        // Encontrar la bodega que coincida con la unidad y la sede
        $bodegaArticulo = collect($item->bodegas_articulos)->first(function ($bodega) use ($item, $sedeId) {
            return $bodega->unidad->id == $item->punto_pedido_unidad_id && $bodega->bodega->sede_id == $sedeId;
        });

        return (object) [
            'cantidad' => $bodegaArticulo->cantidad ?? 0,
            'unidad' => $bodegaArticulo->unidad->nombre ?? '',
            'sede' => $bodegaArticulo->bodega->nombre ?? '',
        ];
    }
}
