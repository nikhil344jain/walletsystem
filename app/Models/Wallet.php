<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'balance'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
