<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'old_cost',
        'new_cost',
        'old_wholesale_price',
        'new_wholesale_price',
        'old_store_price',
        'new_store_price',
        'old_invoice_price',
        'new_invoice_price',
        'changed_at',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
