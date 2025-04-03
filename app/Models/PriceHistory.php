<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'entry_id',
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

    protected $casts = [
        'old_cost' => 'decimal:2',
        'new_cost' => 'decimal:2',
        'old_wholesale_price' => 'decimal:2',
        'new_wholesale_price' => 'decimal:2',
        'old_store_price' => 'decimal:2',
        'new_store_price' => 'decimal:2',
        'old_invoice_price' => 'decimal:2',
        'new_invoice_price' => 'decimal:2',
        'changed_at' => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }
}
