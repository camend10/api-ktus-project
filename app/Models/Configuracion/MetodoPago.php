<?php

namespace App\Models\Configuracion;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodo_pago';

    protected $fillable = [
        'nombre',
        'empresa_id',
        'metodo_pago_id',
        'estado'
    ];

    protected $casts = [
        'metodo_pago_id' => 'integer',
        'empresa_id' => 'integer',
        'estado' => 'integer',
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

    // Padre
    public function metodo_pago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id')->withDefault();
    }

    // Hijo
    public function metodo_pagos()
    {
        return $this->hasMany(MetodoPago::class, 'metodo_pago_id');
    }
}
