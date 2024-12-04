<?php

namespace App\Exports\Cliente;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportCliente implements FromView
{

    protected $clientes;
    public function __construct($clientes) {
        $this->clientes = $clientes;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        return view('clientes.export_cliente',[
            "clientes" => $this->clientes,
        ]);
    }
}
