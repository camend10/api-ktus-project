<?php

namespace App\Exports\Kardex;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DownloadKardex implements FromView
{
    protected $kardex_articulos;
    public function __construct($kardex_articulos)
    {
        $this->kardex_articulos = $kardex_articulos;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        return view('kardex.kardex_articulos', [
            "kardex_articulos" => $this->kardex_articulos,
        ]);
    }
}
