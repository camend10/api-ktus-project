<?php

namespace App\Models\Configuracion;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Iva extends Model
{
    use HasFactory;

    protected $table = 'iva';

    protected $fillable = [
        'porcentaje',
        'empresa_id',
        'estado'
    ];

    protected $casts = [
        'porcentaje' => 'float', // Porcentaje será un número decimal
        'estado' => 'integer',
        'empresa_id' => 'integer',
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
}
