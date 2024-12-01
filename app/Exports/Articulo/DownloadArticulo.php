<?php

namespace App\Exports\Articulo;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DownloadArticulo implements FromView
{

    protected $articulos;
    public function __construct($articulos) {
        $this->articulos = $articulos;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        return view('articulo.download_articulo',[
            "articulos" => $this->articulos,
        ]);
    }
}
