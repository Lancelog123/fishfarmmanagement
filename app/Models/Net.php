<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Net extends Model
{
    use HasFactory;

    protected $fillable = [
        'pond_id',
        'identifier',
        'quantity', // if needed
    ];

    // Relationship: Net has many StockingLogs
    public function stockingLogs()
    {
        return $this->hasMany(StockingLog::class);
    }
}
