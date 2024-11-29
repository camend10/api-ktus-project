<?php

namespace App\Models\Configuracion;

use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadTransformacion extends Model
{
    use HasFactory;

    protected $table = 'unidad_transformacion';

    protected $fillable = [
        'unidad_id',
        'unidad_to_id',
        'empresa_id',
        'estado'
    ];

    protected $casts = [
        'unidad_id' => 'integer',
        'unidad_to_id' => 'integer',
        'estado' => 'integer',
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

    public function unidad_to()
    {
        return $this->belongsTo(Unidad::class, 'unidad_to_id')->withDefault();
    }
}
