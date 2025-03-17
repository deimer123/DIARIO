<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPago extends Model
{
    use HasFactory;

    protected $fillable = [
        'prestamo_id',
        'fecha',
        'estado',
        
    ];

    public function prestamo()
    {
       // return $this->belongsTo(Prestamo::class);
        return $this->belongsTo(Prestamo::class, 'prestamo_id');
    }


    

    
}