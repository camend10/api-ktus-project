<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresa';

    protected $fillable = [
        'nit_empresa',
        'dv',
        'nombre',
        'email',
        'direccion',
        'telefono',
        'web',
        'celular',
        'estado',
        'departamento_id',
        'municipio_id',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class)->withDefault();
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class)->withDefault();
    }
}
