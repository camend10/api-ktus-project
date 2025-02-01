<?php

namespace App\Models\Articulos;

use App\Models\Configuracion\Bodega;
use App\Models\Configuracion\Sede;
use App\Models\Configuracion\Unidad;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Conversion extends Model
{
    use HasFactory;

    protected $table = 'conversiones';

    protected $fillable = [
        'articulo_id',
        'bodega_id',
        'unidad_inicio_id',
        'unidad_final_id',
        'user_id',
        'empresa_id',
        'sede_id',
        'estado',
        'cantidad_inicial',
        'cantidad_final',
        'cantidad_convertida',
        'descripcion'
    ];

    protected $casts = [
        'especificaciones' => 'array',
        'articulo_id' => 'integer',
        'bodega_id' => 'integer',
        'unidad_inicio_id' => 'integer',
        'unidad_final_id' => 'integer',
        'user_id' => 'integer',
        'empresa_id' => 'integer',
        'sede_id' => 'integer',
        'estado' => 'integer',
        'cantidad_inicial' => 'float',
        'cantidad_final' => 'float',
        'cantidad_convertida' => 'float',
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

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id')->withDefault();
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'bodega_id')->withDefault();
    }

    public function unidad_inicio()
    {
        return $this->belongsTo(Unidad::class, 'unidad_inicio_id')->withDefault();
    }

    public function unidad_final()
    {
        return $this->belongsTo(Unidad::class, 'unidad_final_id')->withDefault();
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

    public function scopeFilterAdvance($query, $data)
    {
        // Normaliza los valores especiales
        $data['bodega_id'] = isset($data['bodega_id']) && $data['bodega_id'] == 9999999 ? null : ($data['bodega_id'] ?? null);
        $data['unidad_inicio_id'] = isset($data['unidad_inicio_id']) && $data['unidad_inicio_id'] == 9999999 ? null : ($data['unidad_inicio_id'] ?? null);
        $data['unidad_final_id'] = isset($data['unidad_final_id']) && $data['unidad_final_id'] == 9999999 ? null : ($data['unidad_final_id'] ?? null);

        $query->when($data['buscar'], function ($sql) use ($data) {
            $sql->where('id', $data['buscar']);
        });

        // Filtro por unidad_inicio
        $query->when(isset($data['unidad_inicio_id']), function ($sql) use ($data) {
            $sql->where('unidad_inicio_id', $data['unidad_inicio_id']);
        });

        // Filtro por unidad_final
        $query->when(isset($data['unidad_final_id']), function ($sql) use ($data) {
            $sql->where('unidad_final_id', $data['unidad_final_id']);
        });

        // Filtro por bodega
        $query->when(isset($data['bodega_id']), function ($sql) use ($data) {
            $sql->where('bodega_id', $data['bodega_id']);
        });

        // Filtro por articulo
        $query->when(isset($data['articulo']), function ($sql) use ($data) {
            $sql->whereHas('articulo', function ($q) use ($data) {
                $q->where('nombre', "like", "%" . $data['articulo'] . "%");
            });
        });

        // Filtro por fecha
        $query->when($data['fecha_inicio'] && $data['fecha_final'], function ($sql) use ($data) {
            $sql->whereBetween('created_at', [
                Carbon::parse($data['fecha_inicio'])->format('Y-m-d') . " 00:00:00",
                Carbon::parse($data['fecha_final'])->format('Y-m-d') . " 23:59:59"
            ]);
        });

        return $query;
    }
}
