<?php

namespace App\Models\Facturas;

use App\Models\Articulos\Articulo;
use App\Models\Configuracion\Categoria;
use App\Models\Configuracion\Iva;
use App\Models\Configuracion\Sede;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleFactura extends Model
{
    use HasFactory;

    protected $table = 'detalle_facturas';

    protected $fillable = [
        'precio_item',
        'total_precio',
        'total_iva',
        'cantidad_item',
        'factura_id',
        'articulo_id',
        'iva_id',
        'empresa_id',
        'sede_id',
        'estado',
        'categoria_id',
        'descuento',
        'sub_total'
    ];

    public function setCreatedAtAttribute($value)
    {
        date_default_timezone_set("America/Bogota");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value)
    {
        date_default_timezone_set("America/Bogota");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id')->withDefault();
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id')->withDefault();
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id')->withDefault();
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id')->withDefault();
    }

    public function iva()
    {
        return $this->belongsTo(Iva::class, 'iva_id')->withDefault();
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id')->withDefault();
    }
    
}