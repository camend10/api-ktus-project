<?php

namespace App\Models\Facturas;

use App\Models\Configuracion\MetodoPago;
use App\Models\Configuracion\Sede;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaPago extends Model
{
    use HasFactory;

    protected $table = 'facturas_pagos';

    protected $fillable = [
        'monto',
        'metodo_pago_id',
        'factura_id',
        'fecha_validacion',
        'n_transaccion',
        'empresa_id',
        'sede_id',
        'estado',
        'banco_id',
        'imagen',
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

    public function metodo_pago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id')->withDefault();
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id')->withDefault();
    }
}
