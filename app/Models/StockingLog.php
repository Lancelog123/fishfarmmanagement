<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pond_id',
        'net_id',
        'species',
        'quantity',
        'action_type',
        'target_pond_id',
        'action_date',
    ];

    // Relationship: StockingLog belongs to a Pond
    public function pond()
    {
        return $this->belongsTo(Pond::class);
    }

    // Relationship: StockingLog belongs to a Net (nullable)
    public function net()
    {
        return $this->belongsTo(Net::class);
    }

    // Relationship: StockingLog belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Optional: If you have a target pond reference
    public function targetPond()
    {
        return $this->belongsTo(Pond::class, 'target_pond_id');
    }
}
