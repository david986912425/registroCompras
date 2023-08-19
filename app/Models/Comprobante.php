<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $fillable = [
        'user_id',
        'FechaEmision',
        'nameEmisor',
        'rucEmisor',
        'nameReceptor',
        'rucReceptor',
        'ventaTotal',
        'ventaTotalImpuesto',
        'otrosPagos',
        'importeTotal',
    ];
}
