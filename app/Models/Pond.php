<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pond extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'category',
        'location',
        'status',
    ];

    /**
     * A Pond can have many Nets
     */
    public function nets()
    {
        return $this->hasMany(Net::class);
    }
    public function stockingLogs()
    {
        return $this->hasMany(StockingLog::class);
    }
}
