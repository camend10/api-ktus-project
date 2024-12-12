<?php

namespace App\Models\Facturas;

use App\Models\Configuracion\Sede;
use App\Models\Configuracion\SedeDeliverie;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Municipio;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaDeliverie extends Model
{
    use HasFactory;

    protected $table = 'facturas_deliveries';

    protected $fillable = [
        'sede_deliverie_id',
        'factura_id',
        'fecha_entrega',
        'direccion',
        'empresa_id',
        'sede_id',
        'estado',
        'fecha_envio',
        'departamento_id',
        'municipio_id',
        'agencia',
        'encargado',
        'documento',
        'celular',
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

    public function sede_deliverie()
    {
        return $this->belongsTo(SedeDeliverie::class, 'sede_deliverie_id')->withDefault();
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id')->withDefault();
    }
    
    public function departamento()
    {
        return $this->belongsTo(Departamento::class)->withDefault();
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class)->withDefault();
    }

}
