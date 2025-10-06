<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pond_id',
        'net_id',
        'task',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pond()
    {
        return $this->belongsTo(Pond::class);
    }

    public function net()
    {
        return $this->belongsTo(Net::class);
    }
}
