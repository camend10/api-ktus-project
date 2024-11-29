<?php

namespace App\Models\Articulos;

use App\Models\Configuracion\Bodega;
use App\Models\Configuracion\Unidad;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodegaArticulo extends Model
{
    use HasFactory;

    protected $table = 'bodegas_articulos';

    protected $fillable = [
        'articulo_id',
        'bodega_id',
        'cantidad',
        'empresa_id',
        'estado',
        'unidad_id',
    ];

    protected $casts = [
        'articulo_id' => 'integer',
        'unidad_id' => 'integer',
        'empresa_id' => 'integer',
        'estado' => 'integer',
        'bodega_id' => 'integer',
        'cantidad' => 'integer',
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

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id')->withDefault();
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id')->withDefault();
    }
    
    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'bodega_id')->withDefault();
    }
}
