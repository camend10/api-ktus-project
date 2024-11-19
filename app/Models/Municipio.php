<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;
    protected $table = 'municipios';
    protected $fillable = [
        'nombre', 'estado', 'departamento_id'
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class)->withDefault();
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
