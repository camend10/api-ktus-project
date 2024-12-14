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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    protected $fillable = [
        'total_venta',
        'total_descuento',
        'total_iva',
        'descripcion',
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

    protected $casts = [
        'total_venta' => 'float',
        'total_descuento' => 'float',
        'total_iva' => 'float',
        'user_id' => 'integer',
        'cliente_id' => 'integer',
        'segmento_cliente_id' => 'integer',
        'empresa_id' => 'integer',
        'sede_id' => 'integer',
        'estado' => 'integer',
        'sub_total' => 'float',
        'deuda' => 'float',
        'pago_out' => 'float',
        'estado_pago' => 'integer',
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

    /**
     * Relación con DetalleFactura
     */
    public function detalles_facturas()
    {
        return $this->hasMany(DetalleFactura::class, 'factura_id');
    }

    /**
     * Relación con FacturaDeliverie
     */
    public function factura_deliverie()
    {
        return $this->hasOne(FacturaDeliverie::class, 'factura_id');
    }

    /**
     * Relación con FacturaPago
     */
    public function factura_pago()
    {
        return $this->hasMany(FacturaPago::class, 'factura_id');
    }

    public function scopeFilterAdvance($query, $data)
    {
        // Log::error('Error al crear la factura: ' . json_encode($data));

        // Normaliza los valores especiales
        $data['buscar'] = $data['buscar'] ?? null;
        $data['segmento_cliente_id'] = isset($data['segmento_cliente_id']) && $data['segmento_cliente_id'] == 9999999 ? null : ($data['segmento_cliente_id'] ?? null);
        $data['categoria_id'] = isset($data['categoria_id']) && $data['categoria_id'] == 9999999 ? null : ($data['categoria_id'] ?? null);
        $data['cliente'] = $data['cliente'] ?? null;
        $data['articulo'] = $data['articulo'] ?? null;
        $data['fecha_inicio'] = $data['fecha_inicio'] ?? null;
        $data['fecha_final'] = $data['fecha_final'] ?? null;
        $data['vendedor_id'] = isset($data['vendedor_id']) && $data['vendedor_id'] == 9999999 ? null : ($data['vendedor_id'] ?? null);

        $query->when($data['buscar'], function ($sql) use ($data) {
            $sql->where('id', $data['buscar']);
        });

        // Filtro por segmento_id
        $query->when(isset($data['segmento_cliente_id']), function ($sql) use ($data) {
            $sql->where('segmento_cliente_id', $data['segmento_cliente_id']);
        });

        // Filtro por categoría
        $query->when(isset($data['categoria_id']), function ($sql) use ($data) {
            $sql->whereHas('detalles_facturas', function ($sub) use ($data) {
                $sub->where('categoria_id', $data['categoria_id']);
            });
        });

        // Filtro por cliente
        $query->when(isset($data['cliente']), function ($sql) use ($data) {
            $sql->whereHas('cliente', function ($sub) use ($data) {
                $sub->where('nombres', "like", "%" . $data['cliente'] . "%");
            });
        });

        // Filtro por articulo
        $query->when(isset($data['articulo']), function ($sql) use ($data) {
            $sql->whereHas('detalles_facturas', function ($q) use ($data) {
                $q->whereHas('articulo', function ($sub) use ($data) {
                    $sub->where('nombre', "like", "%" . $data['articulo'] . "%");
                });
            });
        });

        // Filtro por fecha
        $query->when($data['fecha_inicio'] && $data['fecha_final'], function ($sql) use ($data) {
            $sql->whereBetween('created_at', [
                Carbon::parse($data['fecha_inicio'])->format('Y-m-d') . " 00:00:00",
                Carbon::parse($data['fecha_final'])->format('Y-m-d') . " 23:59:59"
            ]);
        });

        // Filtro por segmento_id
        $query->when(isset($data['vendedor_id']), function ($sql) use ($data) {
            $sql->where('user_id', $data['vendedor_id']);
        });

        return $query;
    }
}
