<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HarvestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'pond_id',
        'type',
        'species',
        'size_inch',
        'fish_qty',
        'price_per_unit',
        'total_amount',
        'buyer_name',
    ];

    public function pond()
    {
        return $this->belongsTo(Pond::class);
    }
}
