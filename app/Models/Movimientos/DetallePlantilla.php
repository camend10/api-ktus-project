<?php

namespace App\Models\Movimientos;

use App\Models\Articulos\Articulo;
use App\Models\Configuracion\Bodega;
use App\Models\Configuracion\Sede;
use App\Models\Configuracion\Unidad;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePlantilla extends Model
{
    use HasFactory;

    protected $table = 'detalle_plantillas';

    protected $fillable = [
        'costo',
        'total_costo',
        'cantidad',
        'cantidad_recibida',
        'plantilla_id',
        'articulo_id',
        'empresa_id',
        'sede_id',
        'unidad_id',
        'estado'
    ];

    protected $casts = [
        'costo' => 'float',
        'total_costo' => 'float',
        'cantidad' => 'integer',
        'cantidad_recibida' => 'integer',
        'estado' => 'integer',
        'empresa_id' => 'integer',
        'sede_id' => 'integer',
        'plantilla_id' => 'integer',
        'unidad_id' => 'integer',
        'articulo_id' => 'integer'
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

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class, 'plantilla_id')->withDefault();
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id')->withDefault();
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id')->withDefault();
    }
}
