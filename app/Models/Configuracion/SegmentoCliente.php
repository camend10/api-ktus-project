<?php

namespace App\Models\Configuracion;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SegmentoCliente extends Model
{
    use HasFactory;

    protected $table = 'segmento_cliente';

    protected $fillable = [
        'nombre',
        'descripcion',
        'empresa_id',
        'estado'
    ];

    protected $casts = [
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

}
