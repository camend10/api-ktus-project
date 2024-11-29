<?php

namespace App\Models\Configuracion;

use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Municipio;
use App\Models\TipoDocumento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'tipo_identificacion',
        'identificacion',
        'dv',
        'nombres',
        'apellidos',
        'email',
        'direccion',
        'celular',
        'departamento_id',
        'municipio_id',
        'empresa_id',
        'imagen',
        'estado'
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'estado' => 'integer',
        'tipo_identificacion' => 'integer',
        'dv' => 'integer',
        'celular' => 'integer',
        'departamento_id' => 'integer',
        'municipio_id' => 'integer',
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

    public function tipodocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_identificacion')->withDefault();
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class)->withDefault();
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class)->withDefault();
    }
}
