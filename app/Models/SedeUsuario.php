<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SedeUsuario extends Model
{
    use HasFactory;

    protected $table = 'sedes_usuarios';

    protected $fillable = [
        'usuario_id',
        'sede_id',
        'estado'
    ];
}
