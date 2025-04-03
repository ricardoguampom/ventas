<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = ['provider_id', 'date', 'total'];

    public function details()
    {
        return $this->hasMany(EntryDetail::class, 'entry_id');
    }
    // app/Models/Entry.php
    public function provider() {
        return $this->belongsTo(Person::class, 'provider_id');
    }
}
