<?php

namespace App\Exports\Factura;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class FacturaGeneralExport implements FromView
{
    protected $facturas;
    public function __construct($facturas)
    {
        $this->facturas = $facturas;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        return view('factura.factura_general', [
            "facturas" => $this->facturas,
        ]);
    }
}
