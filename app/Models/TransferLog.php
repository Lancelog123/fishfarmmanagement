<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    
        'from_pond_id', // add this
        'to_net_id',
        'quantity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromNet()
    {
        return $this->belongsTo(Net::class, 'from_net_id');
    }

    public function toNet()
    {
        return $this->belongsTo(Net::class, 'to_net_id');
    }
    public function fromPond()
    {
        return $this->belongsTo(Pond::class, 'from_pond_id');
    }
}
