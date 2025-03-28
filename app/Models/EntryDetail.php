<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
    
use Illuminate\Database\Eloquent\Model;

class EntryDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_id',
        'article_id',
        'quantity',
        'price', // Precio de compra
        'wholesale_price',
        'store_price',
        'invoice_price',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
