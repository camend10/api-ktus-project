<?php

namespace App\Models\Facturas;

use App\Models\Clientes\Cliente;
use App\Models\Configuracion\Sede;
use App\Models\Configuracion\SegmentoCliente;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    protected $fillable = [
        'total_venta',
        'total_descuento',
        'total_iva',
        'descripcion',
        'domicilio',
        'user_id',
        'cliente_id',
        'empresa_id',
        'sede_id',
        'estado',
        'segmento_cliente_id',
        'sub_total',
        'estado_factura',
        'estado_pago',
        'deuda',
        'pago_out',
        'fecha_validacion',
        'fecha_pago_total',
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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id')->withDefault();
    }

    public function segmento()
    {
        return $this->belongsTo(SegmentoCliente::class, 'segmento_cliente_id')->withDefault();
    }
}
