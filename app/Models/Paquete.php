<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paquete extends Model
{
    use SoftDeletes;

    protected $table = 'paquetes';

    protected $fillable = [
        'codigo',
        'destinatario',
        'estado',
        'accion',
        'cuidad',
        'peso',
        'user',
        'observacion',
    ];
}
