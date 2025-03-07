<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    use HasFactory;
    protected $table = 'tipo_documentos';
    protected $fillable = [
        'nombre', 'sigla', 'estado'
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
