<?php

namespace App\Models\Movimientos;

use App\Models\Configuracion\Sede;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    use HasFactory;

    protected $table = 'plantillas';

    protected $fillable = [
        'codigo',
        'nombre',
        'observacion',
        'empresa_id',
        'sede_id',
        'estado',
        'user_id',
    ];

    protected $casts = [
        'estado' => 'integer',
        'empresa_id' => 'integer',
        'sede_id' => 'integer',
        'user_id' => 'integer',
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

    
    public function detalles_plantillas()
    {
        return $this->hasMany(DetallePlantilla::class, 'plantilla_id');
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
}
