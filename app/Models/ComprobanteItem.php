<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComprobanteItem extends Model
{
    protected $table = 'comprobante_items';
    protected $fillable = [
        'comprobante_id',
        'productoName',
        'productoPrecio',
    ];

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }
}
