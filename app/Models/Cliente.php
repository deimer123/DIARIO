<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [

        'cedula',
        'nombre',          
        'telefono',
        'direccion',
        'foto'
            

    ];



    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'cliente_id');
    }

}
