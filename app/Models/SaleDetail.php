<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'article_id',
        'quantity',
        'price',
    ];

    /**
     * Relación: Un detalle pertenece a una venta.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación: Un detalle pertenece a un artículo.
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Calcular el subtotal de este detalle.
     */
    public function calculateSubtotal()
    {
        return $this->quantity * $this->price;
    }
}
