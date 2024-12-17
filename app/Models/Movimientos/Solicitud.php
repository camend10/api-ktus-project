<?php

namespace App\Models\Movimientos;

use App\Models\Configuracion\Bodega;
use App\Models\Configuracion\Proveedor;
use App\Models\Configuracion\Sede;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'movimientos';

    protected $fillable = [
        'fecha_emision',
        'tipo_movimiento',
        'observacion',
        'observacion_entrega',
        'destino',
        'total',
        'user_id',
        'bodega_id',
        'plantilla_id',
        'proveedor_id',
        'empresa_id',
        'sede_id',
        'estado',
        'fecha_entrega',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_entrega' => 'date',
        'tipo_movimiento' => 'integer',
        'estado' => 'integer',
        'total' => 'float',
        'empresa_id' => 'integer',
        'sede_id' => 'integer',
        'user_id' => 'integer',
        'proveedor_id' => 'integer',
        'bodega_id' => 'integer',
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

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id')->withDefault();
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'bodega_id')->withDefault();
    }

    public function detalles_movimientos()
    {
        return $this->hasMany(DetalleMovimiento::class, 'movimiento_id');
    }

    public function detalle_entregados()
    {
        return $this->hasMany(DetalleMovimiento::class, 'movimiento_id')
            ->where('estado', 2);
    }


    public function scopeFilterAdvance($query, $data)
    {
        // Log::error('Error al crear la factura: ' . json_encode($data));

        // Normaliza los valores especiales
        $data['buscar'] = $data['buscar'] ?? null;
        $data['articulo'] = $data['articulo'] ?? null;
        $data['bodega_id'] = isset($data['bodega_id']) && $data['bodega_id'] == 9999999 ? null : ($data['bodega_id'] ?? null);
        $data['proveedor_id'] = isset($data['proveedor_id']) && $data['proveedor_id'] == 9999999 ? null : ($data['proveedor_id'] ?? null);
        $data['fecha_inicio'] = $data['fecha_inicio'] ?? null;
        $data['fecha_final'] = $data['fecha_final'] ?? null;
        $data['usuario_id'] = isset($data['usuario_id']) && $data['usuario_id'] == 9999999 ? null : ($data['usuario_id'] ?? null);

        $query->when($data['buscar'], function ($sql) use ($data) {
            $sql->where('id', $data['buscar']);
        });

        $query->when(isset($data['articulo']), function ($sql) use ($data) {
            $sql->whereHas('detalles_movimientos', function ($q) use ($data) {
                $q->whereHas('articulo', function ($sub) use ($data) {
                    $sub->where('nombre', "like", "%" . $data['articulo'] . "%");
                });
            });
        });

        // Filtro por bodega_id
        $query->when(isset($data['bodega_id']), function ($sql) use ($data) {
            $sql->where('bodega_id', $data['bodega_id']);
        });

        // Filtro por proveedor_id
        $query->when(isset($data['proveedor_id']), function ($sql) use ($data) {
            $sql->where('proveedor_id', $data['proveedor_id']);
        });

        // Filtro por fecha
        $query->when($data['fecha_inicio'] && $data['fecha_final'], function ($sql) use ($data) {
            $sql->whereBetween('created_at', [
                Carbon::parse($data['fecha_inicio'])->format('Y-m-d') . " 00:00:00",
                Carbon::parse($data['fecha_final'])->format('Y-m-d') . " 23:59:59"
            ]);
        });

        // Filtro por usuario_id
        $query->when(isset($data['usuario_id']), function ($sql) use ($data) {
            $sql->where('user_id', $data['usuario_id']);
        });

        return $query;
    }
}
