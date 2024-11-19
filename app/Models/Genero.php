<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genero extends Model
{
    use HasFactory;

    protected $table = 'genero';
    protected $fillable = [
        'nombre',
        'estado'
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
