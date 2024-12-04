<?php

namespace App\Models\Clientes;

use App\Models\Configuracion\Sede;
use App\Models\Configuracion\SegmentoCliente;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Genero;
use App\Models\Municipio;
use App\Models\TipoDocumento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

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
        'sede_id',
        'estado',
        'fecha_nacimiento',
        'user_id',
        'is_parcial',
        'segmento_cliente_id',
        'genero_id',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'estado' => 'integer',
        'tipo_identificacion' => 'integer',
        'dv' => 'integer',
        'celular' => 'integer',
        'departamento_id' => 'integer',
        'municipio_id' => 'integer',
        'sede_id' => 'integer',
        'user_id' => 'integer',
        'is_parcial' => 'integer',
        'segmento_cliente_id' => 'integer',
        'fecha_nacimiento' => 'date', // O 'datetime' si cambiaste a DATETIME
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

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id')->withDefault();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function segmento()
    {
        return $this->belongsTo(SegmentoCliente::class, 'segmento_cliente_id')->withDefault();
    }

    public function genero()
    {
        return $this->belongsTo(Genero::class, 'genero_id')->withDefault();
    }

    public function scopeFilterAdvance($query, $data)
    {
        // Normaliza los valores especiales
        $data['segmento_cliente_id'] = isset($data['segmento_cliente_id']) && $data['segmento_cliente_id'] == 9999999 ? null : $data['segmento_cliente_id'];
        $data['tipo'] = isset($data['tipo']) && $data['tipo'] == 9999999 ? null : $data['tipo'];

        $query->when($data['buscar'], function ($sql) use ($data) {
            $sql->where(DB::raw("CONCAT(clientes.nombres,' ',clientes.apellidos, ' ',
            clientes.identificacion, ' ', ISNULL(clientes.celular,''), ' ',ISNULL(clientes.email,''))"), 'like', '%' . $data['buscar'] . '%');
        });

        // Filtro por categoría
        $query->when(isset($data['tipo']), function ($sql) use ($data) {
            $sql->where('tipo_identificacion', $data['tipo']);
        });

        $query->when(isset($data['segmento_cliente_id']), function ($sql) use ($data) {
            $sql->where('segmento_cliente_id', $data['segmento_cliente_id']);
        });


        return $query;
    }
}
