<?php

namespace App\Models\Configuracion;

use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Municipio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sede extends Model
{
    use HasFactory;

    protected $table = 'sedes';

    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'telefono',
        'celular',
        'identificacion_responsable',
        'responsable',
        'telefono_responsable',
        'empresa_id',
        'estado',
        'departamento_id',
        'municipio_id',
    ];

    public function setCreatedAtAttribute($value)
    {
        // date_default_timezone_set("America/Bogota");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value)
    {
        // date_default_timezone_set("America/Bogota");
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id')->withDefault();
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sedes_usuarios', 'sede_id', 'usuario_id')
                    ->withPivot('estado')
                    ->withTimestamps();
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
