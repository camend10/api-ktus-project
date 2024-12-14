<?php

namespace App\Exports\Factura;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class FacturaDetalleExport implements FromView
{
    protected $detalles;
    public function __construct($detalles)
    {
        $this->detalles = $detalles;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        return view('factura.detalle_facturas', [
            "detalles" => $this->detalles,
        ]);
    }
}
