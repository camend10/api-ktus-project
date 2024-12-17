<?php

namespace App\Models\Articulos;

use App\Models\Configuracion\Bodega;
use App\Models\Configuracion\Categoria;
use App\Models\Configuracion\Iva;
use App\Models\Configuracion\Proveedor;
use App\Models\Configuracion\Sede;
use App\Models\Configuracion\Unidad;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Articulo extends Model
{
    use HasFactory;

    protected $table = 'articulos';

    protected $fillable = [
        'sku',
        'nombre',
        'descripcion',
        'precio_general',
        'punto_pedido',
        'tipo',
        'imagen',
        'iva_id',
        'empresa_id',
        'estado',
        'especificaciones',
        'categoria_id',
        'is_gift',
        'descuento_maximo',
        'descuento_minimo',
        'tiempo_de_abastecimiento',
        'disponibilidad',
        'peso',
        'ancho',
        'alto',
        'largo',
        'user_id',
        'punto_pedido_unidad_id',
        'is_discount',
        'impuesto',
        'proveedor_id',
        'state_stock'
    ];

    protected $casts = [
        'especificaciones' => 'array',
        'state_stock' => 'integer',
        'estado' => 'integer',
        'is_discount' => 'integer',
        'empresa_id' => 'integer',
        'categoria_id' => 'integer',
        'proveedor_id' => 'integer',
        'impuesto' => 'integer',
        'disponibilidad' => 'integer',
        'is_gift' => 'integer',
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

    public function iva()
    {
        return $this->belongsTo(Iva::class, 'iva_id')->withDefault();
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id')->withDefault();
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id')->withDefault();
    }

    public function unidad_punto_pedido()
    {
        return $this->belongsTo(Unidad::class, 'punto_pedido_unidad_id')->withDefault();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id')->withDefault();
    }

    public function bodegas()
    {
        return $this->belongsToMany(Bodega::class, 'bodegas_articulos', 'articulo_id', 'bodega_id')
            ->withPivot('cantidad', 'estado', 'unidad_id', 'empresa_id');
    }

    public function wallets()
    {
        return $this->belongsToMany(Sede::class, 'articulo_wallets', 'articulo_id', 'sede_id')
            ->withPivot('unidad_id', 'segmento_cliente_id', 'precio', 'estado', 'empresa_id');
    }

    public function bodegas_articulos()
    {
        return $this->hasMany(BodegaArticulo::class);
    }

    public function articulos_wallets()
    {
        return $this->hasMany(ArticuloWallet::class);
    }

    public function scopeFilterAdvance($query, $data)
    {
        // Normaliza los valores especiales
        $data['categoria_id'] = isset($data['categoria_id']) && $data['categoria_id'] == 9999999 ? null : ($data['categoria_id'] ?? null);
        $data['impuesto'] = isset($data['impuesto']) && $data['impuesto'] == 9999999 ? null : ($data['impuesto'] ?? null);
        $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? null : ($data['sede_id'] ?? null);
        $data['segmento_cliente_id'] = isset($data['segmento_cliente_id']) && $data['segmento_cliente_id'] == 9999999 ? null : ($data['segmento_cliente_id'] ?? null);
        $data['bodega_id'] = isset($data['bodega_id']) && $data['bodega_id'] == 9999999 ? null : ($data['bodega_id'] ?? null);
        $data['unidad_id_bodegas'] = isset($data['unidad_id_bodegas']) && $data['unidad_id_bodegas'] == 9999999 ? null : ($data['unidad_id_bodegas'] ?? null);
        $data['proveedor_id'] = isset($data['proveedor_id']) && $data['proveedor_id'] == 9999999 ? null : ($data['proveedor_id'] ?? null);
        $data['state_stock'] = isset($data['state_stock']) && $data['state_stock'] == 9999999 ? null : ($data['state_stock'] ?? null);

        $query->when($data['buscar'], function ($sql) use ($data) {
            $sql->where(DB::raw("CONCAT(articulos.nombre,' ',articulos.sku)"), 'like', '%' . $data['buscar'] . '%');
        });

        // Filtro por categoría
        $query->when(isset($data['categoria_id']), function ($sql) use ($data) {
            $sql->where('categoria_id', $data['categoria_id']);
        });

        // Filtro por disponibilidad
        $query->when(isset($data['state_stock']), function ($sql) use ($data) {
            $sql->where('state_stock', $data['state_stock']);
        });

        // Filtro por impuesto
        $query->when(isset($data['impuesto']), function ($sql) use ($data) {
            $sql->where('impuesto', $data['impuesto']);
        });

        // Filtro por proveedor
        $query->when(isset($data['proveedor_id']), function ($sql) use ($data) {
            $sql->where('proveedor_id', $data['proveedor_id']);
        });

        // Filtros en relaciones
        $query->when(isset($data['sede_id']), function ($sql) use ($data) {
            $sql->whereHas('articulos_wallets', function ($sub) use ($data) {
                $sub->where('sede_id', $data['sede_id']);
            });
        });

        $query->when(isset($data['segmento_cliente_id']), function ($sql) use ($data) {
            $sql->whereHas('articulos_wallets', function ($sub) use ($data) {
                $sub->where('segmento_cliente_id', $data['segmento_cliente_id']);
            });
        });

        $query->when(isset($data['bodega_id']), function ($sql) use ($data) {
            $sql->whereHas('bodegas_articulos', function ($sub) use ($data) {
                $sub->where('bodega_id', $data['bodega_id']);
            });
        });

        $query->when(isset($data['unidad_id_bodegas']), function ($sql) use ($data) {
            $sql->whereHas('bodegas_articulos', function ($sub) use ($data) {
                $sub->where('unidad_id', $data['unidad_id_bodegas']);
            });
        });

        return $query;
    }
}
