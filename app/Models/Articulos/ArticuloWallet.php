<?php

namespace App\Models\Articulos;

use App\Models\Configuracion\Sede;
use App\Models\Configuracion\SegmentoCliente;
use App\Models\Configuracion\Unidad;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloWallet extends Model
{
    use HasFactory;

    protected $table = 'articulo_wallets';

    protected $fillable = [
        'articulo_id',
        'unidad_id',
        'segmento_cliente_id',
        'empresa_id',
        'precio',
        'estado',
        'sede_id',
    ];

    protected $casts = [
        'articulo_id' => 'integer',
        'unidad_id' => 'integer',
        'segmento_cliente_id' => 'integer',
        'empresa_id' => 'integer',
        'precio' => 'float',
        'estado' => 'integer',
        'sede_id' => 'integer',
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

    public function segmento_cliente()
    {
        return $this->belongsTo(SegmentoCliente::class, 'segmento_cliente_id')->withDefault();
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id')->withDefault();
    }

}
