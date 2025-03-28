<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'model',
        'name',
        'description',
        'stock', // Stock se actualiza desde los entries
        'cost', // Último precio de compra, se actualiza desde los entries
        'wholesale_price', // Precio al por mayor
        'store_price', // Precio en tienda
        'invoice_price', // Precio con factura
        'expiration_date',
        'status',
        'image', // Agregamos el campo de la imagen aquí
    ];
    protected $casts = [
        'expiration_date' => 'date:Y-m-d', // ✅ Laravel manejará expiration_date como fecha
    ];
    // Relación con la categoría
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relación con historial de precios
    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class, 'article_id')->orderBy('created_at', 'desc');
    }

    // Última entrada relacionada al artículo
    public function lastEntry()
    {
        return $this->hasOne(EntryDetail::class, 'article_id')->latest('created_at');
    }

    // Obtener el último precio
    public function getLastPriceAttribute()
    {
        return $this->lastEntry ? $this->lastEntry->price : 'N/A';
    }

    // Relación con los detalles de entrada
    public function entryDetails()
    {
        return $this->hasMany(EntryDetail::class, 'article_id');
    }

    // 🔹 Relación con los códigos de barras asociados
    public function barcodes()
    {
        return $this->hasMany(ArticleBarcode::class, 'article_id');
    }
    public function decreaseStock($quantity)
    {
        if ($this->stock >= $quantity) {
            $this->stock -= $quantity;
            $this->save();
        } else {
            throw new \Exception("Stock insuficiente para el artículo {$this->name}");
        }
    }

}
