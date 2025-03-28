<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_name', 'date', 'total'];

    public function details()
    {
        return $this->hasMany(EntryDetail::class, 'entry_id');
    }
}
